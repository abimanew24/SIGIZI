<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // <--- INI WAJIB


class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles; // <--- INI WAJIB

    protected $fillable = [
        'name',
        'email',
        'password',
        'jabatan',   // Agar kolom jabatan bisa diisi
        'is_active', // Agar status aktif bisa diisi
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];
    
}