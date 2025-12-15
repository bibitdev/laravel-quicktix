<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions'; // nama tabel di database

    protected $fillable = [
        'ticket_number',
        'amount',
        'payment_method',
        'transaction_time',
        'cashier_id',
        'created_at',
        'updated_at'
    ];
}
