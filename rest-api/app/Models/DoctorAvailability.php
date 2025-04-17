<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorAvailability extends Model
{
    protected $fillable = [
        'doctor_id',
        'weekday',
        'start_time',
        'end_time',
        'is_active'
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
