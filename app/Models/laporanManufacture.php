<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class laporanManufacture extends Model
{
    protected $table = 'laporan_manufacture';

    protected $fillable = [
        'quotation_id',
        'hasil_jadi',
        'waste',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }
}
