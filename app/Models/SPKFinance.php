<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPKFinance extends Model
{
    protected $table = 'spk_finance';

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
}
