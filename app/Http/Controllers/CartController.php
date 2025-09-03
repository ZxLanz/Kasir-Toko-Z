<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan; 
use App\Models\Produk;
use Cart;

class CartController extends Controller
{
    public function index(Request $request) 
    {
        $cart = Cart::name($request->user()->id);
    
        $cart->applyTax([ 
            'id' => 1, 
            'rate' => 10, 
            'title' => 'Pajak PPN 10%'
        ]);
    
        return $cart->getDetails()->toJson();
    }
    
    public function store(Request $request)
    {
        $request->validate([ 
            'kode_produk' => ['required', 'exists:produks'] 
        ]);
    
        $produk = Produk::where('kode_produk', $request->kode_produk)->first();

        // VALIDASI STOK: Cek apakah stok tersedia
        if ($produk->stok <= 0) {
            return response()->json([
                'error' => 'Stok produk ' . $produk->nama_produk . ' sudah habis!'
            ], 422);
        }

        $cart = Cart::name($request->user()->id);

        // Cek apakah produk sudah ada di cart
        $existingItem = null;
        $cartDetails = $cart->getDetails();
        $items = $cartDetails->get('items');
        
        if ($items) {
            foreach ($items as $key => $item) {
                if ($item->id == $produk->id) {
                    $existingItem = $item;
                    break;
                }
            }
        }

        // Jika produk sudah ada di cart, cek total quantity
        if ($existingItem) {
            $newQuantity = $existingItem->getQuantity() + 1;
            if ($newQuantity > $produk->stok) {
                return response()->json([
                    'error' => 'Stok produk ' . $produk->nama_produk . ' tidak mencukupi! Stok tersedia: ' . $produk->stok
                ], 422);
            }
        }
    
        $cart->addItem([ 
            'id' => $produk->id, 
            'title' => $produk->nama_produk, 
            'quantity' => 1, 
            'price' => $produk->harga,
            'options'=>[
                'diskon'=>$produk->diskon,
                'harga_produk'=>$produk->harga_produk,
                'stok'=>$produk->stok, // Tambahkan info stok
            ]
        ]);

        return response()->json(['message' => 'Berhasil ditambahkan.']);
    }

    public function update(Request $request, $hash)
    {
        $cart = Cart::name($request->user()->id); 
        $item = $cart->getItem($hash);

        if (!$item) { 
            return abort(404); 
        }

        // Ambil data produk untuk validasi stok
        $produk = Produk::find($item->getId());
        if (!$produk) {
            return response()->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        // Cek apakah request untuk set quantity langsung
        if ($request->has('set_quantity')) {
            $request->validate([
                'set_quantity' => ['required', 'integer', 'min:1', 'max:999']
            ]);

            $newQuantity = $request->set_quantity;

            // VALIDASI STOK: Cek apakah quantity tidak melebihi stok
            if ($newQuantity > $produk->stok) {
                return response()->json([
                    'error' => 'Quantity tidak boleh melebihi stok yang tersedia! Stok tersedia: ' . $produk->stok
                ], 422);
            }

            // Cek apakah stok masih tersedia
            if ($produk->stok <= 0) {
                return response()->json([
                    'error' => 'Stok produk ' . $produk->nama_produk . ' sudah habis!'
                ], 422);
            }

            $cart->updateItem($item->getHash(), [
                'quantity' => $newQuantity
            ]);

            return response()->json([
                'message' => 'Quantity berhasil diupdate.',
                'new_quantity' => $newQuantity
            ]);
        } 
        // Method lama untuk increment/decrement
        else {
            $request->validate([
                'qty' => ['required', 'in:-1,1']
            ]);

            $newQuantity = $item->getQuantity() + $request->qty;

            // Pastikan quantity tidak kurang dari 1
            if ($newQuantity < 1) {
                return response()->json([
                    'error' => 'Quantity tidak boleh kurang dari 1'
                ], 422);
            }

            // VALIDASI STOK: Cek apakah quantity tidak melebihi stok
            if ($newQuantity > $produk->stok) {
                return response()->json([
                    'error' => 'Quantity tidak boleh melebihi stok yang tersedia! Stok tersedia: ' . $produk->stok
                ], 422);
            }

            // Cek apakah stok masih tersedia
            if ($produk->stok <= 0) {
                return response()->json([
                    'error' => 'Stok produk ' . $produk->nama_produk . ' sudah habis!'
                ], 422);
            }

            $cart->updateItem($item->getHash(), [
                'quantity' => $newQuantity
            ]);

            return response()->json([
                'message' => 'Berhasil diupdate.',
                'new_quantity' => $newQuantity
            ]);
        }
    }

    public function destroy(Request $request, $hash)
    {
        $cart = Cart::name($request->user()->id); 
        $cart->removeItem($hash); 
        return response()->json(['message' => 'Berhasil dihapus.']);
    }

    public function clear(Request $request)
    {
        $cart = Cart::name($request->user()->id); 
        $cart->destroy();

        return back();
    }
}