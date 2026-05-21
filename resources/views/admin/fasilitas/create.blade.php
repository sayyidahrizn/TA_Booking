@extends('admin.layout')

@section('title','Tambah Fasilitas')
@section('page-title','Tambah Fasilitas')

@section('content')

<style>
    .form-card {
        max-width: 800px;
        margin: 0 auto;
        background: #fff;
        border: 2px solid #cbd5e1;
        border-radius: 6px;
        padding: 30px;
    }

    .form-card h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #1f2937;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #1f2937;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 2px solid #cbd5e1;
        border-radius: 4px;
        font-size: 14px;
    }

    .form-group textarea {
        min-height: 120px;
        resize: vertical;
    }

    .error-text {
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
        display: none;
        font-weight: 500;
    }

    .btn-submit {
        background: #2563eb;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-submit:hover {
        background: #1d4ed8;
    }

    .btn-back {
        margin-left: 10px;
        text-decoration: none;
        color: #374151;
        font-size: 14px;
    }

    #preview_gambar img {
        max-width: 120px;
        margin-right: 10px;
        margin-top: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
</style>

<div class="form-card">
    <h2>Formulir Tambah Fasilitas</h2>

    <form action="{{ route('fasilitas.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>Nama Fasilitas</label>
            <input type="text" name="nama_fasilitas" placeholder="Contoh: Gedung Serbaguna" required>
        </div>

        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" placeholder="Jelaskan detail fasilitas..."></textarea>
        </div>

        <div class="form-group">
            <label>Jumlah Unit</label>
            <input type="number" name="jumlah" min="1" value="1" required>
            <small style="color: #64748b;">Tentukan stok tersedia (contoh: 100 untuk kursi)</small>
        </div>

        {{-- HARGA SEWA --}}
        <div class="form-group">
            <label>Harga Sewa (Jasa)</label>
            <input type="text" id="harga_sewa_view" placeholder="Contoh: 1.000.000" required>
            <input type="hidden" name="harga_sewa" id="harga_sewa">
            <small id="sewa_error" class="error-text">Harga hanya boleh diisi dengan angka</small>
        </div>

        {{-- HARGA BENDA (KOLOM BARU) --}}
        <div class="form-group">
            <label>Harga Barang</label>
            <input type="text" id="harga_benda_view" placeholder="Contoh: 50.000" required>
            <input type="hidden" name="harga_benda" id="harga_benda">
            <small id="benda_error" class="error-text">Harga hanya boleh diisi dengan angka</small>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status_fasilitas">
                <option value="tersedia">Tersedia</option>
                <option value="tidak tersedia">Tidak Tersedia</option>
            </select>
        </div>

        <div class="form-group">
            <label>Gambar Fasilitas</label>
            <input type="file" name="gambar[]" id="gambar_input" multiple>
            <div id="preview_gambar"></div>
        </div>

        <button type="submit" class="btn-submit">Simpan Fasilitas</button>

        <a href="{{ route('fasilitas.index') }}" class="btn-back">Kembali</a>
    </form>
</div>

<script>
    // Fungsi reusable untuk format rupiah
    function setupMasking(viewId, hiddenId, errorId) {
        const viewEl = document.getElementById(viewId);
        const hiddenEl = document.getElementById(hiddenId);
        const errorEl = document.getElementById(errorId);

        viewEl.addEventListener('input', function () {
            // Hapus titik untuk mendapatkan angka murni
            let value = this.value.replace(/\./g, '');
            
            // Cek apakah input adalah angka
            if (!/^\d*$/.test(value)) {
                errorEl.style.display = 'block';
                return;
            }
            
            errorEl.style.display = 'none';
            hiddenEl.value = value; // Simpan angka asli ke input hidden untuk dikirim ke DB

            // Format tampilan dengan titik
            if (value !== '') {
                this.value = new Intl.NumberFormat('id-ID').format(value);
            }
        });
    }

    // Jalankan masking untuk kedua field harga
    setupMasking('harga_sewa_view', 'harga_sewa', 'sewa_error');
    setupMasking('harga_benda_view', 'harga_benda', 'benda_error');

    // Preview gambar
    const gambarInput = document.getElementById('gambar_input');
    const preview = document.getElementById('preview_gambar');

    gambarInput.addEventListener('change', function() {
        preview.innerHTML = '';
        const files = this.files;
        for(let i=0; i<files.length; i++){
            const reader = new FileReader();
            reader.onload = function(e){
                const img = document.createElement('img');
                img.src = e.target.result;
                preview.appendChild(img);
            }
            reader.readAsDataURL(files[i]);
        }
    });

document.addEventListener("DOMContentLoaded", function () {
        // 1. Jika redirect back membawa error validasi dari Laravel Controller
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                text: 'Mohon periksa isi form kembali. Pastikan file berupa gambar dan ukuran maksimal 2MB.',
                iconColor: '#ef4444'
            });
        @endif

        // 2. Jika ada session error kustom dari Controller
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: "{{ session('error') }}",
                iconColor: '#ef4444'
            });
        @endif
    });

    // Loading effect saat form dikirim
    document.getElementById('formFasilitas').onsubmit = function() {
        const btn = document.getElementById('btnSimpan');
        btn.disabled = true;
        btn.innerHTML = 'Sedang Menyimpan...';
    };
</script>
@endsection