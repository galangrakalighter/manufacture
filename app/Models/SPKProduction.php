<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPKProduction extends Model
{
    protected $table = 'spk_production';

    protected $fillable = [
        'spk_warehouse_id',
        'pic_id',
        'spk_number',
        'status',
        'cabang',
        'catatan'
    ];

    public function warehouse()
    {
        return $this->belongsTo(
            SPKWarehouse::class,
            'spk_warehouse_id',
            'id'
        );
    }

    public function pic()
    {
        return $this->belongsTo(
            User::class,
            'pic_id'
        );
    }
}
