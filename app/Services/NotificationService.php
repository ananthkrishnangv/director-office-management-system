<?php

namespace App\Services;

use App\Models\User;
use App\Models\Appointment;
use App\Models\MeetingSchedule;
use App\Models\NotificationPreference;
use App\Mail\AppointmentApproved;
use App\Mail\AppointmentRejected;
use App\Mail\MeetingReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send appointment approval notification
     */
    public function sendApprovalNotification(Appointment $appointment): bool
    {
        try {
            $preferences = NotificationPreference::getOrCreateForUser($appointment->director_id);

            // Send email
            if ($preferences->shouldEmailAppointment()) {
                Mail::to($appointment->requester_email)
                    ->send(new AppointmentApproved($appointment));
            }

            // Send WhatsApp
            if ($preferences->shouldWhatsAppAppointment() && $appointment->requester_phone) {
                $this->sendWhatsAppMessage(
                    $appointment->requester_phone,
                    $this->buildApprovalMessage($appointment)
                );
            }

            // Mark as notified
            $appointment->requester_notified = true;
            $appointment->notified_at = now();
            $appointment->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send approval notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send appointment rejection notification
     */
    public function sendRejectionNotification(Appointment $appointment): bool
    {
        try {
            // Send email
            Mail::to($appointment->requester_email)
                ->send(new AppointmentRejected($appointment));

            // Send WhatsApp if phone is available
            if ($appointment->requester_phone) {
                $this->sendWhatsAppMessage(
                    $appointment->requester_phone,
                    $this->buildRejectionMessage($appointment)
                );
            }

            $appointment->requester_notified = true;
            $appointment->notified_at = now();
            $appointment->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send rejection notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send meeting reminder
     */
    public function sendMeetingReminder(MeetingSchedule $meeting): bool
    {
        try {
            $attendees = $meeting->attendees ?? [];
            
            // Send to all attendees with email
            foreach ($attendees as $attendee) {
                if (isset($attendee['email'])) {
                    Mail::to($attendee['email'])->send(new MeetingReminder($meeting));
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send meeting reminder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send WhatsApp message via Twilio
     */
    protected function sendWhatsAppMessage(string $phone, string $message): bool
    {
        try {
            // Check if Twilio credentials are configured
            $accountSid = config('services.twilio.account_sid');
            $authToken = config('services.twilio.auth_token');
            $twilioNumber = config('services.twilio.whatsapp_number');

            if (!$accountSid || !$authToken || !$twilioNumber) {
                Log::warning('Twilio credentials not configured, skipping WhatsApp notification');
                return false;
            }

            // Format phone number
            $formattedPhone = $this->formatPhoneNumber($phone);

            // Note: Actual Twilio integration would go here
            // For now, log the message
            Log::info("WhatsApp message to {$formattedPhone}: {$message}");

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number for WhatsApp
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add India country code if not present
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Build approval message for WhatsApp
     */
    protected function buildApprovalMessage(Appointment $appointment): string
    {
        $date = $appointment->approved_date ?? $appointment->requested_date;
        $time = $appointment->approved_start_time ?? $appointment->requested_start_time;

        return sprintf(
            "✅ Your appointment request has been APPROVED.\n\n" .
            "📅 Date: %s\n" .
            "🕐 Time: %s\n" .
            "📍 Purpose: %s\n\n" .
            "Please arrive 10 minutes before your scheduled time.\n\n" .
            "CSIR-SERC Director's Office",
            $date->format('d M Y'),
            $time,
            $appointment->purpose
        );
    }

    /**
     * Build rejection message for WhatsApp
     */
    protected function buildRejectionMessage(Appointment $appointment): string
    {
        $reason = $appointment->rejection_reason ?? 'Schedule conflict';

        return sprintf(
            "❌ Your appointment request could not be approved.\n\n" .
            "📋 Purpose: %s\n" .
            "📅 Requested Date: %s\n" .
            "📝 Reason: %s\n\n" .
            "You may submit a new request for a different date.\n\n" .
            "CSIR-SERC Director's Office",
            $appointment->purpose,
            $appointment->requested_date->format('d M Y'),
            $reason
        );
    }

    /**
     * Get pending notifications count
     */
    public function getPendingNotificationsCount(int $userId): int
    {
        return Appointment::forDirector($userId)
            ->where('requester_notified', false)
            ->whereIn('status', ['approved', 'rejected'])
            ->count();
    }
}
