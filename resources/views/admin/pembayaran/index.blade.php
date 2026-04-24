@extends('admin.layout') 

{{-- Mengisi judul di header layout secara otomatis --}}
@section('page-title', 'Kelola Pembayaran') 

@section('content')
<style>
    /* Gunakan font yang sama dengan layout agar konsisten */
    body { font-family: Arial, sans-serif; }

    /* Card Utama untuk Tabel */
    .card-main { 
        background-color: #ffffff; 
        border-radius: 8px; 
        box-shadow: 0 2px 6px rgba(0,0,0,0.1); 
        overflow: hidden;
    }
    
    /* Styling Header Tabel agar Modern */
    .table thead th { 
        background-color: #fcfcfd; 
        color: #64748b; 
        text-transform: uppercase; 
        font-size: 11px; 
        font-weight: 700;
        letter-spacing: 0.5px; 
        padding: 18px 15px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        white-space: nowrap;
    }
    
    .table tbody td { 
        vertical-align: middle; 
        padding: 16px 15px; 
        border-color: #f8fafc; 
        color: #334155; 
        font-size: 13px; 
    }

    /* List Fasilitas + Kotak Jumlah Sewa */
    .fasilitas-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }
    .dot {
        height: 7px;
        width: 7px;
        background-color: #10b981; 
        border-radius: 50%;
        flex-shrink: 0;
    }
    .qty-badge {
        font-weight: 700;
        color: #3b5998;
        background-color: #f0f4ff; 
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
        min-width: 28px;
        text-align: center;
        border: 1px solid rgba(59, 89, 152, 0.1);
    }

    /* Badge Status sesuai database ENUM */
    .status-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 6px;
        display: inline-block;
        text-align: center;
        min-width: 85px;
    }
    .status-lunas { color: #10b981; background: #ecfdf5; border: 1px solid rgba(16, 185, 129, 0.2); }
    .status-pending { color: #f59e0b; background: #fffbeb; border: 1px solid rgba(245, 158, 11, 0.2); }
    .status-batal { color: #ef4444; background: #fef2f2; border: 1px solid rgba(239, 68, 68, 0.2); }

    /* Responsivitas */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .text-nowrap { white-space: nowrap; }
</style>

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
                    <th class="text-end">SISA TAGIHAN</th>
                    <th class="text-center">STATUS PEMBAYARAN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayarans as $kode => $items)
                    @php $first = $items->first(); @endphp
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $loop->iteration }}</td>
                        
                        <td class="text-center text-nowrap">
                            <span class="fw-bold text-dark">
                                {{ \Carbon\Carbon::parse($first->tgl_mulai)->translatedFormat('d M Y') }}
                            </span>
                        </td>

                        <td>
                            <div class="fw-bold text-dark">{{ $first->nama_penyewa }}</div>
                            <div class="text-muted" style="font-size: 11px;">NIK: {{ $first->nik }}</div>
                        </td>

                        <td>
                            @foreach($items as $item)
                                <div class="fasilitas-item">
                                    <span class="dot"></span>
                                    {{-- Menggunakan field jumlah_sewa sesuai database kamu --}}
                                    <span class="qty-badge">{{ $item->jumlah_sewa }}x</span>
                                    <span class="text-capitalize">{{ $item->fasilitas->nama_fasilitas }}</span>
                                </div>
                            @endforeach
                        </td>

                        <td class="text-end fw-bold text-dark text-nowrap">
                            Rp {{ number_format($items->sum('total_harga'), 0, ',', '.') }}
                        </td>

                        <td class="text-end fw-bold text-nowrap {{ $items->sum('sisa_pembayaran') > 0 ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($items->sum('sisa_pembayaran'), 0, ',', '.') }}
                        </td>

                        <td class="text-center">
                            <span class="status-badge status-{{ $first->status_pembayaran }}">
                                {{ strtoupper($first->status_pembayaran) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <p class="text-muted mb-0 small">Belum ada data transaksi pembayaran.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection