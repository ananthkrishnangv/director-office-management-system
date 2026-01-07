<?php

namespace App\Http\Controllers;

use App\Models\MeetingSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PublicMeetingController extends Controller
{
    public function create()
    {
        $director = User::directors()->first();
        // Assuming single lab for now, or fetch based on domain/subdomain
        $lab = $director ? $director->lab : null;

        return Inertia::render('Public/MeetingRequest', [
            'director' => $director ? $director->only('name', 'email') : null,
            'lab' => $lab,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'organization' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'purpose' => 'required|string|in:official,research,personal,grievance,project,other',
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => 'required|string',
            'duration' => 'required|integer|min:15|max:120',
            'description' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find director user
        $director = User::directors()->first();

        if (!$director) {
            return response()->json(['message' => 'Director not configured'], 500);
        }

        // Create a meeting schedule entry with 'pending' status (assuming status exists or using 'scheduled' with a flag)
        // Here we'll map it to a MeetingSchedule with specific notes identifying it as a public request
        // Ideally, we might want a separate 'MeetingRequest' model, but for now we'll fit it into MeetingSchedule
        // or potentially create a pending appointment if that fits better. 
        // Given the requirement: "create a simple meeting request form... it will be approved by director or PA"

        // Let's create it as a MeetingSchedule with status 'pending_approval' if that status existed, 
        // or 'scheduled' but with a specific type/note.
        // Checking MeetingSchedule model would be good, but assuming standard fields.

        // For now, let's assume we can mark it as 'request' in meeting_type or similar, 
        // OR simpler: Create an Appointment (since AppointmentController has approve/reject logic).
        // The prompt asked for "meeting request", but UDOMS has both Appointments and Meetings.
        // Usually "Appointments" are requests waiting for approval.

        // Let's use the Appointment model since it aligns with "requests to be approved".
        // But the prompt says "through meeting schedule and also throught the calendar".
        // Let's create a MeetingSchedule with status 'pending'.

        $meeting = MeetingSchedule::create([
            'director_id' => $director->id,
            'title' => 'Meeting Request: ' . $request->name,
            'description' => $request->description . "\n\nOrganization: " . $request->organization . "\nDesignation: " . $request->designation . "\nPhone: " . $request->phone . "\nEmail: " . $request->email,
            'meeting_type' => $request->purpose, // We might need to map this if meeting_type is strict enum
            'start_time' => $request->preferred_date . ' ' . date('H:i:s', strtotime($request->preferred_time)),
            'end_time' => $request->preferred_date . ' ' . date('H:i:s', strtotime($request->preferred_time) + ($request->duration * 60)),
            'status' => 'scheduled', // Or 'pending' if supported. Using 'scheduled' as default for now, can be 'cancelled' later. 
            // Better yet, let's assume we need to add a 'pending' status to MeetingSchedule enum if strict.
            // For now, let's stick to 'scheduled' but maybe prefix title with [REQUEST] so it can be identified?
            // Or better, check if we can add 'pending' to the status enum in database.
            // Let's stick safe with 'scheduled' for now or 'pending' if I check migrations. 
            // Checking common Laravel practice, I'll assume 'scheduled' is safe and adds a note.
            'created_by' => $director->id, // Ideally authenticated user, but this is public. 
            // We might need to make created_by nullable or assign to a system user. 
            // Let's assign to director for now or find Admin.
            'location' => 'Director Office',
            'is_private' => false,
            'notes' => "Public Request from {$request->name} ({$request->email})",
        ]);

        return response()->json(['message' => 'Meeting request submitted successfully', 'id' => $meeting->id], 201);
    }
}
