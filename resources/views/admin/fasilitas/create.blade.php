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
            <input type="number" name="harga_sewa" required>
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
            <input type="file" name="gambar[]" multiple>
        </div>

        <button type="submit" class="btn-submit">
            Simpan
        </button>

        <a href="{{ route('fasilitas.index') }}" class="btn-back">
            Kembali
        </a>
    </form>
</div>

@endsection
