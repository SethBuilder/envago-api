<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = ['name', 'email', 'password', 'role', 'activation_token', 'activation_token_expires_at', 'email_verified_at'];

    protected $hidden = ['password', 'remember_token', 'activation_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'activation_token_expires_at' => 'datetime',
            'password'                   => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isProvider(): bool
    {
        return $this->role === 'provider';
    }

    public function isActivated(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function provider()
    {
        return $this->hasOne(Provider::class);
    }
}
