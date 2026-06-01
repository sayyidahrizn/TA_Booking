@extends('admin.layout')

@section('title', 'Laporan Pemasukan Denda')
@section('page-title', 'Laporan Pemasukan Denda')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <h3 style="margin: 0; font-size: 18px; color: #1e293b;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444; margin-right: 8px;"></i> 
            Data Pemasukan Denda ({{ $periodeTeks }})
        </h3>
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <form method="GET" action="{{ route('admin.laporan.denda') }}" style="display: flex; gap: 10px; align-items: center;">
                <input type="date" name="start_date" value="{{ $startDate }}" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                <input type="date" name="end_date" value="{{ $endDate }}" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                <button type="submit" style="background-color: #111827; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer;">Filter</button>
                @if($startDate || $endDate)
                    <a href="{{ route('admin.laporan.denda') }}" style="background-color: #64748b; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px;">Reset</a>
                @endif
            </form>
            <a href="{{ route('admin.laporan.pdf', request()->query()) }}" target="_blank" style="background-color: #ef4444; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px;">PDF</a>
            <a href="{{ route('admin.laporan.excel', request()->query()) }}" style="background-color: #10b981; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px;">EXC</a>
        </div>
    </div>

    <div style="background-color: #f8fafc; border-left: 4px solid #ef4444; padding: 15px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
        <span style="color: #64748b; font-size: 13px;">Total Pemasukan Denda</span><br>
        <strong style="font-size: 24px; color: #ef4444;">Rp {{ number_format($totalDenda, 0, ',', '.') }}</strong>
    </div>

    <div style="overflow-x: auto; background: white; border-radius: 8px;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px 15px;">No</th>
                    <th style="padding: 12px 15px;">Kode Booking</th>
                    <th style="padding: 12px 15px;">Penyewa</th>
                    <th style="padding: 12px 15px;">Kondisi Barang</th>
                    <th style="padding: 12px 15px;">Alasan / Kerusakan</th>
                    <th style="padding: 12px 15px;">Status</th>
                    <th style="padding: 12px 15px;">Tanggal</th>
                    <th style="padding: 12px 15px;">Total Denda</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailLaporan as $key => $item)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 12px 15px;">{{ $detailLaporan->firstItem() + $key }}</td>
                        <td style="padding: 12px 15px; font-weight: bold; color: #2563eb;">{{ $item->kode_booking }}</td>
                        <td style="padding: 12px 15px;">{{ $item->user->name ?? '-' }}</td>
                        <td style="padding: 12px 15px;">
                            @foreach($item->denda as $d)
                                <span style="display:block; margin-bottom:2px; font-size:11px; background:#fee2e2; color:#991b1b; padding:2px 5px; border-radius:4px;">
                                    {{ $d->jenis_kerusakan ?? 'Denda' }}
                                </span>
                            @endforeach
                        </td>
                        <td style="padding: 12px 15px;">
                            @foreach($item->denda as $d)
                                <small style="display:block; color:#475569;">• {{ $d->keterangan_kerusakan ?? '-' }}</small>
                            @endforeach
                        </td>
                        <td style="padding: 12px 15px;">
                            @php $isBelum = $item->denda->contains('status_denda', 'belum_bayar'); @endphp
                            @if(!$isBelum)
                                <span style="color:#065f46; font-weight:bold; font-size:11px;">LUNAS</span>
                            @else
                                <span style="color:#92400e; font-weight:bold; font-size:11px;">ADA TUNGGAKAN</span>
                            @endif
                        </td>
                        <td style="padding: 12px 15px;">{{ $item->created_at->format('d/m/Y') }}</td>
                        <td style="padding: 12px 15px; font-weight: bold; color: #ef4444;">
                            Rp {{ number_format($item->denda->sum('total_denda'), 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="padding: 40px; text-align: center;">Data tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 25px; display: flex; justify-content: center;">
        {!! $detailLaporan->appends(request()->query())->links() !!}
    </div>
</div>

<style>
    .pagination-wrapper svg { width: 20px; }
    nav div:first-child { display: none; }
    .pagination { display: flex; gap: 5px; list-style: none; }
    .page-link { padding: 8px 12px; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 4px; }
    .active .page-link { background: #111827; color: white; }
</style>
@endsection