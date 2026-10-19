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
        Schema::create('spk_manufacture', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->cascadeOnDelete();
            $table->string('spk_number')->nullable()->unique();
            $table->boolean('warehouse')->default(false);
            $table->boolean('production')->default(false);
            $table->string('type')->default('manufacture');
            $table->tinyInteger('status')->default(0);
            $table->string('cabang');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spk_manufacture');
    }
};
