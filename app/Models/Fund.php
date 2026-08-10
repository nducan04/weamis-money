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

    public static function syncBalance(): float
    {
        $contrib = Transaction::where('status', 'approved')->where('type', 'contribution')->sum('amount');
        $repay = Transaction::where('status', 'approved')->where('type', 'repayment')->sum('amount');
        $adjust = Transaction::where('status', 'approved')->where('type', 'adjustment')->sum('amount');
        $profit = Transaction::where('status', 'approved')->where('type', 'profit')->sum('amount');
        $expense = Transaction::where('status', 'approved')->where('type', 'expense')->sum('amount');
        $loan = Transaction::where('status', 'approved')->where('type', 'loan')->sum('amount');
        $withdraw = Transaction::where('status', 'approved')->where('type', 'withdrawal')->sum('amount');
        $distrib = Transaction::where('status', 'approved')->where('type', 'distribution')->sum('amount');

        $total = ($contrib + $repay + $adjust + $profit) - ($expense + $loan + $withdraw + $distrib);
        
        $fund = self::first();
        if ($fund) {
            $fund->update(['balance' => $total]);
        }
        
        Account::where('type', 'fund')->update(['balance' => $total]);
        
        return $total;
    }
}
