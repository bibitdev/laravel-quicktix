<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'type',
        'is_long_weekend',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_long_weekend' => 'boolean',
    ];

    /**
     * Check if a given date is a holiday
     */
    public static function isHoliday($date)
    {
        return self::whereDate('date', $date)->exists();
    }

    /**
     * Get holiday info for a given date
     */
    public static function getHolidayInfo($date)
    {
        return self::whereDate('date', $date)->first();
    }
}
