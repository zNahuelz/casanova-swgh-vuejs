<?php

namespace App\Jobs;

use App\Mail\ForgotPasswordMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendForgotPasswordToken implements ShouldQueue
{
    use Queueable;

    public $user;
    public $recoveryLink;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $recoveryLink)
    {
        $this->user = $user;
        $this->recoveryLink = $recoveryLink;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)
        ->send(new ForgotPasswordMail($this->user,$this->recoveryLink));
    }
}
