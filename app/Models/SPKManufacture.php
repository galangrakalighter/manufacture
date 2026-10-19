<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPKManufacture extends Model
{
    protected $table = 'spk_manufacture';

    protected $fillable = [
        'quotation_id',
        'spk_number',
        'type',
        'status',
        'cabang',
        'catatan',
        'warehouse',
        'production'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }
}
