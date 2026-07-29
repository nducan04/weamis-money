<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'role',
        'avatar',
        'share_percentage',
        'current_debt',
    ];

    protected function casts(): array
    {
        return [
            'share_percentage' => 'float',
            'current_debt' => 'float',
        ];
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
