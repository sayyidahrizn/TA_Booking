@extends('admin.layout') <!-- Sesuaikan nama layout utama Anda -->

@section('title', 'Laporan Pemasukan Denda')
@section('page-title', 'Laporan Pemasukan Denda')

@section('content')
<div class="card">
    <!-- Bagian Atas / Fitur Kontrol -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <h3 style="margin: 0; font-size: 18px; color: #1e293b;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444; margin-right: 8px;"></i> 
            Data Pemasukan Denda ({{ $periodeTeks }})
        </h3>
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <!-- Form Filter Tanggal -->
            <form method="GET" action="{{ route('admin.laporan.denda') }}" style="display: flex; gap: 10px; align-items: center;">
                <input type="date" name="start_date" value="{{ $startDate }}" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;">
                <span style="color: #64748b;">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate }}" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;">
                <button type="submit" style="background-color: #111827; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if($startDate || $endDate)
                    <a href="{{ route('admin.laporan.denda') }}" style="background-color: #64748b; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold;">Reset</a>
                @endif
            </form>

            <!-- Tombol Ekspor -->
            <a href="{{ route('admin.laporan.pdf', request()->query()) }}" style="background-color: #ef4444; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold;">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('admin.laporan.excel', request()->query()) }}" style="background-color: #10b981; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold;">
                <i class="fa-solid fa-file-excel"></i> EXC
            </a>
        </div>
    </div>

    <!-- Ringkasan Total Denda -->
    <div style="background-color: #f8fafc; border-left: 4px solid #ef4444; padding: 15px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
        <span style="color: #64748b; font-size: 13px; display: block; margin-bottom: 5px;">Total Pemasukan Denda Masuk</span>
        <strong style="font-size: 24px; color: #ef4444;">Rp {{ number_format($totalDenda, 0, ',', '.') }}</strong>
    </div>

    <!-- Tabel Data Transaksi -->
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px 15px; color: #475569;">No</th>
                    <th style="padding: 12px 15px; color: #475569;">Kode Booking</th>
                    <th style="padding: 12px 15px; color: #475569;">Nama Penyewa</th>
                    <th style="padding: 12px 15px; color: #475569;">Kondisi Barang</th>
                    <th style="padding: 12px 15px; color: #475569;">Alasan & Catatan</th>
                    <th style="padding: 12px 15px; color: #475569;">Status Pembayaran</th>
                    <th style="padding: 12px 15px; color: #475569;">Tanggal</th>
                    <th style="padding: 12px 15px; color: #475569;">Jumlah Denda</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailLaporan as $key => $item)
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px 15px;">{{ $detailLaporan->firstItem() + $key }}</td>
                    <td style="padding: 12px 15px; font-weight: bold; color: #2563eb;">{{ $item->kode_booking }}</td>
                    <td style="padding: 12px 15px;">{{ $item->user->name ?? '-' }}</td>
                    <td style="padding: 12px 15px;">
                        <span style="padding: 3px 8px; border-radius: 12px; font-size: 12px; background-color: #fee2e2; color: #991b1b;">
                            {{ ucfirst($item->denda->kondisi_barang ?? '-') }}
                        </span>
                    </td>
                    <td style="padding: 12px 15px;">
                        <strong style="font-size: 13px; color: #1e293b;">{{ $item->denda->alasan_denda ?? '-' }}</strong>
                        <small style="display: block; color: #64748b;">Ket: {{ $item->denda->catatan ?? '-' }}</small>
                    </td>
                    <td style="padding: 12px 15px;">
                        @if(($item->denda->status_pembayaran ?? '') == 'lunas')
                            <span style="padding: 3px 8px; border-radius: 12px; font-size: 12px; background-color: #d1fae5; color: #065f46;">Lunas</span>
                        @else
                            <span style="padding: 3px 8px; border-radius: 12px; font-size: 12px; background-color: #fef3c7; color: #92400e;">Belum Lunas</span>
                        @endif
                    </td>
                    <td style="padding: 12px 15px;">{{ $item->created_at->translatedFormat('d-m-Y') }}</td>
                    <td style="padding: 12px 15px; font-weight: bold; color: #ef4444;">Rp {{ number_format($item->denda->jumlah_denda ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding: 30px; text-align: center; color: #94a3b8;">Belum ada data pemasukan denda pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Link -->
    <div style="margin-top: 20px;">
        {{ $detailLaporan->links() }}
    </div>
</div>
@endsection