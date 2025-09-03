@extends('layouts.main', ['title' => 'Produk'])
@section('title-content')
    <i class="fas fa-box-open mr-2"></i>
    Produk
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-8 col-lg-12"> 
            <form method="POST" class="card card-orange card-outline"
                action="{{ route('produk.update', ['produk' => $produk->id]) }}">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Ubah Produk
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">ID: {{ $produk->id }}</span>
                    </div>
                </div>

                <div class="card-body">
                    @csrf
                    @method('PUT')
                    
                    <!-- Alert untuk error validation -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kode_produk">Kode Produk <span class="text-danger">*</span></label>
                                <x-input name="kode_produk" type="text" :value="$produk->kode_produk" 
                                         placeholder="Contoh: P001" required />
                                <small class="text-muted">Kode unik untuk produk</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kategori_id">Kategori <span class="text-danger">*</span></label>
                                <x-select name="kategori_id" :options="$kategoris" :value="$produk->kategori_id" required />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nama_produk">Nama Produk <span class="text-danger">*</span></label>
                        <x-input name="nama_produk" type="text" :value="$produk->nama_produk" 
                                 placeholder="Masukkan nama produk" required />
                    </div>

                    <!-- Section Harga dan Margin -->
                    <div class="card bg-light mt-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="fas fa-calculator mr-2"></i>Perhitungan Harga & Margin</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="harga_beli">Harga Beli (dari Supplier) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" name="harga_beli" id="harga_beli" 
                                                   class="form-control @error('harga_beli') is-invalid @enderror" 
                                                   placeholder="Contoh: 5.000" 
                                                   value="{{ old('harga_beli') }}"
                                                   required>
                                        </div>
                                        @error('harga_beli')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Harga pembelian dari supplier</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="harga_jual">Harga Jual (yang diinginkan) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" name="harga_jual" id="harga_jual" 
                                                   class="form-control @error('harga_jual') is-invalid @enderror" 
                                                   placeholder="Contoh: 7.000" 
                                                   value="{{ old('harga_jual', $produk->harga_produk) }}"
                                                   required>
                                        </div>
                                        @error('harga_jual')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Harga jual sebelum diskon</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="diskon">Diskon (%)</label>
                                        <div class="input-group">
                                            <input type="number" name="diskon" id="diskon"
                                                   class="form-control @error('diskon') is-invalid @enderror" 
                                                   placeholder="0" min="0" max="100" step="1"
                                                   value="{{ old('diskon', $produk->diskon) }}">
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        @error('diskon')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">0-100 untuk persentase diskon</small>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <!-- Preview Margin akan ditampilkan di sini -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Perhitungan -->
                    <div class="card bg-info mt-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0 text-white"><i class="fas fa-chart-line mr-2"></i>Analisis Keuntungan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-sm">
                                <div class="col-md-3">
                                    <strong class="text-white">Harga Beli:</strong><br>
                                    <span id="harga-beli-preview" class="text-white">Rp 0</span>
                                </div>
                                <div class="col-md-3">
                                    <strong class="text-white">Harga Jual:</strong><br>
                                    <span id="harga-jual-preview" class="text-white">Rp 0</span>
                                </div>
                                <div class="col-md-3">
                                    <strong class="text-white">Harga Final:</strong><br>
                                    <span id="harga-final-preview" class="text-white">Rp 0</span>
                                    <br><small id="diskon-preview" class="text-light">Diskon: 0%</small>
                                </div>
                                <div class="col-md-3">
                                    <strong class="text-white">Margin Keuntungan:</strong><br>
                                    <span id="margin-rupiah-preview" class="text-white">Rp 0</span>
                                    <br><span id="margin-persen-preview" class="badge badge-light">0%</span>
                                </div>
                            </div>
                            
                            <!-- Status Margin -->
                            <div class="mt-3">
                                <div id="margin-status" class="alert alert-light mb-0" style="display: none;">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <span id="margin-message"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Produk Saat Ini -->
                    <div class="form-group">
                        <div class="card bg-secondary">
                            <div class="card-body py-2">
                                <h6 class="text-white mb-2">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Informasi Produk Saat Ini
                                </h6>
                                <div class="row text-sm text-white">
                                    <div class="col-md-4">
                                        <strong>Harga Sekarang:</strong><br>
                                        Rp {{ number_format($produk->harga_produk ?? 0, 0, ',', '.') }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Diskon Sekarang:</strong><br>
                                        {{ $produk->diskon ?? 0 }}%
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Harga Jual Sekarang:</strong><br>
                                        Rp {{ number_format($produk->harga ?? ($produk->harga_produk ?? 0), 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-2"></i>
                                Update Produk
                            </button>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('produk.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Help Panel -->
        <div class="col-xl-4 col-lg-12">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Tips Perhitungan Margin
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="fas fa-shopping-cart mr-2"></i>Harga Beli</h6>
                        <p class="text-muted small">
                            Masukkan harga pembelian dari supplier. Ini adalah modal yang Anda keluarkan.
                        </p>
                    </div>
                    <div class="mb-3">
                        <h6><i class="fas fa-tag mr-2"></i>Harga Jual</h6>
                        <p class="text-muted small">
                            Tentukan harga jual yang diinginkan sebelum diskon. Sistem akan menghitung margin otomatis.
                        </p>
                    </div>
                    <div class="mb-3">
                        <h6><i class="fas fa-percentage mr-2"></i>Margin Keuntungan</h6>
                        <p class="text-muted small">
                            Dihitung otomatis berdasarkan selisih harga jual final dengan harga beli.
                        </p>
                        <ul class="small text-muted">
                            <li><span class="text-success">Hijau</span>: Margin > 20%</li>
                            <li><span class="text-warning">Kuning</span>: Margin 10-20%</li>
                            <li><span class="text-danger">Merah</span>: Margin < 10%</li>
                        </ul>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-calculator mr-1"></i>
                            <strong>Rumus:</strong><br>
                            Margin = (Harga Final - Harga Beli) / Harga Beli × 100%
                        </small>
                    </div>
                </div>
            </div>

           <!-- History Panel -->
            <div class="card card-warning card-outline mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i>
                        Riwayat Produk
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="fas fa-clock mr-2"></i>Terakhir Diubah</h6>
                        <p class="text-muted small">
                            @if($produk->updated_at && $produk->updated_at->gt($produk->created_at))
                                {{ $produk->updated_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                <br><small class="text-info">
                                    <i class="fas fa-edit mr-1"></i>Produk telah diperbarui
                                </small>
                            @elseif($produk->created_at)
                                {{ $produk->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                <br><small class="text-success">
                                    <i class="fas fa-plus-circle mr-1"></i>Produk baru dibuat
                                </small>
                            @else
                                <span class="text-muted">Tidak diketahui</span>
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6><i class="fas fa-info-circle mr-2"></i>Informasi Waktu</h6>
                        <div class="row text-sm">
                            <div class="col-6">
                                <small class="text-muted">Dibuat:</small><br>
                                <strong>
                                    @if($produk->created_at)
                                        {{ $produk->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                    @else
                                        -
                                    @endif
                                </strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Diperbarui:</small><br>
                                <strong>
                                    @if($produk->updated_at)
                                        {{ $produk->updated_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                    @else
                                        -
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6><i class="fas fa-chart-line mr-2"></i>Perbandingan Harga</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Harga Asli:</small><br>
                                <strong>Rp {{ number_format($produk->harga_produk ?? 0, 0, ',', '.') }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Harga Jual:</small><br>
                                <strong class="text-success">Rp {{ number_format($produk->harga ?? ($produk->harga_produk ?? 0), 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        @if(($produk->diskon ?? 0) > 0)
                            <div class="mt-2">
                                <span class="badge badge-success">
                                    Hemat Rp {{ number_format(($produk->harga_produk ?? 0) - ($produk->harga ?? ($produk->harga_produk ?? 0)), 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    @if($produk->updated_at && $produk->updated_at->gt($produk->created_at))
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Info:</strong> Produk telah diperbarui 
                                {{ $produk->updated_at->diffForHumans($produk->created_at) }} 
                                setelah dibuat.
                            </small>
                        </div>
                    @endif

                    <div class="alert alert-warning">
                        <small>
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Perhatian:</strong> Perubahan harga akan mempengaruhi transaksi baru.
                        </small>
                    </div>
                </div>
            </div>
    

@push('scripts')
<script>
function formatRupiah(input) {
    let start = input.selectionStart;
    let end = input.selectionEnd;
    
    let value = input.value.replace(/[^\d]/g, '');
    
    if (!value) {
        input.value = '';
        updatePreview();
        return;
    }
    
    let formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    let oldLength = input.value.length;
    input.value = formattedValue;
    let newLength = formattedValue.length;
    let lengthDiff = newLength - oldLength;
    
    let newStart = start + lengthDiff;
    let newEnd = end + lengthDiff;
    
    newStart = Math.max(0, Math.min(newStart, newLength));
    newEnd = Math.max(0, Math.min(newEnd, newLength));
    
    setTimeout(() => {
        input.setSelectionRange(newStart, newEnd);
        updatePreview();
    }, 0);
}

function formatInitialValue(input) {
    let value = input.value.replace(/[^\d]/g, '');
    if (value && value !== '0') {
        input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    updatePreview();
}

function updatePreview() {
    const hargaBeliInput = document.getElementById('harga_beli');
    const hargaJualInput = document.getElementById('harga_jual');
    const diskonInput = document.getElementById('diskon');
    
    const hargaBeli = parseInt(hargaBeliInput.value.replace(/\./g, '')) || 0;
    const hargaJual = parseInt(hargaJualInput.value.replace(/\./g, '')) || 0;
    const diskon = parseInt(diskonInput.value) || 0;
    
    const hargaFinal = hargaJual - (hargaJual * diskon / 100);
    const marginRupiah = hargaFinal - hargaBeli;
    const marginPersen = hargaBeli > 0 ? (marginRupiah / hargaBeli * 100) : 0;
    
    // Update preview
    document.getElementById('harga-beli-preview').textContent = 'Rp ' + hargaBeli.toLocaleString('id-ID');
    document.getElementById('harga-jual-preview').textContent = 'Rp ' + hargaJual.toLocaleString('id-ID');
    document.getElementById('harga-final-preview').textContent = 'Rp ' + hargaFinal.toLocaleString('id-ID');
    document.getElementById('diskon-preview').textContent = 'Diskon: ' + diskon + '%';
    document.getElementById('margin-rupiah-preview').textContent = 'Rp ' + marginRupiah.toLocaleString('id-ID');
    
    const marginBadge = document.getElementById('margin-persen-preview');
    marginBadge.textContent = marginPersen.toFixed(2) + '%';
    
    // Status margin dengan warna
    const marginStatus = document.getElementById('margin-status');
    const marginMessage = document.getElementById('margin-message');
    
    if (hargaBeli > 0 && hargaJual > 0) {
        marginStatus.style.display = 'block';
        
        if (marginPersen >= 20) {
            marginBadge.className = 'badge badge-success';
            marginStatus.className = 'alert alert-success mb-0';
            marginMessage.innerHTML = '<strong>Margin Baik!</strong> Keuntungan sangat menguntungkan.';
        } else if (marginPersen >= 10) {
            marginBadge.className = 'badge badge-warning';
            marginStatus.className = 'alert alert-warning mb-0';
            marginMessage.innerHTML = '<strong>Margin Cukup.</strong> Keuntungan masih dalam batas wajar.';
        } else if (marginPersen >= 0) {
            marginBadge.className = 'badge badge-danger';
            marginStatus.className = 'alert alert-danger mb-0';
            marginMessage.innerHTML = '<strong>Margin Rendah!</strong> Pertimbangkan untuk menaikkan harga jual.';
        } else {
            marginBadge.className = 'badge badge-dark';
            marginStatus.className = 'alert alert-danger mb-0';
            marginMessage.innerHTML = '<strong>Rugi!</strong> Harga jual lebih rendah dari harga beli.';
        }
    } else {
        marginStatus.style.display = 'none';
        marginBadge.className = 'badge badge-light';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const hargaBeliInput = document.getElementById('harga_beli');
    const hargaJualInput = document.getElementById('harga_jual');
    const diskonInput = document.getElementById('diskon');
    
    // Setup untuk kedua input harga
    [hargaBeliInput, hargaJualInput].forEach(input => {
        if (input) {
            if (input.value) {
                formatInitialValue(input);
            }
            
            input.addEventListener('input', function(e) {
                formatRupiah(this);
            });
            
            input.addEventListener('keydown', function(e) {
                if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                    (e.keyCode === 65 && e.ctrlKey === true) ||
                    (e.keyCode === 67 && e.ctrlKey === true) ||
                    (e.keyCode === 86 && e.ctrlKey === true) ||
                    (e.keyCode === 88 && e.ctrlKey === true) ||
                    (e.keyCode === 90 && e.ctrlKey === true) ||
                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                    return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
            
            input.addEventListener('paste', function(e) {
                setTimeout(() => {
                    formatRupiah(this);
                }, 10);
            });
        }
    });
    
    // Update preview saat diskon berubah
    if (diskonInput) {
        diskonInput.addEventListener('input', updatePreview);
        diskonInput.addEventListener('change', updatePreview);
    }
    
    // Handle form submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Handle harga_beli
            if (hargaBeliInput && hargaBeliInput.value) {
                let cleanValue = hargaBeliInput.value.replace(/\./g, '');
                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'harga_beli';
                hiddenInput.value = cleanValue || '0';
                hargaBeliInput.removeAttribute('name');
                form.appendChild(hiddenInput);
            }
            
            // Handle harga_jual
            if (hargaJualInput && hargaJualInput.value) {
                let cleanValue = hargaJualInput.value.replace(/\./g, '');
                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'harga_jual';
                hiddenInput.value = cleanValue || '0';
                hargaJualInput.removeAttribute('name');
                form.appendChild(hiddenInput);
            }
        });
    }
    
    // Initial preview update
    updatePreview();
});
</script>
@endpush
@endsection