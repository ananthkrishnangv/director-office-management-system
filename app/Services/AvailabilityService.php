<?php

namespace App\Services;

use App\Models\DirectorAvailability;
use App\Models\BlockedDate;
use App\Models\MeetingSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityService
{
    /**
     * Get available slots for a director on a specific date
     */
    public function getAvailableSlots(int $directorId, Carbon $date): array
    {
        // Check if date is blocked
        if (BlockedDate::isDateBlocked($directorId, $date)) {
            return [];
        }

        // Get availability rules for this day of week
        $dayOfWeek = $date->dayOfWeek;
        $rules = DirectorAvailability::forDirector($directorId)
            ->forDay($dayOfWeek)
            ->available()
            ->get();

        if ($rules->isEmpty()) {
            return [];
        }

        $slots = [];

        foreach ($rules as $rule) {
            $ruleSlots = $this->generateSlotsFromRule($rule, $date, $directorId);
            $slots = array_merge($slots, $ruleSlots);
        }

        return $slots;
    }

    /**
     * Generate time slots from an availability rule
     */
    protected function generateSlotsFromRule(DirectorAvailability $rule, Carbon $date, int $directorId): array
    {
        $slots = [];
        $slotDuration = $rule->slot_duration_minutes;
        $bufferMinutes = $rule->buffer_minutes;

        // Parse start and end times
        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $rule->start_time);
        $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $rule->end_time);

        // Get existing meetings for this date
        $existingMeetings = MeetingSchedule::forDirector($directorId)
            ->whereDate('start_time', $date)
            ->active()
            ->get();

        $current = $startTime->copy();

        while ($current->copy()->addMinutes($slotDuration)->lte($endTime)) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($slotDuration);

            // Check if minimum advance time is met
            if ($slotStart->isFuture()) {
                $hoursUntilSlot = now()->diffInHours($slotStart, false);
                
                if ($hoursUntilSlot >= $rule->min_advance_hours) {
                    // Check for conflicts with existing meetings
                    $hasConflict = $this->hasConflict($slotStart, $slotEnd, $existingMeetings);

                    if (!$hasConflict) {
                        $slots[] = [
                            'start' => $slotStart->format('H:i'),
                            'end' => $slotEnd->format('H:i'),
                            'start_datetime' => $slotStart->toIso8601String(),
                            'end_datetime' => $slotEnd->toIso8601String(),
                            'available' => true,
                        ];
                    }
                }
            }

            // Move to next slot, including buffer
            $current->addMinutes($slotDuration + $bufferMinutes);
        }

        return $slots;
    }

    /**
     * Check if a time slot has conflicts with existing meetings
     */
    protected function hasConflict(Carbon $start, Carbon $end, $meetings): bool
    {
        foreach ($meetings as $meeting) {
            $meetingStart = $meeting->start_time;
            $meetingEnd = $meeting->end_time;

            // Check for overlap
            if ($start < $meetingEnd && $end > $meetingStart) {
                return true;
            }
        }

        return false;
    }

    /**
     * Suggest the best available slots for a date range
     */
    public function suggestSlots(int $directorId, Carbon $fromDate, Carbon $toDate, int $count = 5): array
    {
        $suggestions = [];
        $period = CarbonPeriod::create($fromDate, $toDate);

        foreach ($period as $date) {
            if (count($suggestions) >= $count) {
                break;
            }

            $slots = $this->getAvailableSlots($directorId, $date);

            foreach ($slots as $slot) {
                if (count($suggestions) >= $count) {
                    break;
                }

                $suggestions[] = [
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $date->format('l'),
                    'slot' => $slot,
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Check if a specific time slot is available
     */
    public function isSlotAvailable(int $directorId, Carbon $startTime, Carbon $endTime): bool
    {
        // Check if date is blocked
        if (BlockedDate::isDateBlocked($directorId, $startTime->toDateString())) {
            return false;
        }

        // Check for existing meetings
        $conflict = MeetingSchedule::forDirector($directorId)
            ->active()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->exists();

        return !$conflict;
    }

    /**
     * Get the next available slot
     */
    public function getNextAvailableSlot(int $directorId, int $durationMinutes = 30): ?array
    {
        $maxDays = 30; // Look up to 30 days ahead
        $date = Carbon::tomorrow();

        for ($i = 0; $i < $maxDays; $i++) {
            $slots = $this->getAvailableSlots($directorId, $date);

            if (!empty($slots)) {
                return [
                    'date' => $date->format('Y-m-d'),
                    'slot' => $slots[0],
                ];
            }

            $date->addDay();
        }

        return null;
    }
}
