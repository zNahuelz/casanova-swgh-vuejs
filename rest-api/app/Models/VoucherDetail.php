<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VoucherDetail extends Model
{
    protected $fillable = [
        'amount',
        'unit_price',
        'subtotal',
        'voucher_id',
        'medicine_id',
        'treatment_id',
        'appointment_id',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class,'voucher_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
