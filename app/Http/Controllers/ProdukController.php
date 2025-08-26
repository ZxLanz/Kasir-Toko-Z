<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $produks = Produk::join('kategoris', 'kategoris.id', 'produks.kategori_id')
            ->orderBy('produks.id')
            ->select('produks.*', 'nama_kategori')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('kode_produk', 'like', "%{$search}%")
                          ->orWhere('nama_produk', 'like', "%{$search}%");
                });
            })
            ->paginate();

        if ($search) {
            $produks->appends(['search' => $search]);
        }

        return view('produk.index', compact('produks'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori')->pluck('nama_kategori', 'id')->toArray();
        
        return view('produk.create', compact('kategoris'));
    }

   public function store(Request $request)
{
    $request->validate([
        'diskon'=>['required','between:0,100'],
        'kode_produk' => ['required', 'max:250', 'unique:produks'],
        'nama_produk' => ['required', 'max:150'],
        'harga' => ['required', 'min:0'],
        'kategori_id' => ['required', 'exists:kategoris,id'],
    ]);

    // Bersihkan titik dari harga input terlebih dahulu
    $harga_bersih = (float) str_replace('.', '', $request->harga);
    
    // Hitung harga setelah diskon
    $harga_jual = $harga_bersih - ($harga_bersih * $request->diskon / 100);

    $request->merge([
        'harga_produk' => $harga_bersih,     // Harga asli (tanpa titik)
        'harga' => $harga_jual,              // Harga jual (tanpa titik)
    ]);

    Produk::create($request->all());

    return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan!');
}

    public function show(Produk $produk)
    {
        abort(404);
    }

    public function edit(Produk $produk)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->pluck('nama_kategori', 'id')->toArray();

        return view('produk.edit', compact('produk', 'kategoris'));
    }

   public function update(Request $request, Produk $produk)
{
    $request->validate([
        'diskon'=>['required','between:0,100'],
        'kode_produk' => ['required', 'max:250', 'unique:produks,kode_produk,' . $produk->id],
        'nama_produk' => ['required', 'max:150'],
        'harga' => ['required', 'min:0'],
    ]);

    // Bersihkan titik dari harga input terlebih dahulu
    $harga_bersih = (float) str_replace('.', '', $request->harga);
    
    // Hitung harga setelah diskon
    $harga_jual = $harga_bersih - ($harga_bersih * $request->diskon / 100);

    $request->merge([
        'harga_produk' => $harga_bersih,     // Harga asli (tanpa titik)
        'harga' => $harga_jual,              // Harga jual (tanpa titik)
    ]);

    $produk->update($request->all());

    return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui!');
}

    public function destroy(Produk $produk)
    {
        $produk->delete();

        return back()->with('success', 'Produk berhasil dihapus!');
    }
}
