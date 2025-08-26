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
    public function up()
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained()
            ->cascadeOnDelete()
            ->noActionOnUpdate();
            $table->string('kode_produk')->unique();
            $table->string('nama_produk');
            $table->unsignedInteger('harga_produk');
            $table->unsignedInteger('diskon')->default(0);
            $table->unsignedInteger('harga');
            $table->integer('stok')->default(0);
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('produks');
    }
};
