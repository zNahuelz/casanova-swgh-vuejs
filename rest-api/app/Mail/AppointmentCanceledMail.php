<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCanceledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $refund;
    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment,$refund)
    {
        $this->appointment = $appointment;
        $this->refund = $refund;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ALTERNATIVA CASANOVA - CANCELACIÓN DE CITA',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment.canceled',
            with: [
                'appointment' => $this->appointment,
                'refund' => $this->refund,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
