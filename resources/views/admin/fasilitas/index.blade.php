@extends('admin.layout')

@section('title', 'Data Fasilitas')
@section('page-title', 'Data Fasilitas')

@section('content')

<style>
    .btn {
        padding: 6px 12px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
        display: inline-block;
    }

    .btn-add {
        background: #2563eb;
        color: white;
        margin-bottom: 20px;
    }

    .btn-edit {
        background: #facc15;
        color: #000;
    }

    .btn-delete {
        background: #dc2626;
        color: white;
        border: none;
        cursor: pointer;
    }

    .card-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .card {
        border: 2px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        padding: 15px;
    }

    .card img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .card h3 {
        margin: 5px 0;
        font-size: 18px;
        color: #1f2937;
    }

    .card p {
        margin: 5px 0;
        font-size: 14px;
        color: #374151;
    }

    .card .status {
        font-weight: bold;
        margin-top: 5px;
    }

    .card .aksi {
        margin-top: 15px;
        display: flex;
        gap: 8px;
    }
</style>

<a href="{{ route('fasilitas.create') }}" class="btn btn-add">
    + Tambah Fasilitas
</a>

<div class="card-container">
    @forelse($fasilitas as $f)
        <div class="card">

            @if($f->gambar->count() > 0)
                <img src="{{ asset('storage/'.$f->gambar->first()->file_gambar) }}">
            @else
                <img src="https://via.placeholder.com/300x160?text=No+Image">
            @endif

            <h3>{{ $f->nama_fasilitas }}</h3>

            <p>
                <strong>Harga:</strong><br>
                Rp {{ number_format($f->harga_sewa, 0, ',', '.') }}
            </p>

            <p class="status">
                Status: {{ ucfirst($f->status_fasilitas) }}
            </p>

            <div class="aksi">
                <a href="{{ route('fasilitas.edit', $f->id_fasilitas) }}"
                   class="btn btn-edit">
                    Edit
                </a>

                <form action="{{ route('fasilitas.destroy', $f->id_fasilitas) }}"
                      method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus fasilitas ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-delete">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    @empty
        <p>Data fasilitas belum tersedia.</p>
    @endforelse
</div>

@endsection
