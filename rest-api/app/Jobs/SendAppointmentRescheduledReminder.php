<?php

namespace App\Jobs;

use App\Mail\AppointmentRescheduledReminderMail;
use App\Models\Appointment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendAppointmentRescheduledReminder implements ShouldQueue
{
    use Queueable;

    public $appointment;
    /**
     * Create a new job instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->appointment->patient->email)
        ->send(new AppointmentRescheduledReminderMail($this->appointment));
    }
}
