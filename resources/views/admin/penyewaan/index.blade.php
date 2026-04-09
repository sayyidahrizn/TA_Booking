@extends('admin.layout')

@section('page-title', 'Penyewaan Fasilitas')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Menggunakan font Inter agar selaras dengan desain modern */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    .main-wrapper {
        font-family: 'Inter', sans-serif;
    }

    /* KOTAK TABEL */
    .table-card {
        background: #ffffff;
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        overflow: hidden;
        margin: 20px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-table th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.05em;
        padding: 18px 20px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
    }

    .custom-table td {
        padding: 16px 20px;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }

    .custom-table tr:hover {
        background-color: #f9fafb;
    }

    /* Styling Badge */
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: capitalize;
    }
    .badge-success { background-color: #dcfce7; color: #15803d; }
    .badge-warning { background-color: #fef9c3; color: #854d0e; }
    .badge-info { background-color: #e0f2fe; color: #0369a1; }
    .badge-danger { background-color: #fee2e2; color: #991b1b; }

    /* Action Buttons */
    .btn-group {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .btn-action {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        color: white;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
    }

    .btn-approve { background-color: #10b981; }
    .btn-approve:hover { background-color: #059669; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3); }

    .btn-reject { background-color: #ef4444; }
    .btn-reject:hover { background-color: #dc2626; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3); }

    /* Styling Nomor */
    .row-number {
        font-weight: 600;
        color: #64748b;
    }
    
    .status-final {
        color: #94a3b8;
        font-size: 12px;
        font-style: italic;
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 6px;
    }
</style>

<div class="main-wrapper">
    <div class="table-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th>Penyewa</th>
                        <th>Fasilitas</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi Konfirmasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penyewaan as $kode => $group)
                    @php $first = $group->first(); @endphp
                    <tr>
                        <td style="text-align: center;"><span class="row-number">{{ $loop->iteration }}</span></td>
                        
                        <td>
                            <div style="font-weight: 600;">{{ $first->user->name ?? '-' }}</div>
                            <small style="color: #64748b; font-family: monospace;">NIK: {{ $first->user->nik ?? 'N/A' }}</small>
                        </td>
                        <td>
                            @foreach($group as $item)
                                <div style="font-size: 12px; color: #475569; margin-bottom: 2px;">
                                    <span style="color: #10b981;">•</span> {{ $item->fasilitas->nama_fasilitas ?? '-' }}
                                </div>
                            @endforeach
                        </td>
                        <td>
                            <div style="font-weight: 500;">
                                {{ \Carbon\Carbon::parse($first->tgl_mulai)->format('d M Y') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500;">
                                {{ \Carbon\Carbon::parse($first->tgl_selesai)->format('d M Y') }}
                            </div>
                        </td>
                        <td>
                            @if($first->status_sewa == 'disetujui')
                                <span class="badge badge-success">Disetujui</span>
                            @elseif($first->status_sewa == 'proses')
                                <span class="badge badge-info">Menunggu</span>
                            @elseif($first->status_sewa == 'batal' || $first->status_sewa == 'ditolak')
                                <span class="badge badge-danger">{{ ucfirst($first->status_sewa) }}</span>
                            @elseif($first->status_sewa == 'selesai')
                                <span class="badge badge-success" style="background-color: #f1f5f9; color: #475569;">Selesai</span>
                            @else
                                <span class="badge badge-warning">{{ ucfirst($first->status_sewa) }}</span>
                            @endif
                        </td>
                        <td>
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
                                    <span class="status-final">Selesai Diproses</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // Handler Tombol Setujui
        const approveButtons = document.querySelectorAll('.btn-submit-approve');
        approveButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Setujui Penyewaan?',
                    text: "Apakah Anda yakin ingin menyetujui penyewaan ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Setujui Sekarang!',
                    cancelButtonText: 'Kembali',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang menyimpan perubahan data',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });
                        form.submit();
                    }
                });
            });
        });

        // Handler Tombol Tolak
        const rejectButtons = document.querySelectorAll('.btn-submit-reject');
        rejectButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Tolak Penyewaan?',
                    text: "Tindakan ini akan membatalkan penyewaan secara permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Tolak!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang menolak penyewaan',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });
                        form.submit();
                    }
                });
            });
        });

        // Flash Message Notifikasi
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: "{{ session('error') }}",
                confirmButtonColor: '#ef4444'
            });
        @endif
    });
</script>

@endsection