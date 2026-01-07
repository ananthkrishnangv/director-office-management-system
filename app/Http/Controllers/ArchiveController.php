<?php

namespace App\Http\Controllers;

use App\Models\MeetingSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');

        $query = MeetingSchedule::with(['createdBy'])
            ->orderBy('start_time', 'desc');

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('visitor_name', 'like', "%{$search}%")
                    ->orWhere('visitor_organization', 'like', "%{$search}%");
            });
        }

        $meetings = $query->paginate(20);

        // Get counts for tabs
        $counts = [
            'all' => MeetingSchedule::count(),
            'approved' => MeetingSchedule::where('status', 'approved')->count(),
            'completed' => MeetingSchedule::where('status', 'completed')->count(),
            'cancelled' => MeetingSchedule::where('status', 'cancelled')->count(),
            'rejected' => MeetingSchedule::where('status', 'rejected')->count(),
            'walkin' => MeetingSchedule::where('meeting_type', 'walkin')->count(),
        ];

        return Inertia::render('Archive/Index', [
            'meetings' => $meetings,
            'counts' => $counts,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function show(MeetingSchedule $meeting)
    {
        $meeting->load(['requestedBy', 'approvedBy']);

        return Inertia::render('Archive/Show', [
            'meeting' => $meeting,
        ]);
    }

    public function update(Request $request, MeetingSchedule $meeting)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:5000',
            'minutes' => 'nullable|string|max:10000',
        ]);

        $meeting->update($validated);

        return back()->with('success', 'Meeting details updated.');
    }

    public function uploadDocument(Request $request, MeetingSchedule $meeting)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:10240',
        ]);

        $path = $request->file('document')->store('meeting-documents', 'public');

        // Store document path (you may want a separate documents table)
        $documents = $meeting->documents ?? [];
        $documents[] = [
            'path' => $path,
            'name' => $request->file('document')->getClientOriginalName(),
            'uploaded_at' => now()->toISOString(),
        ];

        $meeting->update(['documents' => $documents]);

        return back()->with('success', 'Document uploaded successfully.');
    }
}
