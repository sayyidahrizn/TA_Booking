@extends('admin.layout')

@section('page-title', 'Validasi Pengembalian')

@section('content')

<!-- Import Fonts & Assets -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .admin-wrapper-inner{
        font-family: 'Inter', sans-serif;
        color: #1e293b;
    }

    .table-container{
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .table-responsive{
        width: 100%;
        overflow-x: auto;
    }

    .custom-table{
        width: 100%;
        min-width: 1500px;
        border-collapse: collapse;
    }

    .custom-table th{
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 14px 10px;
        border-bottom: 2px solid #edf2f7;
        white-space: nowrap;
        text-align: center;
    }

    .custom-table td{
        padding: 12px 10px;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    .custom-table tbody tr:hover{
        background: #f8fafc;
    }

    /* IMAGE */
    .img-preview{
        width: 55px;
        height: 55px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        transition: .2s;
    }

    .img-preview:hover{
        transform: scale(1.08);
    }

    /* INPUT */
    .input-sm{
        width: 100%;
        max-width: 130px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 7px 10px;
        font-size: 12px;
        outline: none;
    }

    .input-sm:focus{
        border-color: #7c3aed;
    }

    /* BADGE */
    .badge-status{
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }

    .bg-danger-soft{
        background: #fee2e2;
        color: #991b1b;
    }

    .bg-success-soft{
        background: #dcfce7;
        color: #166534;
    }

    .bg-warning-soft{
        background: #fef3c7;
        color: #92400e;
    }

    .bg-secondary-soft{
        background: #e2e8f0;
        color: #475569;
    }

    /* TOTAL TAGIHAN */
    .tagihan-live{
        color: #7c3aed;
        font-weight: 800;
        font-size: 11px;
        margin-top: 5px;
        display: block;
    }

    /* BUTTON */
    .btn-action{
        padding: 8px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
        cursor: pointer;
        transition: .2s;
        border: none;
        width: 100%;
        display: block;
        text-align: center;
    }

    .btn-primary-custom{
        background: #7c3aed;
        color: #fff;
    }

    .btn-primary-custom:hover{
        background: #6d28d9;
    }

    .btn-success-custom{
        background: #16a34a;
        color: #fff;
        margin-top: 5px;
    }

    .btn-success-custom:hover{
        background: #15803d;
    }

    .btn-disabled{
        background: #cbd5e1;
        color: #64748b;
        cursor: not-allowed;
        pointer-events: none;
        margin-top: 5px;
    }

    .empty-data{
        padding: 50px;
        text-align: center;
        color: #94a3b8;
    }

    .kode-booking{
        display: block;
        margin-top: 3px;
        color: #4f46e5;
        font-size: 11px;
        font-weight: 700;
    }

    @media(max-width:768px){

        .custom-table th,
        .custom-table td{
            font-size: 12px;
            padding: 10px 8px;
        }

        .btn-action{
            font-size: 11px;
        }
    }
</style>

<div class="admin-wrapper-inner">

    <div class="table-container">

        <!-- Main Validation Form -->
        <form action="{{ route('admin.pengembalian.validasi') }}" method="POST">
            @csrf

            <div class="table-responsive">

                <table class="custom-table">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Periode Sewa</th>
                            <th>Penyewa</th>
                            <th>Fasilitas</th>
                            <th>Bukti</th>
                            <th>Tgl Kembali</th>
                            <th>Denda Keterlambatan</th>
                            <th>Denda Kerusakan</th>
                            <th>Catatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @php $no = 1; @endphp

                        @forelse($data as $tanggal => $items)

                            @foreach($items as $item)

                            @php
                                $denda = $item->penyewaan->denda;
                            @endphp

                            <tr>

                                <td>{{ $no++ }}</td>

                                <td style="text-align:left; min-width:140px;">
                                    <strong>
                                        {{ \Carbon\Carbon::parse($item->penyewaan->tgl_mulai)->translatedFormat('d M Y') }}
                                    </strong>
                                    <br>
                                    <small>
                                        s/d {{ \Carbon\Carbon::parse($item->penyewaan->tgl_selesai)->translatedFormat('d M Y') }}
                                    </small>
                                </td>

                                <!-- PENYEWA + KODE BOOKING -->
                                <td style="text-align:left; min-width:180px;">

                                    <div style="font-weight:700; color:#1e293b;">
                                        {{ $item->penyewaan->user->name ?? '-' }}
                                    </div>

                                    <span class="kode-booking">
                                        {{ $item->penyewaan->kode_booking ?? '-' }}
                                    </span>

                                </td>

                                <td style="font-weight:600;">
                                    {{ $item->penyewaan->fasilitas->nama_fasilitas }}
                                </td>

                                <td>
                                    <a href="{{ asset('storage/'.$item->bukti_pengembalian) }}" target="_blank">
                                        <img src="{{ asset('storage/'.$item->bukti_pengembalian) }}" class="img-preview">
                                    </a>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d/m/Y') }}
                                </td>

                                <td>

                                    @if($item->denda_telat_otomatis > 0)

                                        <span class="badge-status bg-danger-soft">
                                            Rp {{ number_format($item->denda_telat_otomatis,0,',','.') }}
                                        </span>

                                        <div style="font-size:10px; color:#dc2626; margin-top:3px;">
                                            Telat {{ $item->hari_telat }} Hari
                                        </div>

                                    @else

                                        <span class="badge-status bg-success-soft">
                                            Tepat Waktu
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">

                                        @if(!$denda)

                                            <input
                                                type="text"
                                                class="input-sm"
                                                placeholder="Input Rupiah..."
                                                onkeyup="formatInputRupiah(this, 'denda_real_{{ $item->id }}'); hitungTotalTagihan({{ $item->id }}, {{ $item->denda_telat_otomatis ?? 0 }});"
                                            >

                                        @else

                                            <input
                                                type="text"
                                                class="input-sm"
                                                disabled
                                                value="Rp {{ number_format($denda->biaya_kerusakan ?? 0,0,',','.') }}"
                                            >

                                        @endif

                                        <input
                                            type="hidden"
                                            name="denda_rusak[{{ $item->id }}]"
                                            id="denda_real_{{ $item->id }}"
                                            value="{{ $denda->biaya_kerusakan ?? 0 }}"
                                        >

                                        <span class="tagihan-live" id="total_tagihan_{{ $item->id }}">
                                            Total:
                                            Rp {{
                                                number_format(
                                                    ($item->denda_telat_otomatis ?? 0)
                                                    + ($denda->biaya_kerusakan ?? 0),
                                                    0,
                                                    ',',
                                                    '.'
                                                )
                                            }}
                                        </span>

                                    </div>

                                </td>

                                <td>

                                    @if(!$denda)

                                        <input
                                            type="text"
                                            name="catatan_admin[{{ $item->id }}]"
                                            class="input-sm"
                                            placeholder="Keterangan..."
                                        >

                                    @else

                                        <input
                                            type="text"
                                            class="input-sm"
                                            value="{{ $denda->keterangan_kerusakan }}"
                                            disabled
                                        >

                                    @endif

                                </td>

                                <td>

                                    @if(!$denda)

                                        <span class="badge-status bg-warning-soft">
                                            Belum Divalidasi
                                        </span>

                                    @elseif($denda->status_denda == 'belum_bayar')

                                        <span class="badge-status bg-warning-soft">
                                            Menunggu Bayar
                                        </span>

                                    @else

                                        <span class="badge-status bg-success-soft">
                                            Lunas
                                        </span>

                                    @endif

                                </td>

                                <td style="min-width:160px;">

                                    {{-- BELUM VALIDASI --}}
                                    @if(!$denda)

                                        <button
                                            type="submit"
                                            class="btn-action btn-primary-custom"
                                        >
                                            Selesaikan & Tagih
                                        </button>

                                    {{-- SUDAH VALIDASI TAPI BELUM BAYAR --}}
                                    @elseif($denda->status_denda == 'belum_bayar')

                                        <button
                                            type="button"
                                            class="btn-action btn-success-custom"
                                            onclick="konfirmasiLunas({{ $denda->id_denda }})"
                                        >
                                            Konfirmasi Lunas
                                        </button>

                                    {{-- SUDAH LUNAS --}}
                                    @else

                                        <button
                                            type="button"
                                            class="btn-action btn-disabled"
                                        >
                                            Sudah Divalidasi
                                        </button>

                                    @endif

                                </td>

                            </tr>

                            @endforeach

                        @empty

                            <tr>
                                <td colspan="11" class="empty-data">
                                    Tidak ada data pengembalian yang perlu divalidasi.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </form>

    </div>

</div>

<!-- Hidden Form for Payment Confirmation -->
<form id="form-konfirmasi-lunas" action="" method="POST" style="display:none;">
    @csrf
</form>

<script>

function formatInputRupiah(element, targetId){

    let value = element.value.replace(/[^0-9]/g, '');

    document.getElementById(targetId).value = value || 0;

    element.value = value
        ? new Intl.NumberFormat('id-ID').format(value)
        : '';
}

function hitungTotalTagihan(id, dendaTelat){

    let dendaRusak = parseInt(document.getElementById('denda_real_' + id).value) || 0;

    let total = parseInt(dendaTelat) + dendaRusak;

    document.getElementById('total_tagihan_' + id).innerText =
        'Total: Rp ' + new Intl.NumberFormat('id-ID').format(total);
}

function konfirmasiLunas(idDenda){

    Swal.fire({
        title: 'Konfirmasi Pembayaran',
        text: "Apakah Anda yakin denda ini telah dibayar lunas secara tunai?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Sudah Lunas',
        cancelButtonText: 'Batal'
    })

    .then((result) => {

        if(result.isConfirmed){

            let form = document.getElementById('form-konfirmasi-lunas');

            form.action = `/admin/pengembalian/konfirmasi/${idDenda}`;

            form.submit();
        }
    });
}

</script>

@if(session('success'))

<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        timer: 2000,
        showConfirmButton: false
    });
</script>

@endif

@endsection