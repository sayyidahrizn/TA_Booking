@extends('admin.layout')

@section('title', 'Data Fasilitas')
@section('page-title', 'Kelola Fasilitas')

@section('content')

<div class="fsl-main-wrapper">
    {{-- Header: Tombol Tambah & Form Pencarian --}}
    <div class="fsl-header-section">
        <div class="fsl-header-left">
            <a href="{{ route('fasilitas.create') }}" class="fsl-btn-tambah">
                <i class="fa-solid fa-plus"></i> Tambah Fasilitas Baru
            </a>
        </div>
        
        <div class="fsl-header-right">
            <form action="{{ route('fasilitas.index') }}" method="GET" class="fsl-search-form">
                <div class="fsl-search-group">
                    <i class="fa-solid fa-magnifying-glass fsl-search-icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama fasilitas..." class="fsl-search-input">
                    @if(request('search'))
                        <a href="{{ route('fasilitas.index') }}" class="fsl-search-clear"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                    <button type="submit" class="fsl-btn-search">Cari</button>
                </div>
            </form>
            <div class="fsl-total-badge">Total: <b>{{ $fasilitas->total() }}</b> Fasilitas</div>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
    <div id="notif-box" class="fsl-alert fsl-alert-success">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Tabel Data Fasilitas --}}
    <div class="fsl-card">
        <div class="fsl-table-responsive">
            <table class="fsl-table">
                <thead>
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th width="100">Gambar</th>
                        <th>Nama Fasilitas</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Jumlah</th>
                        <th>Harga Sewa</th>
                        <th>Status</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fasilitas as $index => $f)
                    <tr>
                        <td class="text-center">{{ $fasilitas->firstItem() + $index }}</td>
                        <td>
                            <div class="fsl-img-preview">
                                @if($f->gambar && $f->gambar->count() > 0)
                                    <img src="{{ asset('storage/'.$f->gambar->first()->file_gambar) }}" alt="Fasilitas">
                                @else
                                    <div class="fsl-no-image">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="fsl-font-bold">{{ $f->nama_fasilitas }}</td>
                        <td><span class="fsl-text-muted">{{ Str::limit($f->deskripsi, 45) }}</span></td>
                        <td class="text-center">
                            <span class="fsl-qty-badge">{{ $f->jumlah ?? 0 }}</span>
                        </td>
                        <td class="fsl-price-text">Rp {{ number_format($f->harga_sewa, 0, ',', '.') }}</td>
                        <td>
                            <span class="fsl-status-badge {{ strtolower($f->status_fasilitas) == 'tersedia' ? 'fsl-bg-success' : 'fsl-bg-warning' }}">
                                {{ strtoupper($f->status_fasilitas ?? 'TERSEDIA') }}
                            </span>
                        </td>
                        <td>
                            <div class="fsl-action-group">
                                <a href="{{ route('fasilitas.edit', $f->id_fasilitas) }}" class="fsl-btn-icon fsl-edit" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('fasilitas.destroy', $f->id_fasilitas) }}" method="POST" onsubmit="return confirm('Hapus fasilitas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="fsl-btn-icon fsl-delete" title="Hapus">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="fsl-empty-state">
                            <i class="fa-solid fa-folder-open"></i>
                            <p>Data fasilitas tidak ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination Modern Kotak-Kotak --}}
    @if($fasilitas->hasPages())
    <div class="fsl-pagination-wrapper">
        <div class="fsl-pagination-info">
            Menampilkan {{ $fasilitas->firstItem() }} - {{ $fasilitas->lastItem() }} dari {{ $fasilitas->total() }} data
        </div>
        <div class="fsl-pagination-nav">
            {{-- Previous --}}
            @if ($fasilitas->onFirstPage())
                <span class="fsl-page-item fsl-disabled">❮</span>
            @else
                <a href="{{ $fasilitas->appends(request()->query())->previousPageUrl() }}" class="fsl-page-item">❮</a>
            @endif

            {{-- Page Numbers --}}
            @foreach ($fasilitas->getUrlRange(max(1, $fasilitas->currentPage() - 2), min($fasilitas->lastPage(), $fasilitas->currentPage() + 2)) as $page => $url)
                @if ($page == $fasilitas->currentPage())
                    <span class="fsl-page-item fsl-active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="fsl-page-item">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Next --}}
            @if ($fasilitas->hasMorePages())
                <a href="{{ $fasilitas->appends(request()->query())->nextPageUrl() }}" class="fsl-page-item">❯</a>
            @else
                <span class="fsl-page-item fsl-disabled">❯</span>
            @endif
        </div>
    </div>
    @endif
</div>

<style>
    /* Global Container */
    .fsl-main-wrapper { padding: 20px; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; }

    /* Header & Search Area */
    .fsl-header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    
    .fsl-btn-tambah { 
        background: #2563eb; color: white !important; padding: 10px 20px; 
        border-radius: 8px; text-decoration: none; font-weight: 600; 
        display: inline-flex; align-items: center; gap: 8px; transition: 0.3s;
    }
    .fsl-btn-tambah:hover { background: #1d4ed8; box-shadow: 0 4px 12px rgba(37,99,235,0.2); }

    .fsl-header-right { display: flex; align-items: center; gap: 15px; }
    .fsl-total-badge { background: #ffffff; border: 1px solid #e2e8f0; padding: 8px 15px; border-radius: 8px; font-size: 13px; color: #475569; }

    /* Search Box */
    .fsl-search-form { background: white; border-radius: 10px; border: 1px solid #e2e8f0; padding: 5px 5px 5px 15px; display: flex; align-items: center; width: 320px; }
    .fsl-search-group { position: relative; display: flex; align-items: center; width: 100%; gap: 10px; }
    .fsl-search-icon { color: #94a3b8; font-size: 14px; }
    .fsl-search-input { border: none; outline: none; font-size: 14px; width: 100%; background: transparent; }
    .fsl-search-clear { color: #cbd5e1; margin-right: 5px; cursor: pointer; }
    .fsl-search-clear:hover { color: #ef4444; }
    .fsl-btn-search { background: #2563eb; color: white; border: none; padding: 6px 14px; border-radius: 7px; cursor: pointer; font-size: 12px; font-weight: 600; }

    /* Card & Table */
    .fsl-card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; }
    .fsl-table-responsive { width: 100%; overflow-x: auto; }
    .fsl-table { width: 100%; border-collapse: collapse; text-align: left; min-width: 1000px; }
    .fsl-table thead { background: #f8fafc; border-bottom: 2px solid #f1f5f9; }
    .fsl-table th { padding: 15px; font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.05em; }
    .fsl-table td { padding: 14px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 14px; color: #1e293b; }

    /* Element Styles */
    .fsl-img-preview { width: 70px; height: 50px; border-radius: 6px; overflow: hidden; background: #f1f5f9; }
    .fsl-img-preview img { width: 100% !important; height: 100% !important; object-fit: cover !important; }
    .fsl-no-image { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-size: 20px; }
    
    .fsl-font-bold { font-weight: 600; color: #0f172a; }
    .fsl-text-muted { color: #64748b; font-size: 13px; }
    .fsl-qty-badge { background: #f1f5f9; padding: 4px 12px; border-radius: 6px; font-weight: 700; color: #475569; }
    .fsl-price-text { font-weight: 700; color: #2563eb; }

    /* Badges */
    .fsl-status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
    .fsl-bg-success { background: #dcfce7; color: #15803d; }
    .fsl-bg-warning { background: #fef9c3; color: #854d0e; }

    /* Action Buttons */
    .fsl-action-group { display: flex; gap: 8px; justify-content: center; }
    .fsl-btn-icon { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: none; cursor: pointer; transition: 0.2s; text-decoration: none; }
    .fsl-edit { background: #eff6ff; color: #2563eb; }
    .fsl-edit:hover { background: #dbeafe; }
    .fsl-delete { background: #fef2f2; color: #dc2626; }
    .fsl-delete:hover { background: #fee2e2; }

    .fsl-empty-state { padding: 60px 0; text-align: center; color: #94a3b8; }
    .fsl-empty-state i { font-size: 40px; margin-bottom: 10px; opacity: 0.5; }

    /* PAGINATION STYLE (KOTAK-KOTAK MODERN) */
    .fsl-pagination-wrapper { margin-top: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .fsl-pagination-info { font-size: 13px; color: #64748b; }
    .fsl-pagination-nav { display: flex; gap: 8px; }
    
    .fsl-page-item {
        width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
        text-decoration: none !important; border-radius: 8px; border: 1px solid #e2e8f0;
        background-color: #ffffff; color: #2563eb !important; font-size: 14px; font-weight: 600;
        transition: all 0.2s ease;
    }
    .fsl-page-item.fsl-active {
        background-color: #2563eb !important; color: #ffffff !important;
        border-color: #2563eb !important; box-shadow: 0 4px 10px rgba(37,99,235,0.2);
    }
    .fsl-page-item.fsl-disabled { background-color: #f8fafc; color: #cbd5e1 !important; cursor: not-allowed; }
    .fsl-page-item:hover:not(.fsl-active):not(.fsl-disabled) { background-color: #f1f5f9; border-color: #cbd5e1; }

    .fsl-alert { padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; color: white; display: flex; align-items: center; gap: 10px; }
    .fsl-alert-success { background: #10b981; }
    .text-center { text-align: center; }
</style>

<script>
    // Auto hide notifikasi
    setTimeout(() => {
        const notif = document.getElementById('notif-box');
        if(notif) {
            notif.style.transition = '0.5s ease-out';
            notif.style.opacity = '0';
            setTimeout(() => notif.remove(), 500);
        }
    }, 3000);
</script>

@endsection