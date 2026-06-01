@extends('user.layouts.app')

@section('page_title_content')
<h1 style="margin: 0; font-size: 30px; font-weight: 700; color: #1a202c;">
    Manajemen Pengembalian
</h1>
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
* { box-sizing: border-box; }

/* ===== DENDA (TIDAK DIUBAH) ===== */
.card-custom {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border: none;
    padding: 25px;
    margin-bottom: 30px;
    width: 100%;
}

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
}

.btn-bayar-denda:hover {
    background-color: #c53030;
    transform: scale(1.03);
    color: white;
}

/* ===== TABEL PENGEMBALIAN ===== */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: white;
}

.table-pengembalian {
    width: 100%;
    min-width: 1100px;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.table-pengembalian thead th {
    background-color: #f8f9fa;
    color: #374151;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 14px 12px;
    text-align: center;
    border-bottom: 2px solid #e5e7eb;
    border-right: 1px solid #e5e7eb;
    white-space: nowrap;
}

.table-pengembalian thead th:last-child {
    border-right: none;
}

.table-pengembalian tbody td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
    border-right: 1px solid #f0f0f0;
    vertical-align: middle;
    color: #374151;
}

.table-pengembalian tbody td:last-child {
    border-right: none;
}

.table-pengembalian tbody tr:last-child td {
    border-bottom: none;
}

.table-pengembalian tbody tr:hover {
    background-color: #fafafa;
}

/* No & Tanggal */
.td-no {
    font-weight: 700;
    color: #6b7280;
    width: 50px;
}

.badge-tanggal {
    background-color: #f3f4f6;
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
    display: inline-block;
}

/* Nama fasilitas */
.td-fasilitas {
    text-align: left !important;
    font-weight: 500;
}

/* Checkbox pilih */
.form-check-input-custom {
    width: 1.3em;
    height: 1.3em;
    cursor: pointer;
    accent-color: #3b3f8c;
}

/* Upload */
.input-upload {
    font-size: 0.78rem;
}

/* Sisa bayar */
.td-sisa {
    font-weight: 700;
    white-space: nowrap;
}

/* Pembayaran */
.btn-bayar {
    color: #d97706;
    text-decoration: underline;
    font-weight: 600;
    font-size: 0.85rem;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}

.btn-bayar:hover { color: #b45309; }

.text-terbayar {
    color: #059669;
    font-weight: 700;
}

/* Status */
.text-lunas     { color: #059669; font-weight: 700; }
.text-belum     { color: #d97706; font-weight: 700; }

/* Keterangan / Aksi */
.btn-ajukan {
    background-color: #3b3f8c;
    color: #fff;
    border: none;
    padding: 8px 22px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s;
    white-space: nowrap;
}

.btn-ajukan:hover {
    background-color: #2d3170;
    transform: scale(1.03);
}

.btn-ajukan:disabled,
.btn-ajukan-disabled {
    background-color: #9ca3af;
    color: #fff;
    border: none;
    padding: 8px 22px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: not-allowed;
    white-space: nowrap;
}

.text-selesaikan {
    font-size: 0.8rem;
    color: #9ca3af;
    font-style: italic;
}

/* rowspan helper: border kiri tebal untuk group */
.td-group-start {
    border-left: 3px solid #e5e7eb;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 10px;
}

@media (max-width: 576px) {
    .table-pengembalian { min-width: 950px; }
}
</style>

<div class="container py-5">

{{-- ========================= DENDA ========================= --}}
@if(isset($denda_tunggakan) && $denda_tunggakan->count() > 0)

@php
$dendaRows = [];
foreach ($denda_tunggakan as $kode => $dendaGroup) {
    $groupItems = collect($dendaGroup)->values();
    $groupCount = $groupItems->count();
    $firstDenda = $groupItems->first();
    $tglGroup   = \Carbon\Carbon::parse($firstDenda->penyewaan->tgl_mulai)->format('d M Y');

    foreach ($groupItems as $pos => $denda) {
        $dendaRows[] = [
            'isCatatan' => false,
            'denda'     => $denda,
            'isFirst'   => $pos === 0,
            'rowspan'   => $groupCount,
            'tglGroup'  => $tglGroup,
        ];
        if ($denda->keterangan_kerusakan) {
            $dendaRows[] = [
                'isCatatan'     => true,
                'keterangan'    => $denda->keterangan_kerusakan,
                'namaFasilitas' => $denda->penyewaan->fasilitas->nama_fasilitas,
            ];
        }
    }
}
@endphp

<div class="card-custom card-denda">
    <h4 class="text-danger fw-bold mb-3">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Tagihan Denda Perlu Dibayar
    </h4>

    <div class="table-responsive">
        <table class="table table-bordered bg-white" style="min-width: 700px;">
            <thead class="table-danger">
                <tr>
                    <th>Tanggal Sewa</th>
                    <th>Fasilitas</th>
                    <th>Denda Telat</th>
                    <th>Denda Rusak</th>
                    <th>Total Denda</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dendaRows as $dr)

                    @if($dr['isCatatan'])
                    <tr>
                        <td colspan="6" class="text-start bg-light small px-3 py-2">
                            <strong>Catatan Admin ({{ $dr['namaFasilitas'] }}):</strong>
                            {{ $dr['keterangan'] }}
                        </td>
                    </tr>

                    @else
                    @php $denda = $dr['denda']; @endphp
                    <tr>

                        @if($dr['isFirst'])
                        <td rowspan="{{ $dr['rowspan'] }}" class="text-center align-middle">
                            <span class="badge-tanggal">{{ $dr['tglGroup'] }}</span>
                        </td>
                        @endif

                        <td class="fw-bold text-start">
                            {{ $denda->penyewaan->fasilitas->nama_fasilitas }}
                        </td>
                        <td class="text-center">
                            Rp {{ number_format($denda->biaya_keterlambatan, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            Rp {{ number_format($denda->biaya_kerusakan, 0, ',', '.') }}
                        </td>
                        <td class="text-danger fw-bold text-center">
                            Rp {{ number_format($denda->total_denda, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if(str_starts_with((string) $denda->kode_pembayaran, 'TUNAI-REQ-'))
                                <span class="btn btn-secondary" style="opacity:.8; cursor:not-allowed;">
                                    Menunggu Verifikasi Tunai
                                </span>
                            @else
                                <a href="{{ route('user.pengembalian.bayar', $denda->id_denda) }}"
                                   class="btn btn-bayar-denda">
                                    Bayar Denda
                                </a>
                            @endif
                        </td>

                    </tr>
                    @endif

                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif


{{-- ========================= TABEL PENGEMBALIAN ========================= --}}
<div class="card-custom">
    <div class="table-responsive">
        <table class="table-pengembalian">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Sewa</th>
                    <th>Nama Fasilitas</th>
                    <th>Pilih</th>
                    <th>Upload</th>
                    <th>Sisa Bayar</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                    <th>Keterangan/Aksi</th>
                </tr>
            </thead>
            <tbody>

                @php
                /**
                 * Flatten $data (grouped by kode_booking) menjadi array $rows.
                 * Setiap row sudah membawa semua info yang dibutuhkan tabel,
                 * termasuk metadata group (isFirst, rowspan, formId, dll).
                 * Hasilnya: 1 loop flat di Blade, tanpa nested foreach sama sekali.
                 */
                $rows = [];
                $no   = 1;
                $gi   = 0; // group index untuk formId unik

                foreach ($data as $kodeBooking => $items) {
                    $itemsFlat      = $items->values();
                    $itemCount      = $itemsFlat->count();
                    $firstItem      = $itemsFlat->first();
                    $formId         = 'form-' . $gi++;
                    $totalSisa      = $itemsFlat->sum('sisa_pembayaran');
                    $groupBisaAjukan= $itemsFlat->every(
                        fn($i) => $i->sisa_pembayaran <= 0 && $i->sudah_boleh_kembali
                    );

                    foreach ($itemsFlat as $pos => $item) {
                        $rows[] = [
                            // data item
                            'item'            => $item,
                            // info group — hanya dipakai kalau isFirst = true
                            'isFirst'         => $pos === 0,
                            'rowspan'         => $itemCount,
                            'no'              => $pos === 0 ? $no : null,
                            'formId'          => $formId,
                            'totalSisa'       => $totalSisa,
                            'firstItemId'     => $firstItem->id_penyewaan,
                            'tglMulai'        => $firstItem->tgl_mulai,
                            'groupBisaAjukan' => $groupBisaAjukan,
                        ];
                    }
                    $no++;
                }
                @endphp

                {{-- Render form tersembunyi (1 per group) di luar tabel --}}
                @foreach(collect($rows)->where('isFirst', true) as $groupRow)
                <form id="{{ $groupRow['formId'] }}"
                      action="{{ route('user.pengembalian.store') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="form-pengembalian"
                      data-form-id="{{ $groupRow['formId'] }}">
                    @csrf
                    <input type="hidden" name="tanggal_group" value="{{ $groupRow['tglMulai'] }}">
                </form>
                @endforeach

                @if(count($rows) === 0)
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Tidak ada data pengembalian.
                    </td>
                </tr>
                @endif

                @foreach($rows as $row)
                @php
                    $item            = $row['item'];
                    $isFirst         = $row['isFirst'];
                    $rowspan         = $row['rowspan'];
                    $formId          = $row['formId'];
                    $totalSisa       = $row['totalSisa'];
                    $groupBisaAjukan = $row['groupBisaAjukan'];
                @endphp
                <tr>

                    {{-- No --}}
                    @if($isFirst)
                    <td class="td-no" rowspan="{{ $rowspan }}">{{ $row['no'] }}</td>
                    @endif

                    {{-- Tanggal Sewa --}}
                    @if($isFirst)
                    <td rowspan="{{ $rowspan }}">
                        <span class="badge-tanggal">
                            {{ \Carbon\Carbon::parse($row['tglMulai'])->format('d M Y') }}
                        </span>
                    </td>
                    @endif

                    {{-- Nama Fasilitas --}}
                    <td class="td-fasilitas">{{ $item->fasilitas->nama_fasilitas }}</td>

                    {{-- Pilih --}}
                    <td>
                        @if($item->sisa_pembayaran <= 0 && $item->sudah_boleh_kembali)
                            <input type="checkbox"
                                   name="id_penyewaan[]"
                                   value="{{ $item->id_penyewaan }}"
                                   form="{{ $formId }}"
                                   class="form-check-input-custom check-item-{{ $formId }}">
                        @else
                            <input type="checkbox" class="form-check-input-custom" disabled>
                        @endif
                    </td>

                    {{-- Upload --}}
                    <td>
                        @if($item->sisa_pembayaran > 0)
                            <span class="text-selesaikan">Selesaikan Pembayaran</span>
                        @elseif(!$item->sudah_boleh_kembali)
                            <span class="text-selesaikan">Belum waktunya</span>
                        @else
                            <input type="file"
                                   name="bukti_pengembalian[{{ $item->id_penyewaan }}]"
                                   form="{{ $formId }}"
                                   class="form-control form-control-sm input-upload"
                                   accept="image/jpeg,image/png,image/jpg">
                        @endif
                    </td>

                    {{-- Sisa Bayar --}}
                    @if($isFirst)
                    <td class="td-sisa" rowspan="{{ $rowspan }}">
                        Rp. {{ number_format($totalSisa, 0, ',', '.') }}
                    </td>
                    @endif

                    {{-- Pembayaran --}}
                    @if($isFirst)
                    <td rowspan="{{ $rowspan }}">
                        @if($totalSisa > 0)
                            <a href="{{ route('user.pembayaran.index', $row['firstItemId']) }}"
                               class="btn-bayar">Bayar</a>
                        @else
                            <span class="text-terbayar">Terbayar</span>
                        @endif
                    </td>
                    @endif

                    {{-- Status --}}
                    @if($isFirst)
                    <td rowspan="{{ $rowspan }}">
                        @if($totalSisa <= 0)
                            <span class="text-lunas">Lunas</span>
                        @else
                            <span class="text-belum">Belum Lunas</span>
                        @endif
                    </td>
                    @endif

                    {{-- Keterangan/Aksi --}}
                    @if($isFirst)
                    <td rowspan="{{ $rowspan }}">
                        @if($groupBisaAjukan)
                            <button type="submit" form="{{ $formId }}" class="btn-ajukan">
                                Ajukan
                            </button>
                        @else
                            <button class="btn-ajukan-disabled" disabled>Ajukan</button>
                        @endif
                    </td>
                    @endif

                </tr>
                @endforeach

            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrapper mt-3">
        {{ $pagination->links() }}
    </div>
</div>

</div>


{{-- ========================= JS ========================= --}}
<script>
document.querySelectorAll('.form-pengembalian').forEach(form => {
    form.addEventListener('submit', function(e) {
        const formId  = this.dataset.formId;
        const checked = document.querySelectorAll(`.check-item-${formId}:checked`);

        if (checked.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Pilih dulu!',
                text: 'Centang fasilitas yang ingin dikembalikan.'
            });
            return;
        }

        // Pastikan setiap item yang dicentang punya file
        let buktiLengkap = true;
        checked.forEach(cb => {
            const penyewaanId = cb.value;
            const fileInput   = document.querySelector(
                `input[name="bukti_pengembalian[${penyewaanId}]"]`
            );
            if (!fileInput || !fileInput.files.length) {
                buktiLengkap = false;
            }
        });

        if (!buktiLengkap) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Upload dulu!',
                text: 'Mohon upload bukti pengembalian untuk setiap fasilitas yang dipilih.'
            });
            return;
        }

        Swal.fire({
            title: 'Mengirim...',
            text: 'Proses pengajuan sedang berjalan',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    });
});
</script>

@if(session('success'))
<script>
Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}" });
</script>
@endif

@if(session('error'))
<script>
Swal.fire({ icon: 'error', title: 'Gagal', text: "{{ session('error') }}" });
</script>
@endif

@endsection
