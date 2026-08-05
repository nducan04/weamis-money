<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'balance',
    ];

    protected $casts = [
        'balance' => 'float',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class)->latest();
    }
}
