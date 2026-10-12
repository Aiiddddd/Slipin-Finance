<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['password_hash'];

    // Kolom password di DB bernama password_hash
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    // Tabel users tidak punya remember_token
    public function getRememberTokenName()
    {
        return '';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }
}
