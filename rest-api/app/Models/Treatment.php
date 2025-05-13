<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treatment extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'description',
        'procedure',
        'price',
        'created_by',
        'updated_by'
    ];


    public function voucherDetails(): HasMany
    {
        return $this->hasMany(VoucherDetail::class);
    }

    public function appointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class);
    }
    
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
