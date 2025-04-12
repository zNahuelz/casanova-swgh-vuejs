<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoucherType extends Model
{
    protected $fillable = [
        'name'
    ];

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }
}
