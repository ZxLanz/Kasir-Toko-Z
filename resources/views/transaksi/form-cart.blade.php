<div class="card card-orange card-outline">
    <div class="card-body">
        <h3 class="m-0 text-right">Rp <span id="totalJumlah">0</span></h3>
    </div>
</div>

<form action="{{ route('transaksi.store') }}" method="POST" class="card card-orange card-outline">
    @csrf
    <div class="card-body">
        <p class="text-right">
            Tanggal: {{ $tanggal }}
        </p>
        <div class="row">
            <div class="col">
                <label>Nama Pelanggan</label>
                <input type="text" id="namaPelanggan" class="form-control @error('pelanggan_id') is-invalid @enderror"
                    disabled>
                @error('pelanggan_id')
                <div class="invalid-feedback">
                    {{ $message}}
                </div>
                @enderror

                <input type="hidden" name="pelanggan_id" id="pelangganId">
            </div>
            <div class="col">
                <label>Nama Kasir</label>
                <input type="text" class="form-control" value="{{ $nama_kasir }}" disabled>
            </div>
        </div>

        <table class="table table-striped table-hover table-bordered mt-3">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th width="150px">Qty</th>
                    <th>Harga</th>
                    <th>Sub Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="resultCart">
                <tr>
                    <td colspan="5" class="text-center"> Tidak ada data.</td>
                </tr>
            </tbody>
        </table>

        <div class="row mt-3">
            <div class="col-2 offset-6">
                <p>Total</p>
                <p>Diskon</p>
                <p>Pajak 10%</p>
                <p><strong>Total Bayar</strong></p>
            </div>
            <div class="col-4 text-right">
                <p>Rp <span id="subtotal">0</span></p>
                <p>Rp <span id="totalDiskon">0</span></p>
                <p>Rp <span id="taxAmount">0</span></p>
                <p><strong>Rp <span id="total">0</span></strong></p>
            </div>
        </div>

        <div class="col-6 offset-6">
            <hr class="mt-0">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">Cash</span>
                </div>
                <input type="text" name="cash" id="cashInput" class="form-control @error('cash') is-invalid @enderror"
                    placeholder="Jumlah Cash" value="{{ old('cash') }}">
            </div>
            <input type="hidden" name="total_bayar" id="totalBayar" />
            <input type="hidden" name="total_diskon" id="totalDiskonHidden" />
            @error('cash')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class="col-12 form-inline mt-3">
            <a href="{{ route('transaksi.index') }}" class="btn btn-secondary mr-2">Ke Transaksi</a>
            <button type="button" class="btn btn-info mr-2" onclick="resetKeUmum()">
                <i class="fas fa-user-times mr-1"></i> Reset ke pelanggan Umum
            </button>
            <a href="{{ route('cart.clear') }}" class="btn btn-danger">Kosongkan</a>
            <button type="submit" class="btn btn-success ml-auto">
                <i class="fas fa-money-bill-wave mr-2"></i> Bayar Transaksi
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
$(function() {
    fetchCart();
    
    // Setup event listeners setelah DOM ready
    setupEventListeners();
    
    // Setup cash input formatting
    setupCashInput();
});

function setupCashInput() {
    const cashInput = $('#cashInput');
    
    // Format input saat mengetik
    cashInput.on('input', function() {
        let value = $(this).val();
        
        // Hapus semua karakter non-digit
        value = value.replace(/[^\d]/g, '');
        
        // Format dengan titik sebagai pemisah ribuan
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
        }
        
        $(this).val(value);
    });
    
    // Hapus format saat form disubmit untuk mengirim nilai numerik
    $('form').on('submit', function() {
        let cashValue = cashInput.val();
        // Konversi kembali ke angka dengan menghapus titik
        cashValue = cashValue.replace(/\./g, '');
        cashInput.val(cashValue);
    });
    
    // Format ulang jika user meninggalkan input
    cashInput.on('blur', function() {
        let value = $(this).val();
        if (value) {
            // Hapus format lama dan buat format baru
            value = value.replace(/\./g, '');
            if (value && !isNaN(value)) {
                $(this).val(parseInt(value).toLocaleString('id-ID'));
            }
        }
    });
}

function fetchCart() {
    $.getJSON("/cart",
        function(response) {
            $('#resultCart').empty();

            const {
                items,
                subtotal,
                tax_amount,
                total,
                extra_info,
                total_diskon = 0 // Default 0 jika tidak ada
            } = response;

            // Hitung total diskon dari semua items
            let calculatedDiskon = 0;
            for (const property in items) {
                const item = items[property];
                if (item.options && item.options.diskon) {
                    const hargaAsli = item.options.harga_produk;
                    const diskonPersen = item.options.diskon;
                    const diskonPerItem = (hargaAsli * diskonPersen / 100) * item.quantity;
                    calculatedDiskon += diskonPerItem;
                }
            }

            // Update display dengan format Rp
            $('#subtotal').html(rupiah(subtotal + calculatedDiskon)); // Subtotal sebelum diskon
            $('#totalDiskon').html(calculatedDiskon > 0 ? '-' + rupiah(calculatedDiskon) : '0');
            $('#taxAmount').html(rupiah(tax_amount));
            $('#total, #totalJumlah').html(rupiah(total));
            $('#totalBayar').val(total);
            $('#totalDiskonHidden').val(calculatedDiskon);

            for (const property in items) {
                addRow(items[property])
            }
            if (Array.isArray(items)) {
                $('#resultCart').html(`<tr><td colspan="5" class="text-center">Tidak ada data. </td></tr>`);
            }

            // PERBAIKAN: Handle pelanggan dengan lebih baik
            if (!Array.isArray(extra_info) && extra_info && extra_info.pelanggan) {
                const { id, nama } = extra_info.pelanggan;
                $('#namaPelanggan').val(nama);
                $('#pelangganId').val(id);
            } else {
                // Jika tidak ada pelanggan, set ke umum
                $('#namaPelanggan').val('- Umum -');
                $('#pelangganId').val('');
            }
        }
    ).fail(function() {
        console.error('Error fetching cart data');
        alert('Error memuat data cart');
    });
}

function addRow(item) {
    const {
        hash,
        title,
        quantity,
        price,
        total_price,
        options
    } = item;

    // Tombol delete saja
    let btn = `<button type="button" class="btn btn-xs btn-danger" onclick="eDel('${hash}')">
        <i class="fas fa-times"></i>
    </button>`;

    const { diskon, harga_produk } = options;
    
    // Tampilkan harga asli dengan format Rp
    const hargaDisplay = 'Rp ' + rupiah(harga_produk);

    const row = `<tr>
            <td>
                ${title}
                ${diskon ? `<br><small class="text-success"><i class="fas fa-tag"></i> Diskon ${diskon}%</small>` : ''}
            </td> 
            <td>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary btn-decrease" type="button" 
                                data-hash="${hash}">-</button>
                    </div>
                    <input type="number" class="form-control text-center qty-input" 
                           value="${quantity}" min="1" max="999" 
                           data-hash="${hash}" data-original="${quantity}"
                           autocomplete="off">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary btn-increase" type="button" 
                                data-hash="${hash}">+</button>
                    </div>
                </div>
            </td> 
            <td>${hargaDisplay}</td> 
            <td>Rp ${rupiah(total_price)}</td>
            <td>${btn}</td>
        </tr>`;

    $('#resultCart').append(row);
}

function setupEventListeners() {
    // Event listener untuk tombol increase
    $(document).off('click', '.btn-increase').on('click', '.btn-increase', function() {
        const hash = $(this).data('hash');
        ePut(hash, 1);
    });
    
    // Event listener untuk tombol decrease
    $(document).off('click', '.btn-decrease').on('click', '.btn-decrease', function() {
        const hash = $(this).data('hash');
        ePut(hash, -1);
    });

    // Event listener untuk input change (blur)
    $(document).off('blur', '.qty-input').on('blur', '.qty-input', function() {
        handleQuantityChange($(this));
    });

    // Event listener untuk Enter key
    $(document).off('keydown', '.qty-input').on('keydown', '.qty-input', function(e) {
        if (e.which === 13 || e.keyCode === 13) { // Enter key
            e.preventDefault();
            $(this).blur(); // Trigger blur event
        }
    });

    // Event listener untuk input (real-time)
    $(document).off('input', '.qty-input').on('input', '.qty-input', function() {
        const val = $(this).val();
        // Hanya izinkan angka
        if (!/^\d*$/.test(val)) {
            $(this).val(val.replace(/\D/g, ''));
        }
    });

    // Focus event untuk select all text
    $(document).off('focus', '.qty-input').on('focus', '.qty-input', function() {
        $(this).select();
    });
}

function handleQuantityChange($input) {
    const hash = $input.data('hash');
    const newQty = parseInt($input.val());
    const originalQty = parseInt($input.data('original'));
    
    console.log('Handling quantity change:', { hash, newQty, originalQty });
    
    if (isNaN(newQty) || newQty < 1) {
        alert('Quantity harus berupa angka dan minimal 1');
        $input.val(originalQty); // Reset ke nilai semula
        return;
    }
    
    if (newQty === originalQty) {
        console.log('Quantity sama, tidak ada perubahan');
        return; // Tidak ada perubahan
    }
    
    updateQuantityDirect(hash, newQty, originalQty);
}

function updateQuantityDirect(hash, newQty, originalQty) {
    console.log('Updating quantity:', { hash, newQty, originalQty });
    
    if (newQty !== originalQty) {
        // Show loading state
        const $input = $(`.qty-input[data-hash="${hash}"]`);
        $input.prop('disabled', true);
        
        // Gunakan method update dengan parameter set_quantity
        $.ajax({
            type: "PUT",
            url: "/cart/" + hash,
            data: {
                set_quantity: newQty, // Set langsung quantity
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: "json",
            success: function(response) {
                console.log('Quantity set success:', response);
                fetchCart();
            },
            error: function(xhr, status, error) {
                console.error('Quantity set error:', { xhr, status, error });
                alert('Gagal mengupdate quantity. Silakan coba lagi.');
                fetchCart();
            },
            complete: function() {
                $input.prop('disabled', false);
            }
        });
    }
}

// FUNGSI BARU: Reset pelanggan ke umum menggunakan method addPelanggan yang sudah ada
function resetKeUmum() {
    // Tampilkan loading
    const $btn = $('button[onclick="resetKeUmum()"]');
    const originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
    
    // Cari atau buat pelanggan "Umum" terlebih dahulu
    $.ajax({
        type: "GET",
        url: "/transaksi/pelanggan",
        data: {
            search: "- Umum -"
        },
        dataType: "json",
        success: function(pelanggans) {
            console.log('Search pelanggan result:', pelanggans);
            
            let pelangganUmum = pelanggans.find(p => p.nama === "- Umum -");
            
            if (pelangganUmum) {
                // Jika pelanggan umum sudah ada, set ke cart
                setPelangganUmum(pelangganUmum.id, $btn, originalText);
            } else {
                // Jika belum ada, buat pelanggan umum terlebih dahulu
                createPelangganUmum($btn, originalText);
            }
        },
        error: function(xhr, status, error) {
            console.error('Search pelanggan error:', { xhr, status, error });
            // Jika gagal search, coba buat langsung
            createPelangganUmum($btn, originalText);
        }
    });
}

function createPelangganUmum($btn, originalText) {
    // Buat pelanggan umum baru jika belum ada
    $.ajax({
        type: "POST",
        url: "/pelanggan", // Sesuaikan dengan route pelanggan store
        data: {
            nama: "- Umum -",
            alamat: "Alamat Umum",
            nomor_tlp: "",
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "json",
        success: function(response) {
            console.log('Create pelanggan umum success:', response);
            // Setelah berhasil dibuat, set ke cart
            setPelangganUmum(response.id || response.pelanggan.id, $btn, originalText);
        },
        error: function(xhr, status, error) {
            console.error('Create pelanggan umum error:', { xhr, status, error });
            
            // Jika gagal create, coba cara manual
            $('#namaPelanggan').val('- Umum -');
            $('#pelangganId').val('');
            
            $btn.prop('disabled', false).html(originalText);
            alert('Pelanggan direset ke umum (mode lokal)');
        }
    });
}

function setPelangganUmum(pelangganId, $btn, originalText) {
    // Set pelanggan umum ke cart menggunakan method addPelanggan yang sudah ada
    $.ajax({
        type: "POST",
        url: "/transaksi/pelanggan", // Route addPelanggan yang sudah ada
        data: {
            id: pelangganId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "json",
        success: function(response) {
            console.log('Set pelanggan umum success:', response);
            // Refresh cart untuk update tampilan
            fetchCart();
            alert(response.message || 'Pelanggan berhasil direset ke umum');
        },
        error: function(xhr, status, error) {
            console.error('Set pelanggan umum error:', { xhr, status, error });
            
            let errorMessage = 'Gagal mereset pelanggan. Silakan coba lagi.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            alert(errorMessage);
        },
        complete: function() {
            // Restore button
            $btn.prop('disabled', false).html(originalText);
        }
    });
}

function rupiah(number) {
    return new Intl.NumberFormat("id-ID").format(number);
}

function ePut(hash, qty) {
    console.log('ePut called:', { hash, qty });
    
    $.ajax({
        type: "PUT",
        url: "/cart/" + hash,
        data: {
            qty: qty,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "json",
        success: function(response) {
            console.log('ePut success:', response);
            fetchCart();
        },
        error: function(xhr, status, error) {
            console.error('ePut error:', { xhr, status, error });
            alert('Gagal mengupdate quantity. Silakan coba lagi.');
        }
    });
}

function eDel(hash) {
    console.log('eDel called:', hash);
    
    if (confirm('Yakin ingin menghapus item ini dari keranjang?')) {
        $.ajax({
            type: "DELETE",
            url: "/cart/" + hash,
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: "json",
            success: function(response) {
                console.log('eDel success:', response);
                fetchCart();
            },
            error: function(xhr, status, error) {
                console.error('eDel error:', { xhr, status, error });
                alert('Gagal menghapus item. Silakan coba lagi.');
            }
        });
    }
}
</script>
@endpush