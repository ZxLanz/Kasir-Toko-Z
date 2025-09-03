@extends('layouts.laporan', ['title' => 'Laporan Bulanan'])
@section('content')
    <h1 class="text-center">Laporan Bulanan</h1>

    <p>Bulan {{ $bulan }} {{ request()->tahun }}</p>
    
<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Transaksi Berhasil</th>
            <th>Transaksi Dibatalkan</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($penjualan as $key => $row)
        <tr>
            <td>{{ $key + 1}}</td>
            <td>{{ $row->tgl }}</td>
            <td>{{ $row->jumlah_transaksi_berhasil }}</td>
            <td>{{ $row->jumlah_transaksi_batal }}</td>
            <td>Rp {{ number_format($row->jumlah_total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2">Jumlah Total</th>
            <th>{{ $penjualan->sum('jumlah_transaksi_berhasil') }}</th>
            <th>{{ $penjualan->sum('jumlah_transaksi_batal') }}</th>
            <th>{{ $penjualan->sum('total_transaksi') }}</th>
        </tr>
        <tr>
            <th colspan="4">Total Pendapatan</th>
            <th>Rp {{ number_format($penjualan->sum('jumlah_total'), 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>
@endsection