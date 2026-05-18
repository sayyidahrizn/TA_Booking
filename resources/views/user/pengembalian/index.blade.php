@extends('user.layouts.app')

{{-- Bagian ini akan tampil di Topbar sebelah kiri, sejajar dengan profil --}}
@section('page_title_content')
<h1 style="margin: 0; font-size: 30px; font-weight: 700; color: #1a202c;">
    Manajemen Pengembalian
</h1>
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    * {
        box-sizing: border-box;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #5b5f65;
        margin-bottom: 30px;
    }

    .card-custom {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border: none;
        padding: 25px;
        margin-bottom: 30px;
        width: 100%;
    }

    /* ========================= */
    /* SECTION DENDA */
    /* ========================= */

    .card-denda {
        border: 2px solid #feb2b2;
        background-color: #fff5f5;
    }

    .btn-bayar-denda {
        background-color: #e53e3e;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 700;
        transition: 0.3s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-bayar-denda:hover {
        background-color: #c53030;
        transform: scale(1.03);
        color: white;
    }

    /* ========================= */
    /* TABLE */
    /* ========================= */

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        border-radius: 15px;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #e2e8f0;
        background: white;
    }

    .table-pengembalian {
        width: 100%;
        min-width: 1150px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-pengembalian thead th {
        background-color: #c1c3c5 !important;
        color: #64748b !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 16px;
        border-bottom: 2px solid #edf2f7 !important;
        border-top: none !important;
        text-align: center;
        white-space: nowrap;
    }

    .table-pengembalian td {
        border: 1px solid #ddd !important;
        padding: 12px;
        vertical-align: middle;
        text-align: center;
        color: #2d3748;
        font-weight: 500;
        white-space: nowrap;
    }

    /* ========================= */
    /* STATUS */
    /* ========================= */

    .text-lunas {
        color: #059669;
        font-weight: 800;
        font-size: 1rem;
    }

    .text-pending {
        color: #d97706;
        font-weight: 800;
        font-size: 1rem;
    }

    .fasilitas-name {
        text-align: left !important;
        font-weight: 700;
        text-transform: capitalize;
        min-width: 180px;
    }

    .form-check-input {
        width: 1.2em;
        height: 1.2em;
        cursor: pointer;
        border: 1px solid #2d3748;
    }

    .badge-custom {
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-block;
    }

    .info-jadwal {
        font-size: 0.75rem;
        margin-top: 8px;
        line-height: 1.5;
    }

    /* ========================= */
    /* FORM */
    /* ========================= */

    .form-control-sm {
        min-width: 180px;
    }

    .btn {
        border-radius: 8px !important;
    }

    /* ========================= */
    /* TABLE DENDA */
    /* ========================= */

    .table {
        margin-bottom: 0;
    }

    .table th,
    .table td {
        vertical-align: middle !important;
        white-space: nowrap;
    }

    /* ========================= */
    /* RESPONSIVE TABLET */
    /* ========================= */

    @media (max-width: 992px) {

        .container.py-5 {
            padding-top: 20px !important;
            padding-bottom: 20px !important;
        }

        .page-title {
            font-size: 2rem;
        }

        .card-custom {
            padding: 18px;
            border-radius: 16px;
        }

        .table-pengembalian {
            min-width: 1000px;
        }

        .table-pengembalian thead th {
            font-size: 0.72rem;
            padding: 12px;
        }

        .table-pengembalian td {
            font-size: 0.82rem;
            padding: 10px;
        }

        .btn,
        .btn-sm,
        .btn-bayar-denda {
            font-size: 0.78rem !important;
            padding: 8px 12px !important;
        }

        .form-control-sm {
            min-width: 160px;
            font-size: 0.75rem;
        }

        .badge-custom,
        .badge {
            font-size: 0.72rem !important;
        }

        .info-jadwal {
            font-size: 0.72rem;
        }
    }

    /* ========================= */
    /* RESPONSIVE MOBILE */
    /* ========================= */

    @media (max-width: 576px) {

        .container.py-5 {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        h1 {
            font-size: 1.5rem !important;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .card-custom {
            padding: 14px;
            border-radius: 14px;
        }

        .table-pengembalian {
            min-width: 950px;
        }

        .table-pengembalian thead th {
            font-size: 0.68rem;
            padding: 10px 8px;
        }

        .table-pengembalian td {
            font-size: 0.72rem;
            padding: 8px;
        }

        .text-lunas,
        .text-pending {
            font-size: 0.85rem;
        }

        .badge-custom,
        .badge {
            font-size: 0.65rem !important;
            padding: 6px 8px !important;
        }

        .info-jadwal {
            font-size: 0.68rem;
        }

        .btn,
        .btn-sm,
        .btn-bayar-denda {
            width: 100%;
            display: block;
            margin-top: 5px;
            font-size: 0.72rem !important;
            padding: 8px 10px !important;
        }

        .form-control-sm {
            width: 100%;
            min-width: 140px;
            font-size: 0.7rem;
        }

        .fasilitas-name {
            min-width: 160px;
        }

        .form-check-input {
            width: 1em;
            height: 1em;
        }
    }
</style>

<div class="container py-5">

    {{-- ========================= --}}
    {{-- NOTIFIKASI TAGIHAN DENDA --}}
    {{-- ========================= --}}
    @if(isset($denda_tunggakan) && $denda_tunggakan->count() > 0)
    <div class="card-custom card-denda shadow-sm animate__animated animate__headShake">
        <h4 class="text-danger fw-bold mb-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Tagihan Denda Perlu Dibayar
        </h4>

        <div class="table-responsive">
            <table class="table table-bordered bg-white">
                <thead class="table-danger">
                    <tr>
                        <th>Fasilitas</th>
                        <th>Denda Telat</th>
                        <th>Denda Rusak</th>
                        <th>Total Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($denda_tunggakan as $denda)
                    <tr>
                        <td class="fw-bold text-start">
                            {{ $denda->penyewaan->fasilitas->nama_fasilitas }}
                        </td>
                        <td>Rp {{ number_format($denda->biaya_keterlambatan, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($denda->biaya_kerusakan, 0, ',', '.') }}</td>
                        <td class="text-danger fw-bold fs-5">
                            Rp {{ number_format($denda->total_denda, 0, ',', '.') }}
                        </td>
                        <td>
                            <a href="{{ route('user.pengembalian.bayar', $denda->id_denda) }}" class="btn btn-bayar-denda shadow-sm">
                                <i class="fas fa-credit-card me-1"></i> Bayar Denda
                            </a>
                        </td>
                    </tr>

                    @if($denda->keterangan_kerusakan)
                    <tr>
                        <td colspan="5" class="bg-light px-3 py-1 small italic text-start">
                            <strong>Catatan Admin:</strong> {{ $denda->keterangan_kerusakan }}
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ========================= --}}
    {{-- FORM PENGEMBALIAN --}}
    {{-- ========================= --}}
    <div class="card-custom">
        <form action="{{ route('user.pengembalian.store') }}" method="POST" enctype="multipart/form-data" class="form-pengembalian">
            @csrf

            <div class="table-responsive">
                <table class="table-pengembalian">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="150">Tanggal Sewa</th>
                            <th>Nama Fasilitas</th>
                            <th width="80">Pilih</th>
                            <th>Sisa Bayar</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Upload Bukti Foto</th>
                            <th>Keterangan / Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($penyewaan->isEmpty())
                        <tr>
                            <td colspan="9" class="py-5 text-center text-muted">
                                <i class="fas fa-info-circle mb-2 d-block" style="font-size: 2rem;"></i>
                                <strong>Belum ada fasilitas yang perlu dikembalikan saat ini.</strong>
                            </td>
                        </tr>
                        @else
                            @php $nomorUrut = 1; @endphp
                            @foreach($penyewaan as $tanggal => $items)
                                @php $rowCount = $items->count(); @endphp
                                @foreach($items as $index => $item)
                                <tr>
                                    @if($index == 0)
                                    <td rowspan="{{ $rowCount }}">{{ $nomorUrut++ }}.</td>
                                    <td rowspan="{{ $rowCount }}" class="fw-bold">
                                        {{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMMM YYYY') }}
                                    </td>
                                    @endif

                                    <td class="fasilitas-name">
                                        {{ $item->fasilitas->nama_fasilitas }}
                                    </td>

                                    <td>
                                        <input type="checkbox" name="id_penyewaan[]" value="{{ $item->id_penyewaan }}" 
                                            class="form-check-input check-item"
                                            {{ ($item->sisa_pembayaran <= 0 && $item->sudah_boleh_kembali) ? '' : 'disabled' }}>
                                    </td>

                                    <td>Rp {{ number_format($item->sisa_pembayaran, 0, ',', '.') }}</td>

                                    <td>
                                        @if($item->sisa_pembayaran <= 0)
                                            <span class="text-lunas">Lunas</span>
                                        @else
                                            <span class="text-pending">Belum Lunas</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($item->sisa_pembayaran <= 0)
                                            <span class="badge bg-success p-2">
                                                <i class="fas fa-check-circle me-1"></i> Terbayar
                                            </span>
                                        @else
                                            <a href="{{ route('user.pembayaran.index', $item->id_penyewaan) }}" class="btn btn-sm btn-warning fw-bold px-3 shadow-sm">
                                                <i class="fas fa-wallet me-1"></i> Bayar
                                            </a>
                                        @endif
                                    </td>

                                    <td>
                                        @if($item->sisa_pembayaran > 0)
                                            <small class="text-muted"><i class="fas fa-lock me-1"></i> Selesaikan Pembayaran</small>
                                        @elseif(!$item->sudah_boleh_kembali)
                                            <small class="text-muted"><i class="fas fa-clock me-1"></i> Menunggu Jadwal Selesai</small>
                                        @else
                                            <input type="file" name="bukti_pengembalian[{{ $item->id_penyewaan }}]" class="form-control form-control-sm" required>
                                        @endif
                                    </td>

                                    <td>
                                        @if($item->sisa_pembayaran > 0)
                                            <button type="button" class="btn btn-sm btn-secondary disabled">
                                                <i class="fas fa-ban me-1"></i> Belum Lunas
                                            </button>
                                        @elseif(!$item->sudah_boleh_kembali)
                                            <span class="badge bg-secondary badge-custom">
                                                <i class="fas fa-clock me-1"></i> Belum Bisa Dikembalikan
                                            </span>
                                            <div class="info-jadwal text-muted">
                                                Jadwal selesai:<br>
                                                {{ \Carbon\Carbon::parse($item->tgl_selesai . ' ' . $item->jam_selesai)->format('d M Y H:i') }}
                                            </div>
                                        @elseif($item->terlambat)
                                            <span class="badge bg-danger badge-custom">
                                                <i class="fas fa-exclamation-triangle me-1"></i> Terlambat
                                            </span>
                                            <div class="info-jadwal text-danger">Pengembalian melebihi batas toleransi 12 jam</div>
                                            <div class="info-jadwal text-muted">
                                                Batas tanpa denda:<br>
                                                {{ $item->batas_tanpa_denda->format('d M Y H:i') }}
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-danger fw-bold px-3 mt-2">
                                                <i class="fas fa-paper-plane me-1"></i> Ajukan
                                            </button>
                                        @else
                                            <span class="badge bg-success badge-custom">
                                                <i class="fas fa-check-circle me-1"></i> Bisa Dikembalikan
                                            </span>
                                            <div class="info-jadwal text-muted">
                                                Batas tanpa denda:<br>
                                                {{ $item->batas_tanpa_denda->format('d M Y H:i') }}
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary fw-bold px-3 mt-2">
                                                <i class="fas fa-paper-plane me-1"></i> Ajukan
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div style="clear: both;"></div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.form-pengembalian').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitter = e.submitter;
            const row = submitter.closest('tr');

            if (row) {
                const checkbox = row.querySelector('.check-item');
                if (checkbox && !checkbox.checked) {
                    checkbox.checked = true;
                }
            }

            const checkedBoxes = this.querySelectorAll('.check-item:checked');

            if (checkedBoxes.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilihan Kosong',
                    text: 'Silahkan centang fasilitas yang ingin dikembalikan!',
                    confirmButtonColor: '#7c3aed'
                });
                return;
            }

            Swal.fire({
                title: 'Sedang Mengirim...',
                text: 'Mohon tunggu, data sedang diproses ke sistem.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    });
</script>

{{-- Toast Notification --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        timer: 4000,
        showConfirmButton: false
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: "{{ session('error') }}",
        confirmButtonColor: '#7c3aed'
    });
</script>
@endif

@endsection