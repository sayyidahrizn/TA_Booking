@extends('admin.layout')

@section('page-title', 'Penyewaan Fasilitas')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    .main-wrapper {
        font-family: 'Inter', sans-serif;
        padding: 20px;
        background-color: #f8fafc;
    }

    /* HEADER & SEARCH SECTION */
    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .search-form {
        background: white;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 5px 5px 5px 15px;
        display: flex;
        align-items: center;
        width: 350px;
    }

    .search-group {
        display: flex;
        align-items: center;
        width: 100%;
        gap: 10px;
    }

    .search-input {
        border: none;
        outline: none;
        font-size: 14px;
        width: 100%;
        background: transparent;
    }

    .btn-search {
        background: #2563eb;
        color: white;
        border: none;
        padding: 6px 14px;
        border-radius: 7px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
    }

    /* KOTAK TABEL */
    .table-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
    }

    .custom-table th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.05em;
        padding: 18px 20px;
        border-bottom: 2px solid #f1f5f9;
    }

    .custom-table td {
        padding: 16px 20px;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }

    /* Badge & Status */
    .badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-success { background-color: #dcfce7; color: #15803d; }
    .badge-info { background-color: #e0f2fe; color: #0369a1; }
    .badge-danger { background-color: #fee2e2; color: #991b1b; }
    .badge-warning { background-color: #fef9c3; color: #854d0e; }

    /* Action Buttons */
    .btn-group { display: flex; gap: 8px; justify-content: center; }
    .btn-action {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        color: white;
        transition: 0.2s;
    }
    .btn-approve { background-color: #10b981; }
    .btn-reject { background-color: #ef4444; }
    .status-final {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 6px;
    }

    /* PAGINATION STYLE (Sesuai Desain Fasilitas) */
    .pagination-wrapper {
        margin-top: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    .pagination-info { font-size: 13px; color: #64748b; }
    .pagination-nav { display: flex; gap: 5px; }
    .page-item {
        width: 38px; height: 38px;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none !important; border-radius: 8px;
        border: 1px solid #e2e8f0; background: white;
        color: #2563eb !important; font-weight: 600; font-size: 14px;
    }
    .page-item.active {
        background: #2563eb !important; color: white !important; border-color: #2563eb;
    }
    .page-item.disabled { background: #f8fafc; color: #cbd5e1 !important; cursor: not-allowed; }
</style>

<div class="main-wrapper">
    {{-- HEADER: SEARCH & FILTER --}}
    <div class="header-section">
        <h4 style="font-weight: 700; color: #1e293b; margin: 0;">Data Pengajuan Sewa</h4>
        
        <form action="{{ route('admin.penyewaan.index') }}" method="GET" class="search-form">
            <div class="search-group">
                <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama penyewa atau NIK..." class="search-input">
                <button type="submit" class="btn-search">Cari</button>
                @if(request('search'))
                    <a href="{{ route('admin.penyewaan.index') }}" style="color: #ef4444; font-size: 18px; margin-left: 5px;"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th>Penyewa</th>
                        <th>Fasilitas</th>
                        <th>Tanggal Sewa</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Aksi Konfirmasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penyewaan as $kode => $group)
                    @php $first = $group->first(); @endphp
                    <tr>
                        <td style="text-align: center;"><span style="font-weight: 600; color: #64748b;">{{ ($penyewaan->currentPage()-1) * $penyewaan->perPage() + $loop->iteration }}</span></td>
                        
                        <td>
                            <div style="font-weight: 600; color: #1e293b;">{{ $first->user->name ?? '-' }}</div>
                            <small style="color: #64748b; font-family: monospace; font-size: 11px;">NIK: {{ $first->user->nik ?? 'N/A' }}</small>
                        </td>
                        <td>
                            @foreach($group as $item)
                                <div style="font-size: 12px; color: #475569; margin-bottom: 2px; display: flex; align-items: center; gap: 5px;">
                                    <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%;"></div>
                                    {{ $item->fasilitas->nama_fasilitas ?? '-' }}
                                </div>
                            @endforeach
                        </td>
                        <td>
                            <div style="font-weight: 500; font-size: 12px;">
                                <span style="color: #64748b;">Mulai:</span> {{ \Carbon\Carbon::parse($first->tgl_mulai)->format('d M Y') }}<br>
                                <span style="color: #64748b;">Selesai:</span> {{ \Carbon\Carbon::parse($first->tgl_selesai)->format('d M Y') }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            @if($first->status_sewa == 'disetujui')
                                <span class="badge badge-success">Disetujui</span>
                            @elseif($first->status_sewa == 'proses')
                                <span class="badge badge-info">Menunggu</span>
                            @elseif(
                                $first->status_sewa == 'batal' ||
                                $first->status_sewa == 'ditolak' ||
                                $first->status_sewa == 'dibatalkan_user'
                            )
                                <span class="badge badge-danger">
                                    {{ $first->status_sewa == 'dibatalkan_user' ? 'Dibatalkan Penyewa' : $first->status_sewa }}
                                </span>
                            @else
                                <span class="badge badge-warning">{{ $first->status_sewa }}</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="btn-group">
                                @if($first->status_sewa == 'proses')
                                    <form method="POST" action="{{ route('admin.penyewaan.konfirmasi.group', ['kode'=>$kode]) }}">
                                        @csrf
                                        <button type="button" class="btn-action btn-approve btn-submit-approve">Setujui</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.penyewaan.tolak.group', ['kode'=>$kode]) }}">
                                        @csrf
                                        <button type="button" class="btn-action btn-reject btn-submit-reject">Tolak</button>
                                    </form>
                                @else
                                    @if($first->status_sewa == 'dibatalkan_user')

                                        <span class="status-final"
                                            style="background:#fee2e2; color:#991b1b;">
                                            Dibatalkan Penyewa
                                        </span>

                                    @else

                                        <span class="status-final">
                                            Selesai Diproses
                                        </span>

                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 50px; color: #94a3b8;">
                            <i class="fa-solid fa-inbox" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                            Data penyewaan tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    @if($penyewaan->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Menampilkan <strong>{{ $penyewaan->firstItem() }}</strong> - <strong>{{ $penyewaan->lastItem() }}</strong> dari <strong>{{ $penyewaan->total() }}</strong> data
        </div>
        <div class="pagination-nav">
            @if ($penyewaan->onFirstPage())
                <span class="page-item disabled">❮</span>
            @else
                <a href="{{ $penyewaan->appends(request()->query())->previousPageUrl() }}" class="page-item">❮</a>
            @endif

            @foreach ($penyewaan->getUrlRange(max(1, $penyewaan->currentPage() - 2), min($penyewaan->lastPage(), $penyewaan->currentPage() + 2)) as $page => $url)
                @if ($page == $penyewaan->currentPage())
                    <span class="page-item active">{{ $page }}</span>
                @else
                    <a href="{{ $penyewaan->appends(request()->query())->url($page) }}" class="page-item">{{ $page }}</a>
                @endif
            @endforeach

            @if ($penyewaan->hasMorePages())
                <a href="{{ $penyewaan->appends(request()->query())->nextPageUrl() }}" class="page-item">❯</a>
            @else
                <span class="page-item disabled">❯</span>
            @endif
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle Submit Buttons dengan SweetAlert (Tetap Seperti Kode Asli Anda)
        const approveButtons = document.querySelectorAll('.btn-submit-approve');
        approveButtons.forEach(button => {
            button.addEventListener('click', function () {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Setujui Penyewaan?',
                    text: "Apakah Anda yakin ingin menyetujui penyewaan ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Setujui Sekarang!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) { form.submit(); }
                });
            });
        });

        const rejectButtons = document.querySelectorAll('.btn-submit-reject');
        rejectButtons.forEach(button => {
            button.addEventListener('click', function () {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Tolak Penyewaan?',
                    text: "Tindakan ini akan membatalkan penyewaan secara permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Tolak!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) { form.submit(); }
                });
            });
        });

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 3000, showConfirmButton: false, toast: true, position: 'top-end' });
        @endif
    });
</script>

@endsection