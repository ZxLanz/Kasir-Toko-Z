<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('detil_penjualans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained()
                ->cascadeOnDelete()
                ->noActionOnUpdate();
            $table->foreignId('produk_id')->constrained()
                ->cascadeOnDelete()
                ->noActionOnUpdate();
            $table->unsignedInteger('jumlah');
            $table->unsignedInteger('harga_produk');
            $table->decimal('harga_asli', 15, 2)->nullable(); // TAMBAHAN: Harga sebelum diskon
            $table->decimal('diskon', 5, 2)->default(0);      // TAMBAHAN: Persentase diskon
            $table->unsignedInteger('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detil_penjualans');
    }
};