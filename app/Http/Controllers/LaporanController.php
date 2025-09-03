<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use DB;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.form');
    }

    public function harian(Request $request)
    {
        // Ambil semua transaksi untuk display (termasuk yang batal)
        $penjualan = Penjualan::join('users', 'users.id', 'penjualans.user_id')
                ->join('pelanggans', 'pelanggans.id', 'penjualans.pelanggan_id')
                ->whereDate('tanggal', $request->tanggal)
                ->select('penjualans.*', 'pelanggans.nama as nama_pelanggan', 'users.nama as nama_kasir')
                ->orderBy('id')
                ->get();

        // Hitung total hanya dari transaksi yang tidak dibatalkan
        $totalValid = Penjualan::whereDate('tanggal', $request->tanggal)
                ->where('status', '!=', 'batal') // Exclude transaksi batal
                ->sum('total');

        return view('laporan.harian', [
            'penjualan' => $penjualan,
            'totalValid' => $totalValid
        ]);
    }

    public function bulanan(Request $request)
{
    // Hitung per tanggal dengan pemisahan transaksi berhasil dan dibatalkan
    $penjualan = Penjualan::select(
        DB::raw('COUNT(CASE WHEN status != "batal" THEN id END) as jumlah_transaksi_berhasil'),
        DB::raw('COUNT(CASE WHEN status = "batal" THEN id END) as jumlah_transaksi_batal'),
        DB::raw('COUNT(id) as total_transaksi'),
        DB::raw('SUM(CASE WHEN status != "batal" THEN total ELSE 0 END) as jumlah_total'),
        DB::raw("DATE_FORMAT(tanggal, '%d/%m/%Y') tgl")
    )
        ->whereMonth('tanggal', $request->bulan)
        ->whereYear('tanggal', $request->tahun)
        ->groupBy('tgl')
        ->having(DB::raw('COUNT(id)'), '>', 0) // Tampilkan semua hari yang ada transaksi
        ->orderBy(DB::raw('DATE(tanggal)'))
        ->get();

    $nama_bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei',
        'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $bulan = isset($nama_bulan[$request->bulan - 1]) ? $nama_bulan[$request->bulan - 1] : null;

    return view('laporan.bulanan', [
        'penjualan' => $penjualan,
        'bulan' => $bulan
    ]);
}
}