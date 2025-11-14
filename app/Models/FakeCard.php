<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FakeCard extends Model
{
    protected $table = 'fake_cards';

    protected $fillable = [
        'card_number',
        'provider',
        'cardholder_name',
        'expiry',
        'cvv',
        'balance',
    ];

    public $timestamps = true;
}
