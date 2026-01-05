<?php

namespace App\Http\Controllers;

use App\Models\MeetingSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class MeetingController extends Controller
{
    /**
     * Display list of meetings
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = MeetingSchedule::with(['director', 'appointment', 'createdBy']);

        if ($user->isDirector()) {
            $query->forDirector($user->id);
        }

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->where('start_time', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('start_time', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        if ($request->meeting_type) {
            $query->where('meeting_type', $request->meeting_type);
        }

        $meetings = $query->orderBy('start_time', 'desc')
            ->paginate(15);

        return Inertia::render('Meetings/Index', [
            'meetings' => $meetings,
            'filters' => $request->only(['status', 'date_from', 'date_to', 'meeting_type']),
        ]);
    }

    /**
     * Show create meeting form
     */
    public function create()
    {
        return Inertia::render('Meetings/Create', [
            'directors' => User::directors()->active()->get(['id', 'name']),
        ]);
    }

    /**
     * Store a new meeting
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'director_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meeting_type' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'online_link' => 'nullable|url|max:500',
            'room_number' => 'nullable|string|max:50',
            'attendees' => 'nullable|array',
            'attendees.*.name' => 'required|string',
            'attendees.*.email' => 'nullable|email',
            'notes' => 'nullable|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'is_private' => 'boolean',
            'color' => 'nullable|string|max:7',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = MeetingSchedule::STATUS_SCHEDULED;

        $meeting = MeetingSchedule::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Meeting created successfully',
                'meeting' => $meeting,
            ], 201);
        }

        return redirect()->route('meetings.index')
            ->with('success', 'Meeting scheduled successfully');
    }

    /**
     * Show meeting details
     */
    public function show(MeetingSchedule $meeting)
    {
        $meeting->load(['director', 'appointment', 'createdBy', 'todos']);

        return Inertia::render('Meetings/Show', [
            'meeting' => $meeting,
        ]);
    }

    /**
     * Update a meeting
     */
    public function update(Request $request, MeetingSchedule $meeting)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'meeting_type' => 'sometimes|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'is_all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'online_link' => 'nullable|url|max:500',
            'room_number' => 'nullable|string|max:50',
            'attendees' => 'nullable|array',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled,postponed',
            'notes' => 'nullable|string',
            'outcome' => 'nullable|string',
            'action_items' => 'nullable|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'is_private' => 'boolean',
            'color' => 'nullable|string|max:7',
        ]);

        $meeting->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Meeting updated successfully',
                'meeting' => $meeting->fresh(),
            ]);
        }

        return redirect()->back()->with('success', 'Meeting updated successfully');
    }

    /**
     * Delete a meeting
     */
    public function destroy(MeetingSchedule $meeting)
    {
        $meeting->delete();

        return response()->json([
            'message' => 'Meeting deleted successfully',
        ]);
    }

    /**
     * Get today's meetings
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $directorId = $user->isDirector() ? $user->id : $this->getDirectorId();

        $meetings = MeetingSchedule::forDirector($directorId)
            ->today()
            ->active()
            ->orderBy('start_time')
            ->get();

        return response()->json($meetings);
    }

    /**
     * Get upcoming meetings
     */
    public function upcoming(Request $request)
    {
        $user = $request->user();
        $directorId = $user->isDirector() ? $user->id : $this->getDirectorId();
        $days = $request->get('days', 7);

        $meetings = MeetingSchedule::forDirector($directorId)
            ->upcoming()
            ->where('start_time', '<=', now()->addDays($days))
            ->active()
            ->limit($request->get('limit', 10))
            ->get();

        return response()->json($meetings);
    }

    /**
     * Mark meeting as completed
     */
    public function complete(Request $request, MeetingSchedule $meeting)
    {
        $validated = $request->validate([
            'outcome' => 'nullable|string',
            'action_items' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $meeting->update(array_merge($validated, [
            'status' => MeetingSchedule::STATUS_COMPLETED,
        ]));

        return response()->json([
            'message' => 'Meeting marked as completed',
            'meeting' => $meeting->fresh(),
        ]);
    }

    /**
     * Cancel a meeting
     */
    public function cancel(Request $request, MeetingSchedule $meeting)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $meeting->update([
            'status' => MeetingSchedule::STATUS_CANCELLED,
            'notes' => $meeting->notes . "\n\nCancellation reason: " . ($validated['reason'] ?? 'Not specified'),
        ]);

        return response()->json([
            'message' => 'Meeting cancelled',
            'meeting' => $meeting->fresh(),
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
