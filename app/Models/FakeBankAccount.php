<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FakeBankAccount extends Model
{
        protected $table = 'fake_bank_accounts';

    protected $fillable = [
        'bank_name',
        'account_number',
        'routing',
        'account_holder',
        'account_type',
        'balance',
    ];

    public $timestamps = true;
}
