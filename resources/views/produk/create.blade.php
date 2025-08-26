@extends('layouts.main', ['title' => 'Produk'])
@section('title-content')
    <i class="fas fa-box-open mr-2"></i>
    Produk
@endsection
 
@section('content')
    <div class="row">
        <div class="col-xl-6 col-lg-8">
            <form method="POST" action="{{ route('produk.store') }}" class="card card-orange card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus mr-2"></i>
                        Buat Produk Baru
                    </h3>
                </div>
                <div class="card-body">
                    @csrf
                    
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
                                <x-input name="kode_produk" type="text" placeholder="Contoh: P001" required />
                                <small class="text-muted">Kode unik untuk produk</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kategori_id">Kategori <span class="text-danger">*</span></label>
                                <x-select name="kategori_id" :options="$kategoris" required />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nama_produk">Nama Produk <span class="text-danger">*</span></label>
                        <x-input name="nama_produk" type="text" placeholder="Masukkan nama produk" required />
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="harga">Harga Produk <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="text" name="harga" id="harga" 
                                           class="form-control @error('harga') is-invalid @enderror" 
                                           placeholder="Contoh: 5.000" 
                                           value="{{ old('harga') }}"
                                           required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">.00</span>
                                    </div>
                                </div>
                                @error('harga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Format otomatis akan ditambahkan (titik pemisah ribuan)
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="diskon">Diskon (%)</label>
                                <div class="input-group">
                                    <input type="number" name="diskon" id="diskon"
                                           class="form-control @error('diskon') is-invalid @enderror" 
                                           placeholder="0" min="0" max="100" step="1"
                                           value="{{ old('diskon', 0) }}">
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
                    </div>

                    <!-- Preview Harga -->
                    <div class="form-group">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="row text-sm">
                                    <div class="col-4">
                                        <strong>Harga Asli:</strong><br>
                                        <span id="harga-preview">Rp 0</span>
                                    </div>
                                    <div class="col-4">
                                        <strong>Diskon:</strong><br>
                                        <span id="diskon-preview">0%</span>
                                    </div>
                                    <div class="col-4">
                                        <strong>Harga Jual:</strong><br>
                                        <span id="harga-jual-preview" class="text-success font-weight-bold">Rp 0</span>
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
                                Simpan Produk
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
        <div class="col-xl-6 col-lg-4">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle mr-2"></i>
                        Panduan Pengisian
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="fas fa-barcode mr-2"></i>Kode Produk</h6>
                        <p class="text-muted small">
                            Masukkan kode unik untuk produk. Contoh: P001, PROD001, SKU001
                        </p>
                    </div>
                    <div class="mb-3">
                        <h6><i class="fas fa-money-bill mr-2"></i>Harga Produk</h6>
                        <p class="text-muted small">
                            Masukkan harga dalam format angka. Sistem akan otomatis menambahkan 
                            format rupiah dengan pemisah titik. Contoh: 5000 → 5.000
                        </p>
                    </div>
                    <div class="mb-3">
                        <h6><i class="fas fa-percentage mr-2"></i>Diskon</h6>
                        <p class="text-muted small">
                            Masukkan persentase diskon (0-100). Harga jual akan dihitung otomatis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div> 

@push('scripts')
<script>
function formatRupiah(input) {
    // Simpan posisi cursor
    let start = input.selectionStart;
    let end = input.selectionEnd;
    
    // Ambil nilai dan hapus semua karakter non-digit
    let value = input.value.replace(/[^\d]/g, '');
    
    // Jika kosong, biarkan kosong
    if (!value) {
        input.value = '';
        updatePreview();
        return;
    }
    
    // Format dengan titik sebagai pemisah ribuan
    let formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    // Hitung perubahan panjang untuk adjust cursor
    let oldLength = input.value.length;
    input.value = formattedValue;
    let newLength = formattedValue.length;
    let lengthDiff = newLength - oldLength;
    
    // Set posisi cursor yang baru
    let newStart = start + lengthDiff;
    let newEnd = end + lengthDiff;
    
    // Pastikan cursor tidak keluar batas
    newStart = Math.max(0, Math.min(newStart, newLength));
    newEnd = Math.max(0, Math.min(newEnd, newLength));
    
    // Set selection range
    setTimeout(() => {
        input.setSelectionRange(newStart, newEnd);
        updatePreview();
    }, 0);
}

function formatInitialValue(input) {
    // Khusus untuk format nilai awal dari database
    let value = input.value.replace(/[^\d]/g, '');
    if (value && value !== '0') {
        input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    updatePreview();
}

function updatePreview() {
    const hargaInput = document.getElementById('harga');
    const diskonInput = document.getElementById('diskon');
    
    const harga = parseInt(hargaInput.value.replace(/\./g, '')) || 0;
    const diskon = parseInt(diskonInput.value) || 0;
    
    const hargaJual = harga - (harga * diskon / 100);
    
    document.getElementById('harga-preview').textContent = 'Rp ' + harga.toLocaleString('id-ID');
    document.getElementById('diskon-preview').textContent = diskon + '%';
    document.getElementById('harga-jual-preview').textContent = 'Rp ' + hargaJual.toLocaleString('id-ID');
}

document.addEventListener('DOMContentLoaded', function() {
    const hargaInput = document.getElementById('harga');
    const diskonInput = document.getElementById('diskon');
    
    if (hargaInput) {
        // Format nilai awal jika ada (dari old value)
        if (hargaInput.value) {
            formatInitialValue(hargaInput);
        }
        
        // Event listener untuk input
        hargaInput.addEventListener('input', function(e) {
            formatRupiah(this);
        });
        
        // Event listener untuk keydown (handle backspace, delete, etc)
        hargaInput.addEventListener('keydown', function(e) {
            // Allow: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                (e.keyCode === 90 && e.ctrlKey === true) ||
                // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
        
        // Event paste handling
        hargaInput.addEventListener('paste', function(e) {
            setTimeout(() => {
                formatRupiah(this);
            }, 10);
        });
        
        // Event focus
        hargaInput.addEventListener('focus', function(e) {
            if (this.value === '') {
                this.placeholder = 'Contoh: 5000';
            }
        });
    }
    
    // Update preview saat diskon berubah
    if (diskonInput) {
        diskonInput.addEventListener('input', updatePreview);
        diskonInput.addEventListener('change', updatePreview);
    }
    
    // Handle form submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const hargaInput = document.getElementById('harga');
            if (hargaInput && hargaInput.value) {
                // Buat hidden input dengan nilai bersih (tanpa titik)
                let cleanValue = hargaInput.value.replace(/\./g, '');
                
                // Pastikan ada nilai numerik
                if (cleanValue && cleanValue !== '0') {
                    let hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'harga';
                    hiddenInput.value = cleanValue;
                    
                    // Hapus name dari input display agar tidak conflict
                    hargaInput.removeAttribute('name');
                    
                    // Tambahkan hidden input ke form
                    form.appendChild(hiddenInput);
                } else {
                    // Jika kosong, set nilai 0
                    let hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'harga';
                    hiddenInput.value = '0';
                    
                    hargaInput.removeAttribute('name');
                    form.appendChild(hiddenInput);
                }
            }
        });
    }
    
    // Initial preview update
    updatePreview();
});
</script>
@endpush
@endsection