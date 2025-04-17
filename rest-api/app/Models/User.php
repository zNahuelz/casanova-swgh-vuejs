<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory,Notifiable,HasApiTokens,SoftDeletes;
    protected $fillable = [
        'username',
        'password',
        'email',
        'role_id'
    ];

    protected $hiddden = [
        'password'
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'username' => $this->username,
            'role_id' => $this->role_id,
            'role' => $this->role->name,
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function worker(): HasOne
    {
        return $this->hasOne(Worker::class);
    }

}
