<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReconciliationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'reconciliation_date',
        'user_id',
        'total_anomalies',
        'cip_records',
        'ams_records',
        'bs_records',
        'excel_file_path',
        'pdf_file_path',
        'summary_data'
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'summary_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}