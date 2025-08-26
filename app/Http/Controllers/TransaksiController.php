<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetilPenjualan;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\User;
use Cart;

class TransaksiController extends Controller
{
    public function index (Request $request)
    {
        $search = $request->search;

        $penjualans = Penjualan::join('users', 'users.id', 'penjualans.user_id')
            ->leftJoin('pelanggans', 'pelanggans.id', 'penjualans.pelanggan_id') // Ubah ke leftJoin
            ->select(
                'penjualans.*', 
                'users.nama as nama_kasir', 
                \DB::raw('COALESCE(pelanggans.nama, "- Umum -") as nama_pelanggan') // Handle null pelanggan
            )
            ->orderBy('id', 'desc')
            ->when($search, function ($q, $search) {
                return $q->where('nomor_transaksi', 'like', "%{$search}%");
            })
            ->paginate();

        if ($search) $penjualans->appends (['search' => $search]);
        return view('transaksi.index', [ 
            'penjualans' => $penjualans
        ]); 
    }

    public function create (Request $request)
    {
        return view('transaksi.create', [ 
            'nama_kasir' => $request->user()->nama, 
            'tanggal' => date('d F Y') 
        ]);
    }

    public function store (Request $request)
{
    $request->validate([
        'pelanggan_id' => ['nullable', 'exists:pelanggans,id'], 
        'cash' => ['required', 'numeric', 'gte:total_bayar']
    ], [], [ 
        'pelanggan_id' => 'pelanggan'
    ]);

    $user = $request->user();
    $lastPenjualan = Penjualan::orderBy('id', 'desc')->first();

    $cart = Cart::name($user->id);
    $cartDetails = $cart->getDetails();

    $total = $cartDetails->get('total');
    $kembalian = $request->cash - $total;

    $no = $lastPenjualan ? $lastPenjualan->id + 1 : 1;
    $no = sprintf("%04d", $no);

    // Hitung total diskon dari semua item
    $allItems = $cartDetails->get('items');
    $totalDiskon = 0;
    $subtotalSebelumDiskon = 0;

    foreach ($allItems as $key => $value) {
        $item = $allItems->get($key);
        
        // Ambil data diskon dari options
        $hargaAsli = $item->options->harga_produk ?? $item->price;
        $diskon = $item->options->diskon ?? 0;
        
        $subtotalSebelumDiskon += ($hargaAsli * $item->quantity);
        
        if ($diskon > 0) {
            $diskonItem = ($hargaAsli * $diskon / 100) * $item->quantity;
            $totalDiskon += $diskonItem;
        }
    }

    // SOLUSI: Cari atau buat pelanggan "Umum" default
    $pelangganId = null;
    
    // Cek dari request
    if ($request->filled('pelanggan_id')) {
        $pelangganId = $request->pelanggan_id;
    } else {
        // Cek dari cart
        $extraInfo = $cart->getExtraInfo();
        if (is_array($extraInfo) && isset($extraInfo['pelanggan']['id'])) {
            $pelangganId = $extraInfo['pelanggan']['id'];
        }
    }
    
    // Jika masih null, buat/cari pelanggan "Umum"
    if ($pelangganId === null) {
        $pelangganUmum = Pelanggan::firstOrCreate(
            ['nama' => '- Umum -'],
            [
                'nama' => '- Umum -',
                'alamat' => 'Alamat Umum',
                'nomor_tlp' => null
            ]
        );
        $pelangganId = $pelangganUmum->id;
    }

    $penjualan = Penjualan::create([
        'user_id' => $user->id, 
        'pelanggan_id' => $pelangganId, // Selalu ada ID, tidak pernah null
        'nomor_transaksi' => date('Ymd'). $no, 
        'tanggal' => date('Y-m-d H:i:s'), 
        'total' => $total, 
        'tunai' => $request->cash, 
        'kembalian' => $kembalian,
        'pajak' => $cartDetails->get('tax_amount'),
        'subtotal' => $cartDetails->get('subtotal'),
        'total_diskon' => $totalDiskon
    ]);

    foreach ($allItems as $key => $value) { 
        $item = $allItems->get($key);
        
        // Ambil data dari options cart
        $hargaAsli = $item->options->harga_produk ?? $item->price;
        $diskon = $item->options->diskon ?? 0;

        DetilPenjualan::create([
            'penjualan_id' => $penjualan->id,
            'produk_id' => $item->id, 
            'jumlah' => $item->quantity,
            'harga_produk' => $item->price, // Harga setelah diskon
            'harga_asli' => $hargaAsli, // Harga sebelum diskon
            'diskon' => $diskon, // Persentase diskon
            'subtotal' => $item->subtotal,
        ]);

        $produk = Produk::find($item->id);
        if ($produk) {
            $produk->stok -= $item->quantity;
            $produk->save();
        }
    }
    
    $cart->destroy();

    return redirect()->route('transaksi.show', ['transaksi' => $penjualan->id]);
}
    public function show (Request $request, Penjualan $transaksi)
    {
        // Handle pelanggan null dengan data default
        $pelanggan = $transaksi->pelanggan_id ? 
            Pelanggan::find($transaksi->pelanggan_id) : 
            (object)[
                'id' => null,
                'nama' => '- Umum -',
                'nomor_tlp' => '-',
                'alamat' => '-'
            ];
            
        $user = User::find($transaksi->user_id);
        $detilPenjualan = DetilPenjualan::join('produks', 'produks.id', 'detil_penjualans.produk_id')
            ->select('detil_penjualans.*', 'nama_produk')
            ->where('penjualan_id', $transaksi->id)->get();

        return view('transaksi.invoice', [
            'penjualan' => $transaksi,
            'pelanggan' => $pelanggan, 
            'user' => $user, 
            'detilPenjualan' => $detilPenjualan
        ]);
    }
        
    public function destroy (Request $request, Penjualan $transaksi)
    {
        $transaksi->update([
            'status'=>'batal'
        ]);

        return back()->with('destroy', 'success');
    }
    
    public function produk (Request $request)
    {
        $search= $request->search;
        $produks = Produk::select('id', 'kode_produk', 'nama_produk')
            ->when ($search, function ($q, $search) {
                return $q->where('nama_produk', 'like', "%{$search}%");
        }) 
        ->orderBy('nama_produk')
        ->take(15)
        ->get();

        return response()->json($produks);
    }

    public function pelanggan (Request $request)
    {
        $search = $request->search;
        $pelanggans = Pelanggan::select('id', 'nama', 'nomor_tlp', 'alamat') // Tambahkan data lain untuk tampilan
            ->when($search, function ($q, $search) {
                return $q->where('nama', 'like', "%{$search}%")
                       ->orWhere('nomor_tlp', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->take(15)
            ->get();

        return response()->json ($pelanggans);
    }

    public function addPelanggan (Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:pelanggans']
        ]);
        
        $pelanggan = Pelanggan::find($request->id);
        $cart = Cart::name($request->user()->id);

        $cart->setExtraInfo([
            'pelanggan' => [
                'id' => $pelanggan->id,
                'nama' => $pelanggan->nama,
            ]
        ]);
        
        return response()->json(['message' => 'Berhasil memilih pelanggan.']);
    }

    // Method baru untuk reset pelanggan ke "Umum"
    public function resetPelanggan(Request $request)
    {
        $cart = Cart::name($request->user()->id);
        
        // Hapus info pelanggan dari cart
        $extraInfo = $cart->getExtraInfo();
        if (is_array($extraInfo)) {
            unset($extraInfo['pelanggan']);
            $cart->setExtraInfo($extraInfo);
        }
        
        return response()->json(['message' => 'Pelanggan direset ke umum.']);
    }
    
    public function cetak (Penjualan $transaksi)
    {
        // Handle pelanggan null untuk cetak
        $pelanggan = $transaksi->pelanggan_id ? 
            Pelanggan::find($transaksi->pelanggan_id) : 
            (object)[
                'id' => null,
                'nama' => '- Umum -',
                'nomor_tlp' => '-',
                'alamat' => '-'
            ];
            
        $user = User::find($transaksi->user_id);
        $detilPenjualan = DetilPenjualan::join('produks', 'produks.id', 'detil_penjualans.produk_id')
            ->select('detil_penjualans.*', 'nama_produk')
            ->where('penjualan_id', $transaksi->id)->get();

        return view('transaksi.cetak', [
            'penjualan' => $transaksi, 
            'pelanggan' => $pelanggan, 
            'user' => $user, 
            'detilPenjualan' => $detilPenjualan
        ]);
    }
}