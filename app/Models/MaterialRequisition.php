<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequisition extends Model
{
    protected $table = 'material_requisitions';
    protected $fillable = ['user_id', 'status', 'catatan', 'approved_by', 'approved_at', 'cabang'];

    // Relasi: User yang membuat permintaan
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: User yang menyetujui (approver)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Relasi: Isi dari permintaan tersebut (banyak barang)
    public function items()
    {
        return $this->hasMany(RequisitionItem::class, 'requisition_id');
    }
}
