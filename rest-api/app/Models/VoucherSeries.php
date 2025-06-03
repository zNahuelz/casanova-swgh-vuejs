<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherSeries extends Model
{
    protected $fillable = [
        'voucher_type',
        'serie',
        'next_correlative',
        'is_active'
    ];

    public function voucherType(): BelongsTo
    {
        return $this->belongsTo(VoucherType::class,'voucher_type');
    }
}
