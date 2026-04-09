@extends('user.layouts.app')

@section('title', 'Detail Fasilitas')

@section('content')

<style>
    .container-detail {
        max-width: 900px;
        margin: 40px auto;
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .carousel {
        position: relative;
        width: 100%;
        height: 350px;
        overflow: hidden;
        border-radius: 10px;
    }

    .carousel img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        display: none;
    }

    .carousel img.active {
        display: block;
    }

    .btn-slide {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,0,0,0.5);
        color: #fff;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        border-radius: 50%;
    }

    .prev { left: 10px; }
    .next { right: 10px; }

    .detail-text {
        margin-top: 20px;
    }

    .detail-text h2 {
        margin-bottom: 10px;
    }

    .harga {
        color: #2563eb;
        font-weight: bold;
        font-size: 18px;
        margin-top: 10px;
    }

    .btn-booking {
        display: inline-block;
        margin-top: 20px;
        background: #10b981;
        color: #fff;
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
    }
    .btn-kembali {
        display: inline-block;
        margin-top: 20px;
        background: #6b7280;
        color: #fff;
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
        margin-left: 10px;
        }
</style>

<div class="container-detail">

    {{-- 🔹 CAROUSEL --}}
    <div class="carousel">
        @forelse($fasilitas->gambar as $g)
            <img src="{{ asset('storage/' . $g->file_gambar) }}" 
                class="{{ $loop->first ? 'active' : '' }}">
        @empty
            <img src="https://via.placeholder.com/800x350" class="active">
        @endforelse

        <button class="btn-slide prev" onclick="prevSlide()">❮</button>
        <button class="btn-slide next" onclick="nextSlide()">❯</button>
    </div>

    {{-- 🔹 DETAIL --}}
    <div class="detail-text">
        <h2>{{ $fasilitas->nama_fasilitas }}</h2>
        <p>{{ $fasilitas->deskripsi }}</p>

        <div class="harga">
            Rp {{ number_format($fasilitas->harga_sewa) }}
        </div>

        <a href="{{ route('user.penyewaan.create', $fasilitas->id_fasilitas) }}" class="btn-booking">Booking Sekarang</a>
        <a href="{{ route('user.fasilitas.index') }}" class="btn-kembali">Kembali</a>
    </div>

</div>

<script>
    let index = 0;
    const slides = document.querySelectorAll('.carousel img');

    function showSlide(i) {
        slides.forEach((img, idx) => {
            img.classList.remove('active');
            if (idx === i) {
                img.classList.add('active');
            }
        });
    }

    function nextSlide() {
        index = (index + 1) % slides.length;
        showSlide(index);
    }

    function prevSlide() {
        index = (index - 1 + slides.length) % slides.length;
        showSlide(index);
    }
</script>

@endsection