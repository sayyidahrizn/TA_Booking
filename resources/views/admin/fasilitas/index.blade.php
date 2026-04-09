@extends('admin.layout')

@section('title', 'Data Fasilitas')
@section('page-title', 'Kelola Fasilitas')

@section('content')

<div class="admin-container">
    {{-- Header: Tombol Tambah & Info --}}
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('fasilitas.create') }}" class="btn-tambah">
                <i class="fa-solid fa-plus"></i> Tambah Fasilitas Baru
            </a>
        </div>
        <div class="header-right">
            <span class="total-badge">Total: <b>{{ $fasilitas->total() }}</b> Fasilitas</span>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
    <div id="notif-box" class="alert-success">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Tabel Data --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th width="120">Gambar</th>
                        <th>Nama Fasilitas</th>
                        <th>Deskripsi</th>
                        <th>Harga Sewa</th>
                        <th>Status</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fasilitas as $index => $f)
                    <tr>
                        <td class="text-center">{{ $fasilitas->firstItem() + $index }}</td>
                        <td>
                            <div class="img-preview">
                                @if($f->gambar && $f->gambar->count() > 0)
                                    <img src="{{ asset('storage/'.$f->gambar->first()->file_gambar) }}" alt="Fasilitas">
                                @else
                                    {{-- Tampilan jika gambar tidak ada --}}
                                    <div class="no-image-box">
                                        <i class="fa-solid fa-image-slash"></i>
                                        <span>Gambar tidak ada</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="font-bold">{{ $f->nama_fasilitas }}</td>
                        <td><span class="text-muted">{{ Str::limit($f->deskripsi, 60) }}</span></td>
                        <td class="price-text">Rp {{ number_format($f->harga_sewa, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ strtolower($f->status_fasilitas) == 'tersedia' ? 'bg-success' : 'bg-warning' }}">
                                {{ strtoupper($f->status_fasilitas ?? 'TERSEDIA') }}
                            </span>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('fasilitas.edit', $f->id_fasilitas) }}" class="btn-icon edit" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('fasilitas.destroy', $f->id_fasilitas) }}" method="POST" onsubmit="return confirm('Hapus fasilitas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon delete" title="Hapus">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty-text">Belum ada data fasilitas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($fasilitas->hasPages())
    <div class="pagination-custom">
        @if ($fasilitas->onFirstPage())
            <span class="page-item disabled">❮</span>
        @else
            <a href="{{ $fasilitas->previousPageUrl() }}" class="page-item">❮</a>
        @endif

        <span class="page-item active">{{ $fasilitas->currentPage() }}</span>

        @if ($fasilitas->hasMorePages())
            <a href="{{ $fasilitas->nextPageUrl() }}" class="page-item">❯</a>
        @else
            <span class="page-item disabled">❯</span>
        @endif
    </div>
    @endif
</div>

<style>
    .admin-container { padding: 20px; font-family: 'Segoe UI', system-ui, sans-serif; }
    
    /* Header Styles */
    .admin-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 20px;
    }
    .btn-tambah {
        background: #2563eb; color: white; padding: 10px 20px;
        border-radius: 8px; text-decoration: none; font-weight: 600;
        display: inline-flex; align-items: center; gap: 8px; transition: 0.2s;
    }
    .btn-tambah:hover { background: #1d4ed8; transform: translateY(-1px); }
    .total-badge { background: #f1f5f9; padding: 6px 12px; border-radius: 6px; font-size: 13px; color: #475569; }

    /* Table Styles */
    .table-card {
        background: white; border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden; border: 1px solid #e5e7eb;
    }
    .table-responsive { overflow-x: auto; }
    .custom-table { width: 100%; border-collapse: collapse; text-align: left; }
    .custom-table thead { background: #f8fafc; border-bottom: 2px solid #f1f5f9; }
    .custom-table th { padding: 15px; font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 700; }
    .custom-table td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 14px; color: #1e293b; }
    
    .text-center { text-align: center; }
    .font-bold { font-weight: 600; }
    .text-muted { color: #64748b; font-size: 13px; }

    /* Image Styling */
    .img-preview { width: 80px; height: 50px; }
    .img-preview img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; }

    /* Placeholder Gambar Tidak Ada */
    .no-image-box {
        width: 100%; height: 100%; background: #f8fafc;
        border: 1px dashed #cbd5e1; border-radius: 6px;
        display: flex; flex-direction: column; align-items: center;
        justify-content: center; color: #94a3b8;
    }
    .no-image-box i { font-size: 14px; margin-bottom: 2px; }
    .no-image-box span { font-size: 8px; font-weight: 700; text-transform: uppercase; text-align: center; line-height: 1; }

    /* Price and Badges */
    .price-text { font-weight: 700; color: #2563eb; white-space: nowrap; }
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .bg-success { background: #dcfce7; color: #166534; }
    .bg-warning { background: #fef9c3; color: #854d0e; }

    /* Actions */
    .action-group { display: flex; gap: 8px; justify-content: center; }
    .btn-icon {
        width: 34px; height: 34px; display: flex; align-items: center;
        justify-content: center; border-radius: 8px; transition: 0.2s;
        border: none; cursor: pointer; text-decoration: none;
    }
    .edit { background: #fff7ed; color: #c2410c; }
    .delete { background: #fef2f2; color: #dc2626; }
    .btn-icon:hover { transform: scale(1.1); filter: brightness(0.95); }

    /* Notif & Pagination */
    .alert-success { background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3); }
    .empty-text { text-align: center; padding: 50px; color: #94a3b8; font-style: italic; }
    .pagination-custom { margin-top: 25px; display: flex; justify-content: center; gap: 8px; }
    .page-item { padding: 8px 16px; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: #2563eb; background: white; font-weight: 600; transition: 0.2s; }
    .page-item.active { background: #2563eb; color: white; border-color: #2563eb; }
    .page-item.disabled { color: #cbd5e1; pointer-events: none; background: #f8fafc; }
    .page-item:hover:not(.active):not(.disabled) { background: #f1f5f9; }
</style>

<script>
    // Menghilangkan notifikasi otomatis setelah 3 detik
    setTimeout(() => {
        const notif = document.getElementById('notif-box');
        if(notif) {
            notif.style.transition = '0.5s ease-out';
            notif.style.opacity = '0';
            notif.style.transform = 'translateY(-10px)';
            setTimeout(() => notif.remove(), 500);
        }
    }, 3000);
</script>

@endsection