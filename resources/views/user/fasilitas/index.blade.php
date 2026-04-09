@extends('user.layouts.app')

@section('title', 'Fasilitas')

@section('content')

<div class="container-fluid">
    <div class="header-section">
        <h2>Daftar Fasilitas</h2>
    </div>

    <style>
        /* CONTAINER & HEADER */
        .header-section {
            margin-bottom: 30px;
        }
        .header-section h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 5px;
        }

        /* GRID SYSTEM */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 24px;
            margin-top: 20px;
        }

        /* CARD STYLE */
        .card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.1);
            border-color: #cbd5e1;
        }

        /* IMAGE STYLE */
        .image-wrapper {
            width: 100%;
            height: 180px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-bottom: 1px solid #f1f5f9;
            position: relative;
        }

        .card img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Diubah ke cover agar lebih estetik untuk foto fasilitas */
            transition: transform 0.3s ease;
        }

        .card:hover img {
            transform: scale(1.05);
        }

        /* NO IMAGE PLACEHOLDER STYLE */
        .no-image-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            gap: 10px;
        }

        .no-image-placeholder i {
            font-size: 40px;
            opacity: 0.5;
        }

        .no-image-placeholder span {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* CARD BODY */
        .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            text-transform: capitalize;
        }

        .card p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* BUTTON GROUP */
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .btn {
            flex: 1;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
            text-align: center;
        }

        .btn-detail {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-detail:hover {
            background-color: #e2e8f0;
            color: #1e293b;
        }

        .btn-booking {
            background-color: #1e3a8a;
            color: #ffffff;
            border: 1px solid #1e3a8a;
        }

        .btn-booking:hover {
            background-color: #1e40af;
            box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3);
        }

        /* PAGINATION */
        .pagination-custom {
            margin: 50px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .page-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            color: #1e3a8a;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .page-btn:hover:not(.disabled) {
            background: #1e3a8a;
            color: white;
            border-color: #1e3a8a;
        }

        .page-btn.disabled {
            background: #f8fafc;
            color: #cbd5e1;
            cursor: not-allowed;
        }

        /* RESPONSIVE */
        @media (max-width: 640px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="grid">
        @forelse($fasilitas as $f)
            <div class="card">
                <div class="image-wrapper">
                    @if($f->gambar && $f->gambar->count() > 0)
                        <img src="{{ asset('storage/'.$f->gambar->first()->file_gambar) }}" alt="{{ $f->nama_fasilas }}">
                    @else
                        {{-- TAMPILAN JIKA GAMBAR TIDAK ADA --}}
                        <div class="no-image-placeholder">
                            <i class="fa-solid fa-image-slash"></i>
                            <span>Gambar Tidak Ada</span>
                        </div>
                    @endif
                </div>

                <div class="card-body">
                    <h3>{{ $f->nama_fasilitas }}</h3>
                    <p>{{ $f->deskripsi ?? 'Tidak ada deskripsi tersedia untuk fasilitas ini.' }}</p>

                    <div class="btn-group">
                        <a href="{{ route('fasilitas.show', $f->id_fasilitas) }}" class="btn btn-detail">
                            Detail
                        </a>

                        <a href="{{ route('user.penyewaan.create', $f->id_fasilitas) }}" class="btn btn-booking">
                            Booking
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 50px; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1;">
                <p style="color: #64748b; font-size: 16px;">Tidak ada fasilitas tersedia saat ini.</p>
            </div>
        @endforelse
    </div>

    @if($fasilitas->hasPages())
    <div class="pagination-custom">
        @if ($fasilitas->onFirstPage())
            <span class="page-btn disabled">❮</span>
        @else
            <a href="{{ $fasilitas->previousPageUrl() }}" class="page-btn">❮</a>
        @endif

        <span class="page-info">
            {{ $fasilitas->currentPage() }} / {{ $fasilitas->lastPage() }}
        </span>

        @if ($fasilitas->hasMorePages())
            <a href="{{ $fasilitas->nextPageUrl() }}" class="page-btn">❯</a>
        @else
            <span class="page-btn disabled">❯</span>
        @endif
    </div>
    @endif
</div>

@endsection