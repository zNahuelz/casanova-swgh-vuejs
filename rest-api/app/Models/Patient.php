<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
        'name',
        'paternal_surname',
        'maternal_surname',
        'dni',
        'email',
        'phone',
        'address',
        'created_by',
        'updated_by'
    ];

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function createdBy(): BelongsTo
    {
        //Wip...
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        //Wip...
        return $this->belongsTo(User::class, 'created_by');
    }
}
