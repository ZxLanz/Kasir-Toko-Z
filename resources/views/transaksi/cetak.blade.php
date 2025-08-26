<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Faktur Pembayaran</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .invoice {
            width: 70mm;
        }

        table {
            width: 100%;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        hr {
            border-top: 1px solid #8c8b8b;
        }
        
        .discount-info {
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body onload="javascript:window.print()">
    <div class="invoice">
        <h3 class="center">{{ config('app.name') }}</h3>
        <p class="center">
            Jl. Raya Padaherang Km.1, Desa Padaherang <br>
            Kec.Padaherang - Kab.Pangandaran
       </p>
       <hr>
       <p>
         Kode Transaksi : {{ $penjualan->nomor_transaksi }} <br>
         Tanggal : {{ date('d/m/Y H:i:s', strtotime($penjualan->tanggal)) }} <br>
         Pelanggan : {{ $pelanggan->nama }} <br>
         Kasir : {{ $user->nama }}
      </p>
      <hr>

      <table>
        @php
            $totalDiskon = 0;
            $subtotalSebelumDiskon = 0;
        @endphp
        
        @foreach ($detilPenjualan as $row)
        <tr>
            <td>
                @php
                    // Hitung harga dan diskon
                    $hargaAsli = $row->harga_asli ?? $row->harga_produk;
                    $persenDiskon = $row->diskon ?? 0;
                    $hargaSetelahDiskon = $row->harga_produk;
                    
                    if ($persenDiskon > 0) {
                        $diskonPerItem = ($hargaAsli * $persenDiskon / 100);
                        $totalDiskonItem = $diskonPerItem * $row->jumlah;
                        $totalDiskon += $totalDiskonItem;
                    }
                    
                    $subtotalSebelumDiskon += ($hargaAsli * $row->jumlah);
                @endphp
                
                {{ $row->jumlah }} {{ $row->nama_produk }}
                
                @if($persenDiskon > 0)
    <br>Rp {{ number_format($hargaAsli, 0,',','.') }} x {{ $row->jumlah }} = Rp {{ number_format($hargaAsli * $row->jumlah, 0,',','.') }}
    <br><span class="discount-info">Diskon {{ number_format($persenDiskon, 0) }}% (-Rp {{ number_format($totalDiskonItem, 0,',','.') }})</span>
@else
    <br>Rp {{ number_format($hargaSetelahDiskon, 0,',','.') }} x {{ $row->jumlah }}
@endif
            </td>
            <td class="right">Rp {{ number_format($row->subtotal, 0,',','.') }}</td>
        </tr>
        @endforeach
    </table>

    <hr>

    <p class="right">
        @if($totalDiskon > 0)
        Sub Total : Rp {{ number_format($subtotalSebelumDiskon, 0, ',','.') }}<br>
        Total Diskon : -Rp {{ number_format($totalDiskon, 0, ',','.') }}<br>
        Setelah Diskon : Rp {{ number_format($subtotalSebelumDiskon - $totalDiskon, 0, ',','.') }}<br>
        @else
        Sub Total : Rp {{ number_format($penjualan->subtotal, 0, ',','.') }}<br>
        @endif
        
        Pajak 10% : Rp {{ number_format($penjualan->pajak, 0, ',','.') }}<br>
        <strong>Total : Rp {{ number_format($penjualan->total, 0, ',','.') }}</strong><br>
        Cash : Rp {{ number_format($penjualan->tunai, 0, ',','.') }}<br>
        Kembalian : Rp {{ number_format($penjualan->kembalian, 0, ',','.') }}<br>
    </p>

         <h3 class="center">Terima Kasih</h3>
    </div>
</body>

</html>