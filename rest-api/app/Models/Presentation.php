<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presentation extends Model
{
    protected $fillable = [
        'name',
        'numeric_value',
        'aux'
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
