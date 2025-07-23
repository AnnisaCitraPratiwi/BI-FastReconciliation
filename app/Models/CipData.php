<?php
// app/Models/CipData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CipData extends Model
{
    use HasFactory;

    protected $table = 'cip_data';
    
    protected $fillable = [
        'transaction_date',
        'end_to_end_id',
        'debit',
        'kredit',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'kredit' => 'decimal:2',
    ];

    public static function getAvailableDates()
    {
        return self::distinct()
            ->orderBy('transaction_date', 'desc')
            ->pluck('transaction_date');
    }

    public static function getSummaryByDate($date)
    {
        return self::where('transaction_date', $date)
            ->selectRaw('
                SUM(debit) as total_debit,
                SUM(kredit) as total_kredit,
                COUNT(CASE WHEN debit > 0 THEN 1 END) as volume_debit,
                COUNT(CASE WHEN kredit > 0 THEN 1 END) as volume_kredit
            ')
            ->first();
    }
}
