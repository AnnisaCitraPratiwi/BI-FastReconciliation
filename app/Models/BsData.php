<?php
// app/Models/BsData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BsData extends Model
{
    use HasFactory;

    protected $table = 'bs_data';
    
    protected $fillable = [
        'transaction_date',
        'retrieval_ref_number', // Allow null untuk row kosong
        'tgl_transaksi',
        'nilai_transaksi',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'tgl_transaksi' => 'date',
        'nilai_transaksi' => 'decimal:2',
    ];

    // Scope untuk data dengan retrieval reference
    public function scopeWithReference($query)
    {
        return $query->whereNotNull('retrieval_ref_number')
                    ->where('retrieval_ref_number', '!=', '')
                    ->where('retrieval_ref_number', '!=', '-');
    }

    // Scope untuk data tanpa retrieval reference
    public function scopeWithoutReference($query)
    {
        return $query->where(function($q) {
            $q->whereNull('retrieval_ref_number')
              ->orWhere('retrieval_ref_number', '')
              ->orWhere('retrieval_ref_number', '-');
        });
    }

    public static function getAvailableDates()
    {
        return self::distinct()
            ->orderBy('tgl_transaksi', 'desc')
            ->pluck('tgl_transaksi');
    }

    public static function getSummaryByDate($date)
    {
        return self::where('tgl_transaksi', $date)
            ->selectRaw('
                SUM(CASE WHEN nilai_transaksi > 0 THEN nilai_transaksi ELSE 0 END) as total_kredit,
                SUM(CASE WHEN nilai_transaksi < 0 THEN ABS(nilai_transaksi) ELSE 0 END) as total_debit,
                COUNT(CASE WHEN nilai_transaksi > 0 THEN 1 END) as volume_kredit,
                COUNT(CASE WHEN nilai_transaksi < 0 THEN 1 END) as volume_debit
            ')
            ->first();
    }

    // TAMBAHAN: Method untuk statistik data kosong
    public static function getEmptyDataStats($date)
    {
        return [
            'total_records' => self::where('tgl_transaksi', $date)->count(),
            'with_reference' => self::withReference()->where('tgl_transaksi', $date)->count(),
            'without_reference' => self::withoutReference()->where('tgl_transaksi', $date)->count(),
        ];
    }
}
