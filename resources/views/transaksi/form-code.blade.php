<form action="#" class="card card-orange card-outline" id="formBarcode">
    <div class="card-body">
        <div class="input-group">
            <input type="text" class="form-control" id="barcode" placeholder="Kode / Barcode">
        <div class="input-group-append">
            <button type="reset" class="btn btn-danger" id="resetBarcode">
            Clear
        </button>
        </div>
    </div>
    <div class="invalid-feedback" id="msgErrorBarcode"></div>
    </div>
</form>

@push('scripts')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            $('#barcode').focus();

            $('#resetBarcode').click(function() {
                $('#barcode').focus();
            });
            
            $('#formBarcode').submit(function(e) {
                e.preventDefault()
                let kode_produk = $('#barcode').val();

                if (kode_produk.length > 0) {
                    addItem(kode_produk);
                } 
            });
        });

        function addItem(kode_produk) {
            $('#msgErrorBarcode').removeClass('d-block').html('');
            $('#barcode').removeClass('is-invalid').prop('disabled', true);

            // Show loading
            AlertManager.loading('Menambahkan produk...');

            $.post("/cart", {
                'kode_produk': kode_produk
            },
            function (response) {
                // Close loading and show success
                AlertManager.closeLoading();
                AlertManager.berhasil('Berhasil!', response.message || 'Produk berhasil ditambahkan ke keranjang');
                
                if (typeof fetchCart === 'function') {
                    fetchCart();
                }
            }, "json")
            .fail(function(error) {
                console.log('Error response:', error);
                
                // Close loading
                AlertManager.closeLoading();
                
                if (error.status == 422) {
                    let errorMessage = '';
                    
                    // Cek apakah ada error dari validasi atau stok
                    if (error.responseJSON && error.responseJSON.error) {
                        // Error khusus untuk stok
                        errorMessage = error.responseJSON.error;
                        
                        // Cek apakah error tentang stok habis
                        if (errorMessage.includes('sudah habis')) {
                            // Extract nama produk dari pesan error
                            const produkNama = errorMessage.split('Stok produk ')[1]?.split(' sudah habis')[0] || 'Produk';
                            AlertManager.stokHabis(produkNama, 0);
                        } 
                        // Cek apakah error tentang stok tidak mencukupi
                        else if (errorMessage.includes('tidak mencukupi')) {
                            // Extract info dari pesan error
                            const parts = errorMessage.split('Stok tersedia: ');
                            const produkNama = parts[0]?.split('Stok produk ')[1]?.split(' tidak mencukupi')[0] || 'Produk';
                            const stokTersedia = parts[1] ? parseInt(parts[1]) : 0;
                            AlertManager.stokTidakCukup(produkNama, stokTersedia, 1);
                        }
                        else {
                            // Error stok lainnya
                            AlertManager.error('Stok Tidak Tersedia', errorMessage);
                        }
                        
                        // Tetap tampilkan di form untuk feedback visual
                        $('#msgErrorBarcode').addClass('d-block').html(errorMessage);
                        $('#barcode').addClass('is-invalid');
                        
                    } else if (error.responseJSON && error.responseJSON.errors) {
                        // Error validasi biasa
                        if (error.responseJSON.errors.kode_produk) {
                            errorMessage = error.responseJSON.errors.kode_produk[0];
                            $('#msgErrorBarcode').addClass('d-block').html(errorMessage);
                            $('#barcode').addClass('is-invalid');
                            
                            // Show toast untuk error validasi
                            AlertManager.warning('Produk Tidak Ditemukan', errorMessage);
                        }
                    }
                } else {
                    // Error lainnya
                    AlertManager.error('Terjadi Kesalahan', 'Gagal menambahkan produk ke keranjang. Silakan coba lagi.');
                }
            })
            .always(function() {
                $('#barcode').val('').prop('disabled', false).focus();
                
                // Clear error setelah beberapa detik
                setTimeout(function() {
                    $('#msgErrorBarcode').removeClass('d-block').html('');
                    $('#barcode').removeClass('is-invalid');
                }, 3000);
            });
        }
    </script>
@endpush