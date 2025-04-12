<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    protected $fillable = [
        'name',
        'composition',
        'description',
        'barcode',
        'buy_price',
        'sell_price',
        'igv',
        'profit',
        'stock',
        'salable',
        'presentation'
    ];

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    public function treatmentRequeriments(): HasMany
    {
        return $this->hasMany(TreatmentRequirement::class);
    }

    public function voucherDetail(): HasMany
    {
        return $this->hasMany(VoucherDetail::class);
    }
}
