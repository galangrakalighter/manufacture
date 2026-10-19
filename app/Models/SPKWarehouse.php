<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPKWarehouse extends Model
{
    protected $table = 'spk_warehouse';

    protected $fillable = [
        'quotation_id',
        'spk_number',
        'type',
        'status',
        'cabang',
        'catatan'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function spkProduction()
    {
        return $this->hasOne(SPKProduction::class, 'spk_warehouse_id');
    }
}
