<?php

namespace App\Models;

use App\Enums\WorkerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Worker extends Model
{
    protected $fillable = [
        'name',
        'paternal_surname',
        'maternal_surname',
        'dni',
        'email',
        'phone',
        'address',
        'hiring_date',
        'user_id',
        'position',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'position' => WorkerType::class,
        'hiring_date' => 'date'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
