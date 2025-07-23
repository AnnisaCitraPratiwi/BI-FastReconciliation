<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'file_type',
        'uploaded_by'
    ];

    // Atau gunakan guarded jika lebih mudah
    // protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by'); 
    }
}
