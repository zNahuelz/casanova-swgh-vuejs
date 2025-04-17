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
        'presentation',
        'created_by',
        'updated_by'
    ];

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function voucherDetail(): HasMany
    {
        return $this->hasMany(VoucherDetail::class);
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
