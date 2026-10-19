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
        Schema::create('spk_warehouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('spk_number')->unique();
            $table->enum('type', [
                'warehouse',
                'production'
            ]);
            $table->tinyInteger('status')->default(0);
            $table->string('cabang');
            $table->text('catatan');
            $table->timestamps();
        });

        Schema::create('spk_production', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spk_warehouse_id')->constrained('spk_warehouse')->cascadeOnDelete();
            $table->foreignId('pic_id')->constrained('users')->cascadeOnDelete();
            $table->string('spk_number')->unique();
            $table->enum('type', [
                'warehouse',
                'production'
            ]);
            $table->tinyInteger('status')->default(0);
            $table->string('cabang');
            $table->text('catatan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('spk_warehouse');
         Schema::dropIfExists('spk_production');
    }
};
