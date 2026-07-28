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
        'type',
        'amount',
        'description',
        'status',
        'approved_by',
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
