@extends('admin.layout')

@section('title', 'Edit Fasilitas')
@section('page-title', 'Edit Fasilitas')

@section('content')

<form action="{{ route('fasilitas.update', $fasilitas->id_fasilitas) }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div>
        <label>Nama Fasilitas</label><br>
        <input type="text"
               name="nama_fasilitas"
               value="{{ $fasilitas->nama_fasilitas }}"
               required>
    </div>

    <br>

    <div>
        <label>Deskripsi</label><br>
        <textarea name="deskripsi" rows="4" required>{{ $fasilitas->deskripsi }}</textarea>
    </div>

    <br>

    <div>
        <label>Harga Sewa</label><br>
        <input type="number"
               name="harga_sewa"
               value="{{ $fasilitas->harga_sewa }}"
               required>
    </div>

    <br>

    <div>
        <label>Status</label><br>
        <select name="status_fasilitas" required>
            <option value="tersedia"
                {{ $fasilitas->status_fasilitas == 'tersedia' ? 'selected' : '' }}>
                Tersedia
            </option>
            <option value="tidak tersedia"
                {{ $fasilitas->status_fasilitas == 'tidak tersedia' ? 'selected' : '' }}>
                Tidak Tersedia
            </option>
        </select>
    </div>

    <br>

    <div>
        <label>Tambah Gambar Baru</label><br>
        <input type="file" name="gambar[]" multiple>
    </div>

    <br>

    <button type="submit">Update</button>
    <a href="{{ route('fasilitas.index') }}">Kembali</a>
</form>

<hr>

<h3>Gambar Saat Ini</h3>

@if($fasilitas->gambar->count() > 0)
    <div style="display:flex;flex-wrap:wrap;">
        @foreach($fasilitas->gambar as $g)
            <div style="margin:10px;text-align:center;">
                <img src="{{ asset('storage/'.$g->file_gambar) }}"
                     width="120"
                     style="border:1px solid #ccc;padding:5px;"><br><br>

                <form action="{{ route('fasilitas.gambar.hapus', $g->id_gambar) }}"
                      method="POST"
                      onsubmit="return confirm('Hapus gambar ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus</button>
                </form>
            </div>
        @endforeach
    </div>
@else
    <p>Belum ada gambar.</p>
@endif

@endsection
