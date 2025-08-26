<form action="?" method="get" id="formCariPelanggan">
    <div class="input-group">
         <input type="text" class="form-control"  placeholder="Nama Pelanggan" id="searchPelanggan">
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
                <th class="border-0">Hasil Pencarian :</th>
                <th class="border-0 text-right">
                    <!-- Tombol Set Umum dihapus -->
                </th>
            </tr>
        </thead>
        <tbody id="resultPelanggan"></tbody>
    </table>

    @push('scripts')
    <script>
        $(function() {
            $('#formCariPelanggan').submit(function(e) {
                e.preventDefault();
                const search = $('#searchPelanggan').val()
                if (search.length >= 3) {
                    fetchCariPelanggan(search)
                } else {
                    alert('Minimal 3 karakter untuk pencarian');
                }
            })
        })
        
        function fetchCariPelanggan(search) {
            $('#resultPelanggan').html('<tr><td colspan="2" class="text-center">Mencari...</td></tr>');
            
            $.getJSON("/transaksi/pelanggan", {
                search: search
            },
            function(response) {
                $('#resultPelanggan').html('')
                if(response.length > 0) {
                    response.forEach(item => {
                        addResultPelanggan(item)
                    });
                } else {
                    $('#resultPelanggan').html('<tr><td colspan="2" class="text-center text-muted">Tidak ada data ditemukan</td></tr>');
                }
            })
            .fail(function() {
                $('#resultPelanggan').html('<tr><td colspan="2" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function addResultPelanggan(item){
            const {
                id,
                nama,
                nomor_tlp = '',
                alamat = ''
            } = item

            const btn = `<button type="button" class="btn btn-xs btn-success" onclick="addPelanggan(${id})"
                               title="Pilih pelanggan ini">
                <i class="fas fa-check mr-1"></i> Pilih
            </button>`;
        
            const customerInfo = `
                <strong>${nama}</strong>
                ${nomor_tlp ? `<br><small class="text-muted"><i class="fas fa-phone"></i> ${nomor_tlp}</small>` : ''}
                ${alamat ? `<br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> ${alamat}</small>` : ''}
            `;
            
            const row = `<tr>
                <td>${customerInfo}</td>
                <td class="text-right">${btn}</td>
            </tr>`;
            $('#resultPelanggan').append(row)
        }

        function addPelanggan(id) {
            // Show loading state
            $(`button[onclick="addPelanggan(${id})"]`).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            
            $.post("/transaksi/pelanggan", {
                id: id,
                _token: $('meta[name="csrf-token"]').attr('content') // Tambahkan CSRF token
            },
            function(response) {
                // Clear search results setelah memilih
                $('#resultPelanggan').html('<tr><td colspan="2" class="text-center text-success">Pelanggan berhasil dipilih!</td></tr>');
                $('#searchPelanggan').val('');
                if (typeof fetchCart === 'function') {
                    fetchCart();
                }
                
                // Auto clear success message after 2 seconds
                setTimeout(function() {
                    $('#resultPelanggan').html('');
                }, 2000);
            },
            "json")
            .fail(function() {
                alert('Gagal memilih pelanggan. Silakan coba lagi.');
                $(`button[onclick="addPelanggan(${id})"]`).prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Pilih');
            });
        }

        // Fungsi resetPelanggan dihapus karena tidak diperlukan lagi
        
    </script>
    @endpush