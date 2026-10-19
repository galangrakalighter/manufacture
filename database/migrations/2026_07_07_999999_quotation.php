<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique(); // Contoh: Q-20260707-001
            $table->string('nama_customer');
            $table->text('alamat_customer')->nullable();
            $table->text('penerima')->nullable();
            $table->text('alamat_penerima')->nullable();
            $table->foreignId('id_sales')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('tanggal_pesan');
            $table->string('tipe_pemesanan');
            $table->string('judul_cetak');
            $table->integer('perbox');
            $table->string('ukuran')->nullable();
            $table->string('perporasi')->nullable();
            $table->integer('jumlah_box');
            $table->integer('jumlah_ply');
            $table->text('keterangan')->nullable();
            $table->text('keterangan_reject')->nullable();
            $table->foreignId('id_barang')->nullable()->constrained('warehouse')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('total_amount', 15, 2);
            $table->text('terms_and_conditions')->nullable();
            $table->string('cabang');
            $table->timestamps();
        });

        // // Schema: Quotation_Items
        // Schema::create('quotation_items', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
        //     $table->string('nama_barang');
        //     $table->integer('quantity');
        //     $table->decimal('unit_price', 15, 2);
        //     $table->decimal('subtotal', 15, 2);
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('quotation_items');
    }
};
