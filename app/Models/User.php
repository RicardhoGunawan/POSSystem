<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Cek apakah user memiliki role "admin"
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Cek apakah user memiliki role "cashier"
     */
    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    /**
     * Dapatkan role pertama dari user
     */
    public function getRoleName(): string
    {
        return $this->roles->first()->name ?? 'No Role';
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
