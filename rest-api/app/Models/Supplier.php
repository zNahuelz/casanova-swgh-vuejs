<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'ruc',
        'address',
        'phone',
        'email',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'description' => 'PROVEEDOR GENERAL'
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
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
