@extends('admin.layout')

@section('title', 'Laporan Pemasukan Penyewaan')
@section('page-title', 'Laporan Pemasukan Penyewaan')

@section('content')
<style>
    /* Mengatasi tampilan list vertikal (bullet points) pada pagination */
    .pagination-custom {
        margin-top: 30px;
        display: flex;
        justify-content: center;
    }
    
    .pagination-custom nav ul {
        display: flex;
        list-style: none;
        padding: 0;
        gap: 5px;
    }

    .pagination-custom nav ul li {
        display: inline-block;
    }

    .pagination-custom nav ul li a, 
    .pagination-custom nav ul li span {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        text-decoration: none;
        background-color: #fff;
        border: 1px solid #dee2e6;
        color: #2563eb;
        border-radius: 4px;
        transition: all 0.2s;
        min-width: 40px;
    }

    .pagination-custom nav ul li a:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
    }

    .pagination-custom nav ul li.active span {
        background-color: #2563eb;
        color: white;
        border-color: #2563eb;
    }

    .pagination-custom nav ul li.disabled span {
        color: #94a3b8;
        background-color: #f8fafc;
        cursor: not-allowed;
    }

    /* Merapikan panah SVG agar tidak raksasa */
    .pagination-custom svg {
        width: 16px;
        height: 16px;
    }
</style>

<div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <h3 style="margin: 0; font-size: 18px; color: #1e293b;">
            <i class="fa-solid fa-wallet" style="color: #38bdf8; margin-right: 8px;"></i> 
            Data Pemasukan Penyewaan Fasilitas ({{ $periodeTeks }})
        </h3>
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <form method="GET" action="{{ route('admin.laporan.sewa') }}" style="display: flex; gap: 10px; align-items: center;">
                <input type="date" name="start_date" value="{{ $startDate }}" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;">
                <span style="color: #64748b;">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate }}" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;">
                <button type="submit" style="background-color: #111827; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if($startDate || $endDate)
                    <a href="{{ route('admin.laporan.sewa') }}" style="background-color: #64748b; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold;">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.laporan.pdf', request()->query()) }}" style="background-color: #ef4444; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold;">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('admin.laporan.excel', request()->query()) }}" style="background-color: #10b981; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold;">
                <i class="fa-solid fa-file-excel"></i> EXC
            </a>
        </div>
    </div>

    <div style="background-color: #f8fafc; border-left: 4px solid #10b981; padding: 15px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
        <span style="color: #64748b; font-size: 13px; display: block; margin-bottom: 5px;">Total Pendapatan Sewa</span>
        <strong style="font-size: 24px; color: #10b981;">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</strong>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px 15px; color: #475569;">No</th>
                    <th style="padding: 12px 15px; color: #475569;">Kode Booking</th>
                    <th style="padding: 12px 15px; color: #475569;">Nama Penyewa</th>
                    <th style="padding: 12px 15px; color: #475569;">Fasilitas</th>
                    <th style="padding: 12px 15px; color: #475569;">Jumlah Unit</th>
                    <th style="padding: 12px 15px; color: #475569;">Status Sewa</th>
                    <th style="padding: 12px 15px; color: #475569;">Tanggal Transaksi</th>
                    <th style="padding: 12px 15px; color: #475569;">Total Bayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailLaporan as $key => $item)
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px 15px;">{{ $detailLaporan->firstItem() + $key }}</td>
                    <td style="padding: 12px 15px; font-weight: bold; color: #2563eb;">{{ $item->kode_booking }}</td>
                    <td style="padding: 12px 15px;">{{ $item->user->name ?? '-' }}</td>
                    <td style="padding: 12px 15px;">{{ $item->fasilitas->nama_fasilitas ?? '-' }}</td>
                    <td style="padding: 12px 15px;">{{ $item->jumlah_sewa }}</td>
                    <td style="padding: 12px 15px;">
                        <span style="padding: 3px 8px; border-radius: 12px; font-size: 12px; background-color: #dbeafe; color: #1e40af;">
                            {{ ucfirst($item->status_sewa) }}
                        </span>
                    </td>
                    <td style="padding: 12px 15px;">{{ $item->created_at->translatedFormat('d M Y') }}</td>
                    <td style="padding: 12px 15px; font-weight: bold; color: #1e293b;">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding: 30px; text-align: center; color: #94a3b8;">Belum ada data pemasukan sewa pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-custom">
        {{ $detailLaporan->appends(request()->query())->links() }}
    </div>
</div>
@endsection