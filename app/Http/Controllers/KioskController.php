<?php

namespace App\Http\Controllers;

use App\Models\MeetingSchedule;
use App\Models\BlockedDate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KioskController extends Controller
{
    // Default kiosk PIN (can be configured in .env)
    private const KIOSK_PIN = '1234';

    public function index()
    {
        return Inertia::render('Kiosk/Login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|min:4|max:6',
        ]);

        $validPin = config('app.kiosk_pin', self::KIOSK_PIN);

        if ($request->pin === $validPin) {
            session(['kiosk_authenticated' => true]);
            return redirect('/kiosk/dashboard');
        }

        return back()->withErrors(['pin' => 'Invalid PIN']);
    }

    public function dashboard()
    {
        if (!session('kiosk_authenticated')) {
            return redirect('/kiosk');
        }

        $today = now()->toDateString();

        // Today's meetings
        $todayMeetings = MeetingSchedule::whereDate('start_time', $today)
            ->orderBy('start_time')
            ->get();

        // Stats
        $stats = [
            'today_meetings' => $todayMeetings->count(),
            'pending' => MeetingSchedule::where('status', 'scheduled')->count(),
            'completed_today' => MeetingSchedule::whereDate('start_time', $today)
                ->where('status', 'completed')->count(),
            'cancelled' => MeetingSchedule::where('status', 'cancelled')
                ->whereDate('start_time', $today)->count(),
        ];

        // Blocked dates
        $blockedDates = BlockedDate::where('blocked_date', '>=', $today)
            ->orderBy('blocked_date')
            ->limit(5)
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'blocked_date' => $d->blocked_date,
                'block_reason' => $d->reason ?? $d->type,
            ]);

        return Inertia::render('Kiosk/Dashboard', [
            'todayMeetings' => $todayMeetings,
            'stats' => $stats,
            'blockedDates' => $blockedDates,
            'currentTime' => now()->toISOString(),
        ]);
    }

    public function logout()
    {
        session()->forget('kiosk_authenticated');
        return redirect('/kiosk');
    }
}
