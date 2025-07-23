<?php
// app/Models/AmsData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmsData extends Model
{
    use HasFactory;

    protected $table = 'ams_data';
    
    protected $fillable = [
        'transaction_date',
        'reference_number',
        'bifast_reference_number',
        'trx_amount',
        'trx_source',
        'source_account_number',
        'destination_account_number',
        'trx_date_time',
        'trx_status',
        'debit_status',
        'credit_status',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'trx_date_time' => 'datetime',
        'trx_amount' => 'decimal:2',
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
                SUM(CASE WHEN trx_amount > 0 THEN trx_amount ELSE 0 END) as total_kredit,
                SUM(CASE WHEN trx_amount < 0 THEN ABS(trx_amount) ELSE 0 END) as total_debit,
                COUNT(CASE WHEN trx_amount > 0 THEN 1 END) as volume_kredit,
                COUNT(CASE WHEN trx_amount < 0 THEN 1 END) as volume_debit
            ')
            ->first();
    }

    // Method untuk reconciliation CIP vs AMS
    public static function getReconciliationWithCip($date)
    {
        return self::leftJoin('cip_data', function($join) use ($date) {
            $join->on('ams_data.bifast_reference_number', '=', 'cip_data.end_to_end_id')
                 ->where('cip_data.transaction_date', $date);
        })
        ->where('ams_data.transaction_date', $date)
        ->select(
            'ams_data.*',
            'cip_data.end_to_end_id',
            'cip_data.debit as cip_debit',
            'cip_data.kredit as cip_kredit'
        )
        ->get();
    }

    // Method untuk reconciliation AMS vs BS
    public static function getReconciliationWithBs($date)
    {
        return self::leftJoin('bs_data', function($join) use ($date) {
            $join->on('ams_data.reference_number', '=', 'bs_data.retrieval_ref_number')
                 ->where('bs_data.tgl_transaksi', $date);
        })
        ->where('ams_data.transaction_date', $date)
        ->select(
            'ams_data.*',
            'bs_data.retrieval_ref_number',
            'bs_data.nilai_transaksi as bs_amount'
        )
        ->get();
    }

    // Scope untuk data dengan reference number
    public function scopeWithReference($query)
    {
        return $query->whereNotNull('reference_number')
                    ->where('reference_number', '!=', '');
    }

    // Scope untuk data tanpa reference number
    public function scopeWithoutReference($query)
    {
        return $query->where(function($q) {
            $q->whereNull('reference_number')
              ->orWhere('reference_number', '');
        });
    }
}
