<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MeetingSchedule;
use App\Models\Todo;
use App\Models\DailyLogbook;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the director's dashboard
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $directorId = $user->isDirector() ? $user->id : $this->getDirectorId();

        // Get statistics
        $stats = $this->getStatistics($directorId);

        // Get today's meetings
        $todayMeetings = MeetingSchedule::forDirector($directorId)
            ->today()
            ->active()
            ->orderBy('start_time')
            ->get();

        // Get upcoming meetings (next 7 days)
        $upcomingMeetings = MeetingSchedule::forDirector($directorId)
            ->upcoming()
            ->where('start_time', '<=', now()->addDays(7))
            ->active()
            ->limit(5)
            ->get();

        // Get pending appointments
        $pendingAppointments = Appointment::forDirector($directorId)
            ->pending()
            ->orderBy('requested_date')
            ->limit(5)
            ->get();

        // Get user's todos
        $todos = Todo::where('user_id', $user->id)
            ->incomplete()
            ->orderByPriority()
            ->limit(5)
            ->get();

        // Get today's logbook
        $todayLogbook = DailyLogbook::getOrCreateForToday($directorId, $user->id);

        // Get blocked dates for the current and next month
        $blockedDates = \App\Models\BlockedDate::forDirector($directorId)
            ->whereBetween('blocked_date', [now()->startOfMonth()->subWeek(), now()->addMonth()->endOfMonth()])
            ->get();

        // Get monthly meetings for calendar (current month +/- buffer)
        $monthlyMeetings = MeetingSchedule::forDirector($directorId)
            ->whereBetween('start_time', [now()->startOfMonth()->subWeek(), now()->addMonth()->endOfMonth()])
            ->active()
            ->get()
            ->map(function ($meeting) {
                return array_merge($meeting->toCalendarEvent(), [
                    'extendedProps' => [
                        'description' => $meeting->description,
                        'status' => $meeting->status,
                        'attendees' => $meeting->attendees,
                        'location' => $meeting->location,
                    ]
                ]);
            });

        // Get notifications for ticker
        $notifications = $this->getRecentNotifications($directorId);

        return Inertia::render('Director/Dashboard', [
            'stats' => $stats,
            'todayMeetings' => $todayMeetings,
            'upcomingMeetings' => $upcomingMeetings,
            'pendingAppointments' => $pendingAppointments,
            'todos' => $todos,
            'todayLogbook' => $todayLogbook,
            'notifications' => $notifications,
            'blockedDates' => $blockedDates,
            'monthlyMeetings' => $monthlyMeetings,
        ]);
    }

    /**
     * Get dashboard statistics
     */
    protected function getStatistics(int $directorId): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'pending_appointments' => Appointment::forDirector($directorId)->pending()->count(),
            'today_meetings' => MeetingSchedule::forDirector($directorId)->today()->active()->count(),
            'this_week_meetings' => MeetingSchedule::forDirector($directorId)
                ->whereBetween('start_time', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])
                ->active()
                ->count(),
            'this_month_meetings' => MeetingSchedule::forDirector($directorId)
                ->whereBetween('start_time', [$thisMonth, $thisMonth->copy()->endOfMonth()])
                ->active()
                ->count(),
            'approved_this_month' => Appointment::forDirector($directorId)
                ->where('status', Appointment::STATUS_APPROVED)
                ->whereMonth('updated_at', $today->month)
                ->count(),
            'rejected_this_month' => Appointment::forDirector($directorId)
                ->where('status', Appointment::STATUS_REJECTED)
                ->whereMonth('updated_at', $today->month)
                ->count(),
        ];
    }

    /**
     * Get recent notifications for ticker display
     */
    protected function getRecentNotifications(int $directorId): array
    {
        $notifications = [];

        // Recent appointment requests
        $recentAppointments = Appointment::forDirector($directorId)
            ->pending()
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentAppointments as $appointment) {
            $notifications[] = [
                'id' => 'apt-' . $appointment->id,
                'type' => 'appointment',
                'message' => "New appointment request from {$appointment->requester_name}",
                'time' => $appointment->created_at->diffForHumans(),
                'priority' => $appointment->priority,
            ];
        }

        // Upcoming meetings reminder (within 1 hour)
        $upcomingMeetings = MeetingSchedule::forDirector($directorId)
            ->where('start_time', '>=', now())
            ->where('start_time', '<=', now()->addHour())
            ->active()
            ->get();

        foreach ($upcomingMeetings as $meeting) {
            $notifications[] = [
                'id' => 'mtg-' . $meeting->id,
                'type' => 'meeting',
                'message' => "Upcoming: {$meeting->title} at {$meeting->start_time->format('h:i A')}",
                'time' => $meeting->start_time->diffForHumans(),
                'priority' => 'high',
            ];
        }

        return $notifications;
    }

    /**
     * Get the director ID (for PA/Staff access)
     */
    protected function getDirectorId(): int
    {
        // Get the first director in the system
        $director = \App\Models\User::directors()->first();
        return $director ? $director->id : 0;
    }

    /**
     * Dashboard API endpoint
     */
    public function apiIndex(Request $request)
    {
        $user = $request->user();
        $directorId = $user->isDirector() ? $user->id : $this->getDirectorId();

        return response()->json([
            'stats' => $this->getStatistics($directorId),
            'notifications' => $this->getRecentNotifications($directorId),
        ]);
    }
}
