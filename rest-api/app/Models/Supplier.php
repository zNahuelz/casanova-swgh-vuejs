<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes; 
    
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

    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(Medicine::class,'medicine_suppliers');
    }

    public function createdBy(): BelongsTo
    {
        //Wip...
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        //Wip...
        return $this->belongsTo(User::class, 'updated_by');
    }

}
