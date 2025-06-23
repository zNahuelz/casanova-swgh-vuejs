<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'date',
        'time',
        'notes',
        'status',
        'duration',
        'rescheduling_date',
        'rescheduling_time',
        'is_treatment',
        'is_remote',
        'patient_id',
        'doctor_id',
        'created_by',
        'updated_by'
    ];


    protected $casts = [
        'status' => AppointmentStatus::class,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'is_treatment');
    }

    public function voucherDetails(): HasMany
    {
        return $this->hasMany(VoucherDetail::class);
    }

    public function pendingPayments(): HasMany
    {
        return $this->hasMany(PendingPayment::class); 
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
