<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use SoftDeletes;

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
        return $this->belongsTo(Presentation::class, 'presentation');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class,'medicine_suppliers');
    }

    public function voucherDetail(): HasMany
    {
        return $this->hasMany(VoucherDetail::class);
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
