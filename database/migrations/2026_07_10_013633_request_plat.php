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
        Schema::create('request_plat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade'); // Contoh: Q-20260707-001
            $table->foreignId('request_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('approve_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->tinyInteger('status');
            $table->string('lokasi_plat')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_plat');
    }
};