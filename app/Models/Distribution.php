<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'fund_id',
        'total_amount',
        'note',
        'payout_details',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'payout_details' => 'array',
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
