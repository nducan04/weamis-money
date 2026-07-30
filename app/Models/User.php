<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
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

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_members')
                    ->withPivot('share_percentage')
                    ->withTimestamps();
    }

    public function ledProjects()
    {
        return $this->hasMany(Project::class, 'lead_user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isLead(): bool
    {
        return $this->role === 'lead' || $this->role === 'admin';
    }
}
