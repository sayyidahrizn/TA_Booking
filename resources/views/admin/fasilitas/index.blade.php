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
                        <th class="text-center">Stok</th>
                        <th>Harga Sewa</th>
                        <th>Harga Benda</th>
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
                        <td>
                            <div class="fsl-font-bold">{{ $f->nama_fasilitas }}</div>
                            <div class="fsl-text-muted">{{ Str::limit($f->deskripsi, 30) }}</div>
                        </td>
                        <td class="text-center">
                            <span class="fsl-qty-badge">{{ $f->jumlah ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="fsl-price-tag sewa">
                                Rp{{ number_format($f->harga_sewa, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            <span class="fsl-price-tag benda">
                                Rp{{ number_format($f->harga_benda, 0, ',', '.') }}
                            </span>
                        </td>
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

    {{-- Pagination --}}
    @if($fasilitas->hasPages())
    <div class="fsl-pagination-wrapper">
        <div class="fsl-pagination-info">
            Menampilkan {{ $fasilitas->firstItem() }} - {{ $fasilitas->lastItem() }} dari {{ $fasilitas->total() }} data
        </div>
        <div class="fsl-pagination-nav">
            @if ($fasilitas->onFirstPage())
                <span class="fsl-page-item fsl-disabled">❮</span>
            @else
                <a href="{{ $fasilitas->appends(request()->query())->previousPageUrl() }}" class="fsl-page-item">❮</a>
            @endif

            @foreach ($fasilitas->getUrlRange(max(1, $fasilitas->currentPage() - 2), min($fasilitas->lastPage(), $fasilitas->currentPage() + 2)) as $page => $url)
                @if ($page == $fasilitas->currentPage())
                    <span class="fsl-page-item fsl-active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="fsl-page-item">{{ $page }}</a>
                @endif
            @endforeach

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
    .fsl-main-wrapper { padding: 20px; font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    
    /* Header & Search */
    .fsl-header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .fsl-btn-tambah { background: #2563eb; color: white !important; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
    .fsl-btn-tambah:hover { background: #1d4ed8; }

    .fsl-search-form { background: white; border-radius: 10px; border: 1px solid #e2e8f0; padding: 5px 10px; display: flex; align-items: center; width: 300px; }
    .fsl-search-group { display: flex; align-items: center; width: 100%; gap: 8px; }
    .fsl-search-input { border: none; outline: none; font-size: 13px; width: 100%; padding-left: 8px; background: transparent; }
    .fsl-btn-search { background: #f1f5f9; color: #475569; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
    .fsl-total-badge { background: white; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 8px; font-size: 13px; color: #64748b; }

    /* Card & Table */
    .fsl-card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
    .fsl-table-responsive { width: 100%; overflow-x: auto; }
    .fsl-table { width: 100%; border-collapse: collapse; min-width: 1000px; }
    .fsl-table th { background: #f8fafc; padding: 16px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; border-bottom: 2px solid #f1f5f9; text-align: left; }
    .fsl-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 14px; color: #334155; }

    /* Image Preview */
    .fsl-img-preview { width: 75px; height: 50px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; background: #f1f5f9; }
    .fsl-img-preview img { width: 100%; height: 100%; object-fit: cover; }
    .fsl-no-image { height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-size: 20px; }

    .fsl-font-bold { font-weight: 700; color: #1e293b; font-size: 15px; }
    .fsl-text-muted { color: #94a3b8; font-size: 12px; display: block; margin-top: 2px; }
    .fsl-qty-badge { background: #eff6ff; color: #2563eb; padding: 4px 12px; border-radius: 6px; font-weight: 800; font-size: 13px; }

    /* Price Tag Styling */
    .fsl-price-tag { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-block; white-space: nowrap; }
    .fsl-price-tag.sewa { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
    .fsl-price-tag.benda { background: #fff1f2; color: #be123c; border: 1px solid #ffe4e6; }

    /* Badges */
    .fsl-status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
    .fsl-bg-success { background: #dcfce7; color: #15803d; }
    .fsl-bg-warning { background: #fef9c3; color: #854d0e; }

    /* Action Buttons */
    .fsl-action-group { display: flex; gap: 8px; justify-content: center; }
    .fsl-btn-icon { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 8px; transition: 0.2s; border: none; cursor: pointer; text-decoration: none; }
    .fsl-edit { background: #eff6ff; color: #2563eb; }
    .fsl-edit:hover { background: #dbeafe; }
    .fsl-delete { background: #fff1f2; color: #e11d48; }
    .fsl-delete:hover { background: #ffe4e6; }

    /* Pagination */
    .fsl-pagination-wrapper { margin-top: 25px; display: flex; justify-content: space-between; align-items: center; }
    .fsl-pagination-nav { display: flex; gap: 6px; }
    .fsl-page-item { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #2563eb; text-decoration: none; font-weight: 600; font-size: 13px; }
    .fsl-page-item.fsl-active { background: #2563eb; color: white; border-color: #2563eb; }
    .fsl-page-item.fsl-disabled { opacity: 0.4; cursor: not-allowed; }

    .fsl-alert-success { background: #10b981; color: white; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; }
    .fsl-empty-state { padding: 50px; text-align: center; color: #94a3b8; }
</style>

<script>
    setTimeout(() => {
        const notif = document.getElementById('notif-box');
        if(notif) {
            notif.style.transition = '0.5s';
            notif.style.opacity = '0';
            setTimeout(() => notif.remove(), 500);
        }
    }, 3000);
</script>

@endsection