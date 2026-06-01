@extends('admin.layout')

@section('page-title', 'Validasi Pengembalian')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .admin-wrapper-inner { font-family: 'Inter', sans-serif; padding: 10px; }
    .table-container { background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; overflow: hidden; }
    .table-responsive { overflow-x: auto; }
    .custom-table { width: 100%; min-width: 1700px; border-collapse: collapse; }

    .custom-table th {
        background: #f8fafc; font-size: 11px; text-transform: uppercase;
        padding: 14px; text-align: center;
    }

    .custom-table td {
        padding: 12px; font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        text-align: center;
    }

    .img-preview { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }

    .input-sm {
        width: 100%; border: 1px solid #cbd5e1;
        border-radius: 8px; padding: 6px;
        font-size: 12px;
    }

    .badge-status {
        padding: 4px 10px; border-radius: 8px;
        font-size: 11px; font-weight: 700;
    }

    .bg-danger-soft { background: #fee2e2; color: #991b1b; }
    .bg-success-soft { background: #dcfce7; color: #166534; }
    .bg-warning-soft { background: #fef3c7; color: #92400e; }
    .bg-primary-soft { background: #dbeafe; color: #1d4ed8; }

    .btn-action {
        padding: 8px 10px; border-radius: 8px;
        font-size: 12px; font-weight: 700;
        width: 100%;
        cursor: pointer;
        border: none;
    }

    .btn-primary-custom { background: #7c3aed; color: white; }
    .btn-disabled { background: #e2e8f0; color: #94a3b8; }

    .btn-print {
        background: #0f172a;
        color: white;
        margin-top: 5px;
        display: inline-block;
        text-decoration: none;
        padding: 8px;
        border-radius: 8px;
        width: 100%;
    }

    .denda-input-wrapper {
        display: none;
        align-items: center;
        gap: 5px;
        border: 1px solid #cbd5e1;
        padding: 5px;
        border-radius: 8px;
        margin-top: 5px;
    }

    .cash-input-wrapper {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .btn-save-inline {
        background: #10b981;
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
    }
    .action-input-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
        justify-content: center;
    }
    .currency-symbol {
        font-weight: 700;
        color: #334155;
    }
    .nominal-input {
        width: 110px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 8px;
        font-size: 12px;
    }
    .btn-save-nominal {
        border: none;
        background: #16a34a;
        color: #fff;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    /* Container utama pagination */
.pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 20px;
    gap: 8px;
}

/* Kotak angka/link */
.pagination li a, 
.pagination li span {
    display: inline-block;
    padding: 8px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #64748b;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
}

/* Saat di-hover */
.pagination li a:hover {
    background-color: #f1f5f9;
    border-color: #7c3aed;
    color: #7c3aed;
}

/* Halaman yang sedang aktif (ungu) */
.pagination li.active span {
    background-color: #7c3aed; /* Warna ungu sesuai tema kamu */
    border-color: #7c3aed;
    color: white;
    font-weight: bold;
}

/* Link yang tidak bisa diklik (Disabled) */
.pagination li.disabled span {
    background-color: #f8fafc;
    color: #cbd5e1;
    cursor: not-allowed;
}

</style>

<div class="admin-wrapper-inner">
    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom:12px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" style="margin-bottom:12px;">
            {{ session('error') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-container">

            <div class="table-responsive">
                <table class="custom-table">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Periode</th>
                            <th>Penyewa</th>
                            <th>Fasilitas</th>
                            <th>Bukti</th>
                            <th>Tgl Kembali</th>
                            <th>Denda Telat</th>
                            <th>Denda Rusak</th>
                            <th>Catatan</th>
                            <th>Status</th>
                            <th>Bayar Tunai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $no = 1; @endphp

                        @forelse($data as $kodeBooking => $items)

                            @php
                                $first = $items->first();
                                $rowCount = count($items);

                                $deadline = \Carbon\Carbon::parse(
                                    $first->penyewaan->tgl_selesai
                                )->startOfDay();

                                $tglKembali = \Carbon\Carbon::parse(
                                    $first->tanggal_pengembalian
                                )->startOfDay();

                                $hariTelat = $tglKembali->gt($deadline)
                                    ? $deadline->diffInDays($tglKembali)
                                    : 0;

                                $totalDendaTelatBooking = $hariTelat * 10000;

                                $groupStatusSewa = $items->pluck('penyewaan.status_sewa')
                                    ->filter()
                                    ->unique();

                                $isMenungguDenda = $groupStatusSewa->contains('menunggu_pembayaran_denda');

                                $isSelesaiSewa = $groupStatusSewa->every(
                                    fn($s) => $s === 'selesai'
                                );

                                $dendaGroup = \App\Models\Denda::where('kode_booking', $kodeBooking)
                                    ->orderByDesc('id_denda')
                                    ->first();

                                $pembayaran = $first->penyewaan->pembayaran
                                    ->where('jenis_pembayaran', 'pelunasan')
                                    ->last();

                                $isRequestTunai = $dendaGroup &&
                                    str_starts_with(
                                        (string) $dendaGroup->kode_pembayaran,
                                        'TUNAI-REQ-'
                                    );
                            @endphp

                            @foreach($items as $i => $item)
                                @php
                                    $harga = $item->penyewaan->fasilitas->harga_benda ?? 0;
                                @endphp

                                <tr>
                                    @if($i === 0)
                                        <td rowspan="{{ $rowCount }}">{{ $no++ }}</td>
                                        <td rowspan="{{ $rowCount }}">
                                            {{ \Carbon\Carbon::parse($first->penyewaan->tgl_mulai)->format('d M Y') }}<br>s/d<br>
                                            {{ \Carbon\Carbon::parse($first->penyewaan->tgl_selesai)->format('d M Y') }}
                                        </td>
                                        <td rowspan="{{ $rowCount }}">
                                            {{ $first->penyewaan->user->name ?? '-' }}<br>
                                            <small>{{ $kodeBooking }}</small>
                                        </td>
                                    @endif

                                    <td>{{ $item->penyewaan->fasilitas->nama_fasilitas ?? '-' }}</td>

                                    <td>
                                        <a href="{{ asset('storage/'.$item->bukti_pengembalian) }}" target="_blank">
                                            <img src="{{ asset('storage/'.$item->bukti_pengembalian) }}" class="img-preview">
                                        </a>
                                    </td>

                                    @if($i === 0)
                                        <td rowspan="{{ $rowCount }}">{{ \Carbon\Carbon::parse($first->tanggal_pengembalian)->format('d/m/Y') }}</td>
                                        <td rowspan="{{ $rowCount }}">
                                            @if($totalDendaTelatBooking > 0)
                                                <span class="badge-status bg-danger-soft">Rp {{ number_format($totalDendaTelatBooking,0,',','.') }}</span>
                                            @else
                                                <span class="badge-status bg-success-soft">Tepat Waktu</span>
                                            @endif
                                        </td>
                                    @endif

                                    <td>
                                        {{-- Tambahkan atribut form="form_{{ $kodeBooking }}" --}}
                                        <select name="jenis_kerusakan[{{ $item->id }}]"
                                                form="form_{{ $kodeBooking }}"
                                                onchange="handleDenda(this,'{{ $item->id }}',{{ $harga }})"
                                                class="input-sm">
                                            <option value="tidak_rusak">Tidak Rusak</option>
                                            <option value="ringan">Rusak Ringan</option>
                                            <option value="berat">Rusak Berat</option>
                                        </select>

                                        <div id="denda_{{ $item->id }}" class="denda-input-wrapper">
                                            <span>Rp</span>
                                            <input type="text" class="input-sm rupiah" 
                                                name="denda_rusak[{{ $item->id }}]"
                                                form="form_{{ $kodeBooking }}"
                                                id="input_{{ $item->id }}" value="0">
                                        </div>
                                    </td>

                                    <td>
                                        <input type="text" name="catatan_admin[{{ $item->id }}]" 
                                            form="form_{{ $kodeBooking }}" class="input-sm">
                                    </td>

                                    @if($i === 0)
                                        <td rowspan="{{ $rowCount }}">
                                            @if($dendaGroup && $dendaGroup->status_denda == 'lunas')
                                                <span class="badge-status bg-success-soft">LUNAS</span>
                                            @elseif($isRequestTunai)
                                                <span class="badge-status bg-primary-soft">MENUNGGU VERIFIKASI TUNAI</span>
                                            @elseif($dendaGroup)
                                                <span class="badge-status bg-warning-soft">MENUNGGU PEMBAYARAN</span>
                                            @else
                                                <span class="badge-status bg-danger-soft">BELUM DITAGIH</span>
                                            @endif
                                        </td>

                                        {{-- 💰 BAYAR TUNAI (TIDAK DIHAPUS) --}}
                                        <td rowspan="{{ $rowCount }}">

                                            @if($dendaGroup)

                                                <div style="margin-bottom:10px;">
                                                    <small>Total Denda</small><br>

                                                    <span style="
                                                        color:#dc2626;
                                                        font-size:16px;
                                                        font-weight:700;
                                                    ">
                                                        Rp {{ number_format($dendaGroup->total_denda,0,',','.') }}
                                                    </span>
                                                </div>

                                                @if(
                                                    $dendaGroup->status_denda == 'belum_bayar' &&
                                                    $isRequestTunai
                                                )

                                                    <form
                                                        id="formTunai{{ $dendaGroup->id_denda }}"
                                                        action="{{ route('admin.pengembalian.konfirmasi', $dendaGroup->id_denda) }}"
                                                        method="POST"
                                                    >
                                                        @csrf

                                                        <input
                                                            name="jumlah_bayar"
                                                            class="raw-nominal"
                                                        >
                                                    </form>

                                                    <div class="action-input-wrapper">

                                                        <span class="currency-symbol">Rp</span>


                                                        <button
                                                            type="submit"
                                                            form="formTunai{{ $dendaGroup->id_denda }}"
                                                            class="btn-save-nominal"
                                                        >
                                                            Simpan
                                                        </button>

                                                    </div>

                                                @elseif($dendaGroup->status_denda == 'lunas')

                                                    <span class="badge-status bg-success-soft">
                                                        Lunas
                                                    </span>

                                                @else

                                                    <small>Transfer / Tunai</small>

                                                @endif

                                            @else

                                                -

                                            @endif

                                        </td>

                                        {{-- 🧾 AKSI (TERMASUK CETAK BUKTI) --}}
                                        <td rowspan="{{ $rowCount }}">

    {{-- 1. LUNAS --}}
    @if($dendaGroup && $dendaGroup->status_denda == 'lunas')

        <button type="button"
                class="btn-action bg-success-soft"
                style="color:#166534;"
                disabled>
            ✓ Selesai
        </button>

        {{-- CETAK BUKTI HANYA JIKA BUKAN MIDTRANS --}}
        @if(!($pembayaran && $pembayaran->metode_pembayaran == 'midtrans'))
            <a href="{{ route('admin.pengembalian.bukti', $dendaGroup->id_denda) }}"
               target="_blank"
               class="btn-print">
                Cetak Bukti
            </a>
        @endif


    {{-- 2. MENUNGGU DENDA --}}
    @elseif($isMenungguDenda)
        <button type="button" class="btn-action btn-disabled" disabled>
            Menunggu Pembayaran Denda
        </button>


    {{-- 3. SELESAI TANPA DENDA --}}
    @elseif($isSelesaiSewa && !$dendaGroup)
        <button type="button" class="btn-action btn-disabled" disabled>
            ✓ Selesai
        </button>


    {{-- 4. FORM VALIDASI --}}
    @elseif(!$dendaGroup)

        <form id="form_{{ $kodeBooking }}"
              method="POST"
              action="{{ route('admin.pengembalian.validasi') }}">
            @csrf

            <input type="hidden" name="submit_booking" value="{{ $kodeBooking }}">

            @foreach($items as $row)
                <input type="hidden"
                       id="hk_{{ $row->id }}"
                       name="jenis_kerusakan[{{ $row->id }}]"
                       value="tidak_rusak">

                <input type="hidden"
                       id="hdr_{{ $row->id }}"
                       name="denda_rusak[{{ $row->id }}]"
                       value="0">

                <input type="hidden"
                       id="hca_{{ $row->id }}"
                       name="catatan_admin[{{ $row->id }}]"
                       value="">
            @endforeach

            <button type="button"
                    class="btn-action btn-primary-custom"
                    onclick="submitValidasi('{{ $kodeBooking }}', [{{ $items->pluck('id')->join(',') }}])">
                Selesaikan & Tagih
            </button>
        </form>


    {{-- 5. MIDTRANS PENDING --}}
    @elseif(
        $pembayaran &&
        $pembayaran->metode_pembayaran == 'midtrans' &&
        $pembayaran->status_pembayaran == 'pending'
    )
        <button type="button" class="btn-action btn-disabled" disabled>
            Menunggu Pembayaran Midtrans
        </button>


    {{-- 6. DENDA BELUM BAYAR --}}
    @elseif($dendaGroup && $dendaGroup->status_denda == 'belum_bayar')
        <button type="button" class="btn-action btn-disabled" disabled>
            Menunggu Pembayaran Denda
        </button>


    {{-- 7. DEFAULT --}}
    @else
        <button type="button" class="btn-action btn-disabled" disabled>
            Diproses...
        </button>
    @endif

</td>
                                    @endif

                                </tr>

                            @endforeach

                        @empty
                            <tr>
                                <td colspan="12">Data kosong</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="padding:15px">
                {{ $data->links() }}
            </div>

    </div>
</div>

<script>
// Format rupiah pada input
function handleDenda(select, id, harga) {
    let box = document.getElementById('denda_' + id);
    let input = document.getElementById('input_' + id);
    if (select.value === 'ringan') {
        box.style.display = 'flex';
        input.value = '';
        input.readOnly = false;
    } else if (select.value === 'berat') {
        box.style.display = 'flex';
        input.value = new Intl.NumberFormat('id-ID').format(harga);
        input.readOnly = true;
    } else {
        box.style.display = 'none';
        input.value = 0;
    }
}

document.querySelectorAll('.rupiah').forEach(function (input) {
    input.addEventListener('keyup', function () {
        let angka = this.value.replace(/\D/g, '');
        this.value = new Intl.NumberFormat('id-ID').format(angka);
    });
});

function submitValidasi(kodeBooking, ids) {
    let hasDenda = false;

    ids.forEach(function (id) {
        // Ambil elemen dari baris tabel (UI)
        let selectEl = document.querySelector('select[name="jenis_kerusakan[' + id + ']"]');
        let dendaEl  = document.getElementById('input_' + id);
        let catatanEl = document.querySelector('input[name="catatan_admin[' + id + ']"]');

        // Target hidden input di dalam FORM (yang akan dikirim ke server)
        let hkEl  = document.getElementById('hk_' + id);
        let hdrEl = document.getElementById('hdr_' + id);
        let hcaEl = document.getElementById('hca_' + id);

        if (selectEl && hkEl) hkEl.value = selectEl.value;
        
        if (dendaEl && hdrEl) {
            // Hapus titik ribuan agar menjadi angka murni (e.g. 10.000 -> 10000)
            let nominal = dendaEl.value.replace(/\./g, '');
            hdrEl.value = nominal || '0';
            if (parseInt(nominal) > 0) hasDenda = true;
        }
        
        if (catatanEl && hcaEl) hcaEl.value = catatanEl.value;
    });

    Swal.fire({
        title: 'Validasi Pengembalian?',
        text: "Data akan disimpan dan status akan diperbarui.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        confirmButtonText: 'Ya, Selesaikan!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form_' + kodeBooking).submit();
        }
    });
}
</script>

@endsection
