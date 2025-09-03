<form action="" method="get" id="formCariProduk">
    <div class="input-group">
         <input type="text" class="form-control"  placeholder="Nama Produk" id="searchProduk">
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary">
                    Cari
                 </button>
            </div>
        </div>
    </form>
    <table class="table table-sm mt-3">
        <thead>
            <tr>
                <th colspan="2" class="border-0"> Hasil Pencarian :</th>
            </tr>
        </thead>
        <tbody id="resultProduk"></tbody>
    </table>
    @push('scripts')
    <script>
        $(function() {
            $('#formCariProduk').submit(function(e) {
                e.preventDefault();
                const search = $('#searchProduk').val()
                if (search.length >= 3) {
                    fetchCariProduk(search)
                } else {
                    alert('Minimal 3 karakter untuk pencarian');
                }
            })
        })
        
        function fetchCariProduk(search) {
            $('#resultProduk').html('<tr><td colspan="2" class="text-center">Mencari...</td></tr>');
            
            // Modifikasi untuk mengambil data produk dengan stok
            $.ajax({
                url: "/transaksi/produk-with-stok", // Route baru yang akan kita buat
                method: "GET",
                data: { search: search },
                dataType: "json",
                success: function(response) {
                    $('#resultProduk').html('');
                    
                    if (response.length > 0) {
                        response.forEach(item => {
                            addResultProduk(item);
                        });
                    } else {
                        $('#resultProduk').html('<tr><td colspan="2" class="text-center text-muted">Tidak ada data ditemukan</td></tr>');
                    }
                },
                error: function() {
                    // Fallback ke method lama jika route baru belum ada
                    $.getJSON("/transaksi/produk", {
                        search: search
                    }, function(response) {
                        $('#resultProduk').html('');
                        response.forEach(item => {
                            addResultProduk(item);
                        });
                    }).fail(function() {
                        $('#resultProduk').html('<tr><td colspan="2" class="text-center text-danger">Gagal memuat data</td></tr>');
                    });
                }
            });
        }

        function addResultProduk(item) {
            const {
                nama_produk,
                kode_produk,
                stok = null // Stok mungkin tidak ada di response lama
            } = item;

            // Cek status stok dan buat pesan
            let stokInfo = '';
            let btnClass = 'btn-success';
            let btnText = 'Add';
            let btnDisabled = '';
            
            if (stok !== null) {
                if (stok <= 0) {
                    stokInfo = '<br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Stok Habis</small>';
                    btnClass = 'btn-secondary';
                    btnText = 'Habis';
                    btnDisabled = 'disabled';
                } else if (stok <= 5) {
                    stokInfo = `<br><small class="text-warning"><i class="fas fa-exclamation-circle"></i> Stok: ${stok}</small>`;
                } else {
                    stokInfo = `<br><small class="text-info"><i class="fas fa-box"></i> Stok: ${stok}</small>`;
                }
            }

            const btn = `<button type="button" 
                class="btn btn-xs ${btnClass}" 
                onclick="addItem('${kode_produk}')" 
                ${btnDisabled}
                ${stok <= 0 ? 'title="Stok habis, tidak dapat ditambahkan"' : ''}>
                ${btnText}
            </button>`;

            const row = `<tr>
                <td>
                    ${nama_produk}
                    <br><small class="text-muted">${kode_produk}</small>
                    ${stokInfo}
                </td>
                <td class="text-right">${btn}</td>
            </tr>`;
            
            $('#resultProduk').append(row);
        }

        // Override function addItem untuk menangani stok habis
        function addItem(kode_produk) {
            // Cek apakah tombol yang diklik disabled (stok habis)
            const $button = $(`button[onclick="addItem('${kode_produk}')"]`);
            if ($button.prop('disabled') || $button.hasClass('btn-secondary')) {
                alert('Produk ini stoknya sudah habis!');
                return;
            }

            $('#msgErrorBarcode').removeClass('d-block').html('');
            $('#barcode').removeClass('is-invalid');
            
            // Disable button saat proses
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.post("/cart", {
                'kode_produk': kode_produk,
                '_token': $('meta[name="csrf-token"]').attr('content')
            },
            function (response) {
                if (typeof fetchCart === 'function') {
                    fetchCart();
                }
                
                // Clear search results dan focus ke barcode
                $('#resultProduk').html('');
                $('#searchProduk').val('');
                if ($('#barcode').length) {
                    $('#barcode').focus();
                }
                
                // Show success message
                const successMsg = `<tr><td colspan="2" class="text-center text-success">
                    <i class="fas fa-check"></i> ${response.message || 'Produk berhasil ditambahkan!'}
                </td></tr>`;
                $('#resultProduk').html(successMsg);
                
                // Auto clear success message
                setTimeout(() => {
                    $('#resultProduk').html('');
                }, 2000);
            }, "json")
            .fail(function(error) {
                console.log('Error response:', error);
                
                // Restore button
                $button.prop('disabled', false).html('Add');
                
                if (error.status == 422) {
                    let errorMessage = '';
                    
                    // Cek apakah ada error dari validasi atau stok
                    if (error.responseJSON && error.responseJSON.error) {
                        // Error khusus untuk stok
                        errorMessage = error.responseJSON.error;
                        
                        // Tampilkan alert untuk error stok
                        alert(errorMessage);
                        
                        // Update tampilan produk jika stok habis
                        fetchCariProduk($('#searchProduk').val());
                        
                    } else if (error.responseJSON && error.responseJSON.errors) {
                        // Error validasi biasa
                        if (error.responseJSON.errors.kode_produk) {
                            errorMessage = error.responseJSON.errors.kode_produk[0];
                            alert(errorMessage);
                        }
                    }
                } else {
                    // Error lainnya
                    alert('Terjadi kesalahan saat menambahkan produk ke keranjang');
                }
            });
        }
     </script>
     @endpush