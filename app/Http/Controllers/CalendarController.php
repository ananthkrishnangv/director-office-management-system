<?php

namespace App\Http\Controllers;

use App\Models\MeetingSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Display calendar view
     */
    public function index(Request $request)
    {
        return Inertia::render('Calendar/View', [
            'initialView' => $request->get('view', 'month'),
            'initialDate' => $request->get('date', now()->format('Y-m-d')),
        ]);
    }

    /**
     * Display kiosk mode calendar
     */
    public function kiosk(Request $request)
    {
        return Inertia::render('Calendar/Kiosk');
    }

    /**
     * Get calendar events for a date range
     */
    public function events(Request $request)
    {
        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'director_id' => 'nullable|exists:users,id',
        ]);

        $user = $request->user();
        $directorId = $validated['director_id'] ?? ($user->isDirector() ? $user->id : $this->getDirectorId());

        $meetings = MeetingSchedule::forDirector($directorId)
            ->betweenDates($validated['start'], $validated['end'])
            ->active()
            ->get()
            ->map(function ($meeting) {
                return $meeting->toCalendarEvent();
            });

        return response()->json($meetings);
    }

    /**
     * Get events for a specific date
     */
    public function date(Request $request, string $date)
    {
        $user = $request->user();
        $directorId = $user->isDirector() ? $user->id : $this->getDirectorId();
        $targetDate = Carbon::parse($date);

        $meetings = MeetingSchedule::forDirector($directorId)
            ->whereDate('start_time', $targetDate)
            ->active()
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'date' => $targetDate->format('Y-m-d'),
            'day_name' => $targetDate->format('l'),
            'meetings' => $meetings,
            'count' => $meetings->count(),
        ]);
    }

    /**
     * Sync with Google Calendar
     */
    public function syncGoogle(Request $request)
    {
        // This would integrate with Google Calendar API
        // For now, return a placeholder response

        $clientId = config('services.google.client_id');
        
        if (!$clientId) {
            return response()->json([
                'status' => 'not_configured',
                'message' => 'Google Calendar integration is not configured. Please set up Google API credentials.',
            ], 422);
        }

        // TODO: Implement actual Google Calendar sync
        // 1. Exchange authorization code for tokens
        // 2. Fetch events from Google Calendar
        // 3. Create/update MeetingSchedule records
        // 4. Push local events to Google Calendar

        return response()->json([
            'status' => 'success',
            'message' => 'Google Calendar sync initiated',
            'synced_events' => 0,
        ]);
    }

    /**
     * Sync with Microsoft Calendar
     */
    public function syncMicrosoft(Request $request)
    {
        // This would integrate with Microsoft Graph API
        // For now, return a placeholder response

        $clientId = config('services.microsoft.client_id');
        
        if (!$clientId) {
            return response()->json([
                'status' => 'not_configured',
                'message' => 'Microsoft Calendar integration is not configured. Please set up Microsoft Graph API credentials.',
            ], 422);
        }

        // TODO: Implement actual Microsoft Calendar sync
        // 1. Exchange authorization code for tokens
        // 2. Fetch events from Outlook Calendar
        // 3. Create/update MeetingSchedule records
        // 4. Push local events to Outlook Calendar

        return response()->json([
            'status' => 'success',
            'message' => 'Microsoft Calendar sync initiated',
            'synced_events' => 0,
        ]);
    }

    /**
     * Get Google OAuth URL for calendar integration
     */
    public function getGoogleAuthUrl(Request $request)
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect_uri');

        if (!$clientId) {
            return response()->json([
                'error' => 'Google Calendar integration is not configured',
            ], 422);
        }

        $scopes = [
            'https://www.googleapis.com/auth/calendar.readonly',
            'https://www.googleapis.com/auth/calendar.events',
        ];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return response()->json([
            'auth_url' => $authUrl,
        ]);
    }

    /**
     * Get Microsoft OAuth URL for calendar integration
     */
    public function getMicrosoftAuthUrl(Request $request)
    {
        $clientId = config('services.microsoft.client_id');
        $redirectUri = config('services.microsoft.redirect_uri');
        $tenantId = config('services.microsoft.tenant_id', 'common');

        if (!$clientId) {
            return response()->json([
                'error' => 'Microsoft Calendar integration is not configured',
            ], 422);
        }

        $scopes = [
            'openid',
            'profile',
            'email',
            'Calendars.ReadWrite',
        ];

        $authUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'response_mode' => 'query',
        ]);

        return response()->json([
            'auth_url' => $authUrl,
        ]);
    }

    /**
     * Get director ID
     */
    protected function getDirectorId(): int
    {
        $director = User::directors()->first();
        return $director ? $director->id : 0;
    }
}
