<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $table = 'quotations';
    
    protected $fillable = [
        'quotation_number',
        'nama_customer',
        'alamat_customer',
        'penerima',
        'alamat_penerima',
        'id_sales',
        'tanggal_pesan',
        'tipe_pemesanan',
        'judul_cetak',
        'perbox',
        'ukuran',
        'perporasi',
        'jumlah_box',
        'jumlah_ply',
        'keterangan',
        'id_barang',
        'quantity',
        'harga',
        'total_amount',
        'terms_and_conditions',
        'cabang',
        'status',
        'approved_by',
        'approved_at',
        'keterangan_reject',
        'film'
    ];

    // Relasi ke Sales / User
    public function sales()
    {
        return $this->belongsTo(User::class, 'id_sales');
    }

    // Relasi ke Barang / Warehouse (Inventory)
    public function barang()
    {
        return $this->belongsTo(Warehouse::class, 'id_barang');
    }

    public function laporan()
    {
        return $this->hasOne(laporanManufacture::class, 'quotation_id');
    }
    
    public function requestPlat()
    {
        return $this->hasOne(RequestPlat::class, 'quotation_id');
    }

    public function spkWarehouse()
    {
        return $this->hasOne(SPKWarehouse::class, 'quotation_id');
    }
    public function spkFinance()
    {
        return $this->hasOne(SPKFinance::class, 'quotation_id');
    }
}