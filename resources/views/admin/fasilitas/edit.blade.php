@extends('admin.layout')

@section('title','Edit Fasilitas')
@section('page-title','Edit Fasilitas')

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

.form-card h2{
    text-align:center;
    margin-bottom:30px;
    color:#1f2937;
}

.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    color:#1f2937;
}

.form-group input,
.form-group textarea,
.form-group select{
    width:100%;
    padding:12px;
    border:2px solid #cbd5e1;
    border-radius:4px;
    font-size:14px;
}

.form-group textarea{
    min-height:120px;
    resize:vertical;
}

.error-text{
    color:red;
    font-size:13px;
    margin-top:5px;
    display:none;
}

.btn-submit{
    background:#2563eb;
    color:white;
    padding:12px 20px;
    border:none;
    border-radius:4px;
    cursor:pointer;
    font-size:14px;
}

.btn-submit:hover{
    background:#1d4ed8;
}

.btn-back{
    margin-left:10px;
    text-decoration:none;
    color:#374151;
}

.image-container{
    margin-top:40px;
}

.image-list{
    display:flex;
    flex-wrap:wrap;
    gap:15px;
}

.image-item{
    text-align:center;
}

.image-item img{
    width:130px;
    height:100px;
    object-fit:cover;
    border:1px solid #cbd5e1;
    border-radius:6px;
    padding:4px;
}

.btn-delete{
    background:#dc2626;
    color:white;
    border:none;
    margin-top:6px;
    padding:6px 10px;
    border-radius:4px;
    cursor:pointer;
}
</style>

@if(session('success'))
<div style="max-width: 800px; margin: 0 auto 20px; background: #10b981; color: white; padding: 15px; border-radius: 6px; font-weight: 600;">
    ✅ {{ session('success') }}
</div>
@endif

<div class="form-card">

<h2>Edit Fasilitas</h2>

<form action="{{ route('fasilitas.update', $fasilitas->id_fasilitas) }}"
      method="POST"
      enctype="multipart/form-data">

@csrf
@method('PUT')


<div class="form-group">
<label>Nama Fasilitas</label>
<input type="text"
       name="nama_fasilitas"
       value="{{ $fasilitas->nama_fasilitas }}"
       required>
</div>


<div class="form-group">
<label>Deskripsi</label>
<textarea name="deskripsi">{{ $fasilitas->deskripsi }}</textarea>
</div>

{{-- BAGIAN JUMLAH YANG DITAMBAHKAN --}}
<div class="form-group">
<label>Jumlah</label>
<input type="number" 
       name="jumlah" 
       value="{{ $fasilitas->jumlah }}" 
       min="1" 
       required>
</div>


<div class="form-group">
<label>Harga Sewa</label>

<input type="text"
       id="harga_sewa_view"
       value="{{ number_format($fasilitas->harga_sewa,0,',','.') }}"
       placeholder="Contoh: 1.000.000"
       required>

<input type="hidden"
       name="harga_sewa"
       id="harga_sewa"
       value="{{ $fasilitas->harga_sewa }}">

<small id="harga_error" class="error-text">
Harga hanya boleh angka
</small>

</div>


<div class="form-group">
<label>Status</label>
<select name="status_fasilitas">

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


<div class="form-group">
<label>Tambah Gambar Baru</label>
<input type="file" name="gambar[]" multiple>
</div>


<button type="submit" class="btn-submit">
Update
</button>

<a href="{{ route('fasilitas.index') }}" class="btn-back">
Kembali
</a>

</form>

</div>


<div class="image-container">

<h3>Gambar Saat Ini</h3>

@if($fasilitas->gambar->count() > 0)

<div class="image-list">

@foreach($fasilitas->gambar as $g)

<div class="image-item">

<img src="{{ asset('storage/'.$g->file_gambar) }}">

<form action="{{ route('fasilitas.gambar.hapus', $g->id_gambar) }}"
      method="POST"
      onsubmit="return confirm('Hapus gambar ini?')">

@csrf
@method('DELETE')

<button type="submit" class="btn-delete">
Hapus
</button>

</form>

</div>

@endforeach

</div>

@else

<p>Belum ada gambar.</p>

@endif

</div>


<script>

const hargaView = document.getElementById('harga_sewa_view');
const hargaReal = document.getElementById('harga_sewa');
const hargaError = document.getElementById('harga_error');

hargaView.addEventListener('input', function(){

let value = this.value.replace(/\./g,'');

if(!/^\d*$/.test(value)){
hargaError.style.display = 'block';
return;
}

hargaError.style.display = 'none';
hargaReal.value = value;

if(value !== ''){
this.value = new Intl.NumberFormat('id-ID').format(value);
}

});

// Menghilangkan notifikasi setelah 3 detik
    setTimeout(function() {
        const alert = document.querySelector('[style*="background: #10b981"]');
        if (alert) {
            alert.style.transition = "opacity 0.5s";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 3000);

</script>

@endsection