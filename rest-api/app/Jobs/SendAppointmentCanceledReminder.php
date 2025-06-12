<?php

namespace App\Jobs;

use App\Mail\AppointmentCanceledMail;
use App\Models\Appointment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendAppointmentCanceledReminder implements ShouldQueue
{
    use Queueable;

    public $appointment;
    public $refund;
    /**
     * Create a new job instance.
     */
    public function __construct(Appointment $appointment,$refund)
    {
        $this->appointment = $appointment;
        $this->refund = $refund;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->appointment->patient->email)
        ->send(new AppointmentCanceledMail($this->appointment,$this->refund));
    }
}
