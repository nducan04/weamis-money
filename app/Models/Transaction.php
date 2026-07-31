<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fund_id',
        'user_id',
        'project_id',
        'responsible_user_id',
        'claimant_user_id',
        'type',
        'amount',
        'description',
        'billing_cycle',
        'evidence_type',
        'evidence_value',
        'status',
        'approved_by',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function claimantUser()
    {
        return $this->belongsTo(User::class, 'claimant_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
