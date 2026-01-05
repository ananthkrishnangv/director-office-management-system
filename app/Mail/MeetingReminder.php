<?php

namespace App\Mail;

use App\Models\MeetingSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetingReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MeetingSchedule $meeting) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Meeting Reminder - ' . $this->meeting->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.meeting-reminder',
            with: ['meeting' => $this->meeting],
        );
    }
}
