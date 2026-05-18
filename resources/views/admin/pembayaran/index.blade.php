@extends('admin.layout') 

{{-- Mengisi judul di header layout secara otomatis --}}
@section('page-title', 'Kelola Pembayaran') 

@section('content')

<style>
    /* Dasar & Reset */
    body { 
        font-family: 'Inter', Arial, sans-serif; 
        background-color: #f8fafc;
    }

    /* HEADER & FILTER STYLE */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }

    .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .filter-form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-form .form-control,
    .filter-form select {
        min-width: 200px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        font-size: 13px;
        height: 38px;
        transition: all 0.2s;
    }

    .filter-form .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .btn-filter {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        height: 38px;
        cursor: pointer;
    }

    .btn-reset {
        background: #64748b;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        height: 38px;
        display: flex;
        align-items: center;
    }

    /* CARD & TABLE STYLE */
    .card-main { 
        background-color: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    
    .table thead th { 
        background-color: #f8fafc; 
        color: #64748b; 
        text-transform: uppercase; 
        font-size: 11px; 
        font-weight: 700;
        letter-spacing: 0.5px; 
        padding: 18px 15px;
        border-bottom: 2px solid #f1f5f9;
        vertical-align: middle;
        white-space: nowrap;
    }
    
    .table tbody td { 
        vertical-align: middle; 
        padding: 16px 15px; 
        border-color: #f1f5f9; 
        color: #334155; 
        font-size: 13px; 
    }

    .table tbody tr:hover { background: #f1f5f9; }

    /* FASILITAS ITEM STYLE */
    .fasilitas-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .dot {
        height: 7px; width: 7px;
        background-color: #10b981; 
        border-radius: 50%;
        flex-shrink: 0;
    }

    .qty-badge {
        font-weight: 700;
        color: #1e40af;
        background-color: #eff6ff; 
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        border: 1px solid rgba(30, 64, 175, 0.1);
    }

    /* STATUS BADGE STYLE */
    .status-badge {
        font-size: 10px;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 50px;
        display: inline-block;
        text-align: center;
        min-width: 85px;
        text-transform: uppercase;
    }

    .status-lunas { color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0; }
    .status-pending { color: #92400e; background: #fef3c7; border: 1px solid #fde68a; }
    .status-batal { color: #991b1b; background: #fee2e2; border: 1px solid #fecaca; }

    .text-primary-custom { color: #2563eb; }
    .table-responsive { overflow-x: auto; }

    /* PAGINATION STYLE (Sesuai Halaman Fasilitas) */
    .fsl-pagination-wrapper { 
        margin-top: 25px; 
        display: flex !important; 
        justify-content: space-between !important; /* Paksa Kiri-Kanan */
        align-items: center !important; 
        width: 100%;
        padding: 0 5px;
    }

    .fsl-pagination-info { 
        font-size: 13px; 
        color: #64748b; 
    }

    /* Reset default Bootstrap pagination */
    .fsl-pagination-nav .pagination {
        display: flex !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 5px !important;
    }

    .fsl-pagination-nav .page-item {
        margin: 0 !important;
    }

    .fsl-pagination-nav .page-link {
        width: 40px !important;
        height: 40px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-decoration: none !important;
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        color: #2563eb !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
        padding: 0 !important;
    }

    .fsl-pagination-nav .page-item.active .page-link {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        box-shadow: 0 4px 10px rgba(37,99,235,0.2) !important;
    }

    .fsl-pagination-nav .page-item.disabled .page-link {
        background-color: #f8fafc !important;
        color: #cbd5e1 !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    
    /* Menghilangkan panah bawaan jika perlu atau merapikannya */
    .fsl-pagination-nav .page-link:hover:not(.active):not(.disabled) {
        background-color: #f1f5f9 !important;
    }
    /* Mengecilkan input group agar pas di dalam baris tabel */
    .input-group-sm > .form-control {
        font-size: 12px;
        height: 32px;
    }
    /* Style Baru untuk Input Nominal di Tabel */
    .action-input-wrapper {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 2px 4px;
        transition: all 0.3s ease;
        width: 180px; /* Ukuran pas untuk kolom aksi */
        margin: 0 auto;
    }

    .action-input-wrapper:focus-within {
        border-color: #10b981; /* Hijau agar senada dengan tombol simpan */
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .currency-symbol {
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        padding-left: 8px;
        user-select: none;
    }

    .nominal-input {
        border: none !important;
        background: transparent !important;
        font-size: 12px !important;
        font-weight: 600;
        color: #334155;
        padding: 6px 8px !important;
        width: 100%;
        outline: none !important;
        box-shadow: none !important;
    }

    .btn-save-nominal {
        background-color: #10b981;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .btn-save-nominal:hover {
        background-color: #059669;
    }

    /* Hilangkan panah up/down di input number */
    .nominal-input::-webkit-outer-spin-button,
    .nominal-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .btn-success.btn-sm {
        padding: 2px 10px;
        font-size: 11px;
    }
</style>

<div class="container-fluid py-4">
    {{-- HEADER --}}
    <div class="page-header">
        <h4 class="page-title"></h4>

        {{-- SEARCH & FILTER --}}
        <form method="GET" action="" class="filter-form">
            <input type="text" name="search" class="form-control" placeholder="Cari Nama atau NIK..." value="{{ request('search') }}">

            <select name="status">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
            </select>

            <button type="submit" class="btn-filter">Filter</button>
            <a href="{{ url()->current() }}" class="btn-reset">Reset</a>
        </form>
    </div>

    {{-- CARD TABEL --}}
    <div class="card card-main">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">NO</th>
                        <th class="text-center">TANGGAL</th>
                        <th>PENYEWA</th>
                        <th>FASILITAS</th>
                        <th class="text-end">TOTAL TAGIHAN</th>
                        <th class="text-end">PEMBAYARAN</th>
                        <th class="text-end">SISA TAGIHAN</th>
                        <th class="text-center">STATUS PEMBAYARAN</th>
                        <th class="text-center" width="10%">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayarans as $kode => $items)
                        @php
                            $first = $items->first();
                            $totalTagihan = $items->total_tagihan ?? 0;
                            $totalBayar = $items->total_bayar ?? 0;
                            $sisaTagihan = $items->sisa_tagihan ?? 0;
                            $statusPembayaran = $items->status_custom ?? 'pending';

                            // Ambil data pembayaran yang statusnya masih pending dan tipenya tunai
                            // agar admin bisa melakukan input nominal
                            $pembayaranPending = $items->flatMap->pembayaran
                                                ->where('status_pembayaran', 'pending')
                                                ->where('metode_pembayaran', 'tunai')
                                                ->first();
                        @endphp
                        <tr>
                            <td class="text-center fw-bold text-muted">
                                {{ ($pembayarans->currentPage()-1) * $pembayarans->perPage() + $loop->iteration }}
                            </td>
                            <td class="text-center text-nowrap">
                                <span class="fw-bold text-dark">
                                    {{ \Carbon\Carbon::parse($first->tgl_mulai)->translatedFormat('d M Y') }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $first->nama_penyewa }}</div>
                                <div class="text-muted" style="font-size:11px;">NIK: {{ $first->nik }}</div>
                            </td>
                            <td>
                                @foreach($items as $item)
                                    <div class="fasilitas-item">
                                        <span class="dot"></span>
                                        <span class="qty-badge">{{ $item->jumlah_sewa }}x</span>
                                        <span class="text-capitalize">{{ $item->fasilitas->nama_fasilitas }}</span>
                                    </div>
                                @endforeach
                            </td>
                            <td class="text-end fw-bold text-dark text-nowrap">
                                Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold text-primary-custom text-nowrap">
                                Rp {{ number_format($totalBayar, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold text-nowrap {{ $sisaTagihan > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="status-badge {{ $statusPembayaran == 'lunas' ? 'status-lunas' : 'status-pending' }}">
                                    {{ $statusPembayaran }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($statusPembayaran != 'lunas' && $pembayaranPending)
                                    <form action="{{ route('admin.pembayaran.verifikasi', $pembayaranPending->id_pembayaran) }}" method="POST">
                                        @csrf
                                        <div class="action-input-wrapper">
                                            <span class="currency-symbol">Rp</span>
                                            
                                            <input type="text" 
                                                class="nominal-input" 
                                                placeholder="0" 
                                                onkeyup="formatRupiah(this)"
                                                required>
                                            
                                            <input type="hidden" name="jumlah_bayar" class="raw-nominal">

                                            <button type="submit" class="btn-save-nominal">
                                                <i class="fas fa-check"></i>
                                                <span>Simpan</span>
                                            </button>
                                        </div>
                                    </form>
                                @elseif($statusPembayaran == 'lunas')
                                    <div class="text-success fw-bold" style="font-size: 12px;">
                                        <i class="fas fa-check-circle mr-1"></i> Selesai
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border" style="font-weight: 500; font-size: 11px; padding: 5px 10px;">
                                        <i class="fas fa-external-link-alt mr-1"></i> Midtrans
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <p class="text-muted mb-0 small">Data tidak ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- FOOTER PAGINATION --}}
    <div class="fsl-pagination-wrapper">
        <div class="fsl-pagination-info">
            Menampilkan <strong>{{ $pembayarans->firstItem() ?? 0 }} - {{ $pembayarans->lastItem() ?? 0 }}</strong> dari <strong>{{ $pembayarans->total() }}</strong> data
        </div>

        <div class="fsl-pagination-nav">
            {{ $pembayarans->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<script>
function formatRupiah(element) {
    // 1. Ambil angka saja
    let number_string = element.value.replace(/[^,\d]/g, '').toString();
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    // 2. Tambahkan titik jika ribuan
    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    
    // 3. Cetak ke layar
    element.value = rupiah;

    // 4. Update hidden input (hapus semua titik agar jadi angka murni)
    let rawValue = number_string; 
    element.closest('form').querySelector('.raw-nominal').value = rawValue;
}
</script>

@endsection

