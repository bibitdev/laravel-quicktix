<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions'; // nama tabel di database

    protected $fillable = [
        'amount',
        'created_at',
        'updated_at'
    ];
}
