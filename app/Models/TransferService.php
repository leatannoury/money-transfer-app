<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferService extends Model
{
    protected $fillable = [
        'name',
        'source_type',
        'destination_type',
        'destination_country',
        'speed',
        'fee',
        'exchange_rate',
        'promotion_active',
        'promotion_text',
        'destination_currency'
    ];
}
