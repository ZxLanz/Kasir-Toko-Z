@extends('layouts.main', ['title' => 'Produk'])

@section('title-content')
    <i class="fas fa-box-open mr-2"></i> Produk
@endsection

@section('content')
@if (session('success'))
    <x-alert type="success">
        <strong>Berhasil!</strong> {{ session('success') }}
    </x-alert>
@endif
@if (session('update') == 'success')
    <x-alert type="success">
        <strong>Berhasil diupdate!</strong> Produk berhasil diupdate.
    </x-alert>
@endif
@if (session('destroy') == 'success')
    <x-alert type="success">
        <strong>Berhasil dihapus!</strong> Produk berhasil dihapus.
    </x-alert>
@endif

<div class="card card-orange card-outline">
    <div class="card-header form-inline">
        <a href="{{ route('produk.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Tambah
        </a>
        <form action="?" method="get" class="ml-auto">
            <div class="input-group">
                <input type="text" class="form-control" name="search" value="{{ request()->search }}"
                    placeholder="Kode, Nama Produk">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga Produk</th>
                        <th>Diskon (%)</th>
                        <th>Harga Jual</th> 
                        <th>Stok</th>
                        <th width="80"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produks as $key => $produk)
                        <tr>
                            <td>{{ $produks->firstItem() + $key }}</td>
                            <td><span class="badge badge-secondary">{{ $produk->kode_produk }}</span></td>
                            <td>{{ $produk->nama_produk }}</td>
                            <td><span class="badge badge-info">{{ $produk->nama_kategori }}</span></td>
                            <td>
                                <strong>Rp {{ number_format($produk->harga_produk, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @if($produk->diskon > 0)
                                    <span class="badge badge-warning">{{ $produk->diskon }}%</span>
                                @else
                                    <span class="badge badge-light">0%</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">Rp {{ number_format($produk->harga, 0, ',', '.') }}</strong>
                                @if($produk->diskon > 0)
                                    <br><small class="text-muted">
                                        <s>Rp {{ number_format($produk->harga_produk, 0, ',', '.') }}</s>
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($produk->stok > 10)
                                    <span class="badge badge-success">{{ $produk->stok }}</span>
                                @elseif($produk->stok > 0)
                                    <span class="badge badge-warning">{{ $produk->stok }}</span>
                                @else
                                    <span class="badge badge-danger">{{ $produk->stok }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('produk.edit', ['produk' => $produk->id]) }}" 
                                        class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" data-toggle="modal" data-target="#modalDelete"
                                        data-url="{{ route('produk.destroy', ['produk' => $produk->id]) }}"
                                        class="btn btn-sm btn-outline-danger btn-delete" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-box-open fa-2x mb-2"></i>
                                    <p>Tidak ada produk ditemukan</p>
                                    @if(request()->search)
                                        <a href="{{ route('produk.index') }}" class="btn btn-sm btn-primary">
                                            Tampilkan Semua Produk
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($produks->hasPages())
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        Menampilkan {{ $produks->firstItem() }} - {{ $produks->lastItem() }} 
                        dari {{ $produks->total() }} produk
                    </small>
                </div>
                <div class="col-md-6">
                    {{ $produks->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('modals')
    <x-modal-delete />
@endpush

@push('styles')
<style>
.table th {
    background-color: #f8f9fa;
    border-top: none;
}
.badge {
    font-size: 0.75em;
}
.btn-group .btn {
    margin: 0;
}
</style>
@endpush