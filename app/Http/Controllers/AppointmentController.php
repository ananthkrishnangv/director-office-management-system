<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MeetingSchedule;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    protected AvailabilityService $availabilityService;
    protected NotificationService $notificationService;

    public function __construct(
        AvailabilityService $availabilityService,
        NotificationService $notificationService
    ) {
        $this->availabilityService = $availabilityService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display list of appointments
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Appointment::with(['director', 'createdBy']);

        // Filter by director
        if ($user->isDirector()) {
            $query->forDirector($user->id);
        }

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->where('requested_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('requested_date', '<=', $request->date_to);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('requester_name', 'ilike', "%{$request->search}%")
                    ->orWhere('requester_email', 'ilike', "%{$request->search}%")
                    ->orWhere('purpose', 'ilike', "%{$request->search}%");
            });
        }

        $appointments = $query->orderBy('requested_date', 'desc')
            ->orderBy('requested_start_time', 'desc')
            ->paginate(15);

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
            'filters' => $request->only(['status', 'date_from', 'date_to', 'search']),
            'statuses' => Appointment::getStatuses(),
        ]);
    }

    /**
     * Show create appointment form
     */
    public function create()
    {
        return Inertia::render('Appointments/Create', [
            'meetingTypes' => Appointment::getMeetingTypes(),
            'directors' => User::directors()->active()->get(['id', 'name']),
        ]);
    }

    /**
     * Store a new appointment request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'director_id' => 'required|exists:users,id',
            'requester_name' => 'required|string|max:255',
            'requester_email' => 'required|email|max:255',
            'requester_phone' => 'nullable|string|max:20',
            'requester_organization' => 'nullable|string|max:255',
            'requester_designation' => 'nullable|string|max:255',
            'purpose' => 'required|string|max:500',
            'description' => 'nullable|string',
            'meeting_type' => 'required|in:' . implode(',', array_keys(Appointment::getMeetingTypes())),
            'requested_date' => 'required|date|after_or_equal:today',
            'requested_start_time' => 'required|date_format:H:i',
            'requested_end_time' => 'nullable|date_format:H:i|after:requested_start_time',
            'duration_minutes' => 'nullable|integer|min:15|max:240',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ]);

        $validated['created_by'] = $request->user()?->id;
        $validated['source'] = $request->user() ? 'manual' : 'portal';
        $validated['status'] = Appointment::STATUS_PENDING;

        $appointment = Appointment::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Appointment request submitted successfully',
                'appointment' => $appointment,
            ], 201);
        }

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment request submitted successfully');
    }

    /**
     * Show appointment details
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['director', 'createdBy', 'meetingSchedule']);

        return Inertia::render('Appointments/Show', [
            'appointment' => $appointment,
        ]);
    }

    /**
     * Approve an appointment
     */
    public function approve(Request $request, Appointment $appointment)
    {

        if (!$appointment->isPending()) {
            return response()->json([
                'message' => 'Only pending appointments can be approved',
            ], 422);
        }

        $validated = $request->validate([
            'approved_date' => 'nullable|date',
            'approved_start_time' => 'nullable|date_format:H:i',
            'approved_end_time' => 'nullable|date_format:H:i',
            'admin_notes' => 'nullable|string',
        ]);

        // Approve the appointment
        $appointment->approve(
            $validated['approved_date'] ?? null,
            $validated['approved_start_time'] ?? null,
            $validated['approved_end_time'] ?? null
        );

        if (isset($validated['admin_notes'])) {
            $appointment->admin_notes = $validated['admin_notes'];
            $appointment->save();
        }

        // Create a meeting schedule from this appointment
        $meeting = $this->createMeetingFromAppointment($appointment);

        // Send notification
        $this->notificationService->sendApprovalNotification($appointment);

        return response()->json([
            'message' => 'Appointment approved successfully',
            'appointment' => $appointment->fresh(),
            'meeting' => $meeting,
        ]);
    }

    /**
     * Reject an appointment
     */
    public function reject(Request $request, Appointment $appointment)
    {

        if (!$appointment->isPending()) {
            return response()->json([
                'message' => 'Only pending appointments can be rejected',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
            'admin_notes' => 'nullable|string',
        ]);

        $appointment->reject($validated['rejection_reason']);

        if (isset($validated['admin_notes'])) {
            $appointment->admin_notes = $validated['admin_notes'];
            $appointment->save();
        }

        // Send notification
        $this->notificationService->sendRejectionNotification($appointment);

        return response()->json([
            'message' => 'Appointment rejected',
            'appointment' => $appointment->fresh(),
        ]);
    }

    /**
     * Get available slots suggestion
     */
    public function suggestSlots(Request $request)
    {
        $validated = $request->validate([
            'director_id' => 'required|exists:users,id',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after:from_date',
            'count' => 'nullable|integer|min:1|max:20',
        ]);

        $fromDate = Carbon::parse($validated['from_date']);
        $toDate = Carbon::parse($validated['to_date']);
        $count = $validated['count'] ?? 5;

        $slots = $this->availabilityService->suggestSlots(
            $validated['director_id'],
            $fromDate,
            $toDate,
            $count
        );

        return response()->json([
            'slots' => $slots,
        ]);
    }

    /**
     * Create a meeting schedule from approved appointment
     */
    protected function createMeetingFromAppointment(Appointment $appointment): MeetingSchedule
    {
        $date = $appointment->approved_date ?? $appointment->requested_date;
        $startTime = $appointment->approved_start_time ?? $appointment->requested_start_time;
        $endTime = $appointment->approved_end_time ?? $appointment->requested_end_time;

        // Calculate end time if not provided
        if (!$endTime) {
            $endTime = Carbon::parse($startTime)->addMinutes($appointment->duration_minutes)->format('H:i');
        }

        return MeetingSchedule::create([
            'director_id' => $appointment->director_id,
            'appointment_id' => $appointment->id,
            'created_by' => auth()->id(),
            'title' => "Meeting with {$appointment->requester_name}",
            'description' => $appointment->purpose . ($appointment->description ? "\n\n" . $appointment->description : ''),
            'meeting_type' => $appointment->meeting_type,
            'start_time' => Carbon::parse($date->format('Y-m-d') . ' ' . $startTime),
            'end_time' => Carbon::parse($date->format('Y-m-d') . ' ' . $endTime),
            'attendees' => [
                [
                    'name' => $appointment->requester_name,
                    'email' => $appointment->requester_email,
                    'phone' => $appointment->requester_phone,
                    'organization' => $appointment->requester_organization,
                ],
            ],
            'status' => MeetingSchedule::STATUS_SCHEDULED,
            'priority' => $appointment->priority,
        ]);
    }

    /**
     * API endpoint for list
     */
    public function apiIndex(Request $request)
    {
        $user = $request->user();
        $query = Appointment::with(['director']);

        if ($user->isDirector()) {
            $query->forDirector($user->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('requested_date', 'desc')->get();

        return response()->json($appointments);
    }
}
