<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorUnavailability extends Model
{
    protected $fillable = [
        'doctor_id',
        'start_datetime',
        'end_datetime',
        'reason'
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
