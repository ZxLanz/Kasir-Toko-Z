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
    
        $cart = Cart::name($request->user()->id);
    
        $cart->addItem([ 
            'id' => $produk->id, 
            'title' => $produk->nama_produk, 
            'quantity' => 1, 
            'price' => $produk->harga,
            'options'=>[
                'diskon'=>$produk->diskon,
                'harga_produk'=>$produk->harga_produk,
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

        // Cek apakah request untuk set quantity langsung
        if ($request->has('set_quantity')) {
            $request->validate([
                'set_quantity' => ['required', 'integer', 'min:1', 'max:999']
            ]);

            $newQuantity = $request->set_quantity;

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
                ], 400);
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