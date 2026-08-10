<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['type', 'owner_type', 'owner_id', 'name', 'balance'];

    public function owner()
    {
        return $this->morphTo();
    }

    public function journalEntriesAsFrom()
    {
        return $this->hasMany(JournalEntry::class, 'from_account_id');
    }

    public function journalEntriesAsTo()
    {
        return $this->hasMany(JournalEntry::class, 'to_account_id');
    }
}
