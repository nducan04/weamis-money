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
        'weamis_fund_percentage',
        'lead_user_id',
    ];

    public function lead()
    {
        return $this->belongsTo(User::class, 'lead_user_id');
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
}
