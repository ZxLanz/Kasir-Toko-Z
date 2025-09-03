<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void 
    {
         \App\Models\User::create( [
        'nama'=> 'Administrator',
        'username'=> 'zilan',
        'role'=> 'admin',
        'password'=> bcrypt ('password'),
        ]);
         \App\Models\User::create([
             'nama' => 'Petugas',
             'username' => 'petugas',
             'role'=> 'petugas',
             'password'=> bcrypt ('password'),
         ]);
         \App\Models\Kategori::create ([
            'nama_kategori'=>'Makanan',
         ]);
         \App\Models\Kategori::create ([
            'nama_kategori'=>'Minuman',
         ]);
         \App\Models\Produk::create([
            'kategori_id'=> 1,
            'kode_produk'=>'1001',
            'nama_produk'=>'Snack Lays',
            'harga'=>5000,
            'harga_produk'=>5000
           ]);
           \App\Models\Produk::create([
           'kategori_id'=> 2,
            'kode_produk'=>'2001',
            'nama_produk'=>'Red Bull',
            'harga'=>20000,
            'harga_produk'=>20000
           ]);
         \App\Models\Stok::create([
            'produk_id' => 1,
            'nama_suplier' => 'PT Pepsico Indonesia Foods and Beverages',
            'jumlah' => 250,
            'tanggal' => date('Y-m-d', strtotime('-1 week'))
         ]);
         \App\Models\Stok::create([
            'produk_id' => 2,
            'nama_suplier' => 'Red Bull GmbH',
            'jumlah' => 1000,
            'tanggal' => date('Y-m-d', strtotime('-1 week'))
         ]);
         \App\Models\Produk::where('id',1)->update([
            'stok' =>1000,
         ]);
         \App\Models\Produk::where('id',2)->update([
            'stok'=>1000,
         ]);
    }
}
