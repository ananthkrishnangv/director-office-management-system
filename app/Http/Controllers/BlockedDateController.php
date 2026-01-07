<?php

namespace App\Http\Controllers;

use App\Models\BlockedDate;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlockedDateController extends Controller
{
    public function index()
    {
        $blockedDates = BlockedDate::where('blocked_date', '>=', now()->toDateString())
            ->orderBy('blocked_date')
            ->get();

        return Inertia::render('BlockedDates/Index', [
            'blockedDates' => $blockedDates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'blocked_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|in:on_tour,urgent_meeting,scheduled_meeting,on_leave,other',
            'notes' => 'nullable|string|max:500',
        ]);

        // Get director ID
        $directorId = User::where('role', 'director')->first()?->id ?? 1;

        BlockedDate::create([
            'director_id' => $directorId,
            'blocked_date' => $validated['blocked_date'],
            'reason' => $validated['notes'] ?? $validated['reason'],
            'type' => $this->mapReasonToType($validated['reason']),
            'is_all_day' => true,
        ]);

        return back()->with('success', 'Date blocked successfully.');
    }

    public function destroy(BlockedDate $blockedDate)
    {
        $blockedDate->delete();
        return back()->with('success', 'Blocked date removed.');
    }

    public function getBlockedDates()
    {
        $blockedDates = BlockedDate::where('blocked_date', '>=', now()->toDateString())
            ->orderBy('blocked_date')
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'date' => $d->blocked_date,
                'reason' => $d->reason,
                'type' => $d->type,
            ]);

        return response()->json($blockedDates);
    }

    private function mapReasonToType(string $reason): string
    {
        return match ($reason) {
            'on_tour' => 'travel',
            'on_leave' => 'leave',
            default => 'other',
        };
    }
}
