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
        color: red;
        font-size: 13px;
        margin-top: 5px;
        display: none;
    }

    .btn-submit {
        background: #2563eb;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .btn-submit:hover {
        background: #1d4ed8;
    }

    .btn-back {
        margin-left: 10px;
        text-decoration: none;
        color: #374151;
    }

    /* Preview Gambar */
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
            <input type="text" name="nama_fasilitas" required>
        </div>

        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi"></textarea>
        </div>

        <div class="form-group">
            <label>Harga Sewa</label>
            <input type="text" id="harga_sewa_view" placeholder="Contoh: 1.000.000" required>
            <input type="hidden" name="harga_sewa" id="harga_sewa">
            <small id="harga_error" class="error-text">
                Harga hanya boleh diisi dengan angka
            </small>
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

        <button type="submit" class="btn-submit">
            Simpan
        </button>

        <a href="{{ route('fasilitas.index') }}" class="btn-back">
            Kembali
        </a>
    </form>
</div>

<script>
    // Format harga
    const hargaView = document.getElementById('harga_sewa_view');
    const hargaReal = document.getElementById('harga_sewa');
    const hargaError = document.getElementById('harga_error');

    hargaView.addEventListener('input', function () {
        let value = this.value.replace(/\./g, '');
        if (!/^\d*$/.test(value)) {
            hargaError.style.display = 'block';
            return;
        }
        hargaError.style.display = 'none';
        hargaReal.value = value;
        if (value !== '') {
            this.value = new Intl.NumberFormat('id-ID').format(value);
        }
    });

    // Preview gambar sebelum upload
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
</script>

@endsection