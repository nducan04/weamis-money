<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'release_date',
        'weamis_fund_percentage',
        'fund_credited_amount',
        'lead_user_id',
        'created_by_user_id',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function lead()
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->withPivot('share_percentage')
                    ->withTimestamps();
    }

    public function projectMembers()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function canManage(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        if ($this->lead_user_id && $this->lead_user_id == $user->id) {
            return true;
        }
        if ($this->created_by_user_id && $this->created_by_user_id == $user->id) {
            return true;
        }
        return false;
    }
}
