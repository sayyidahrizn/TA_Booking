@extends('admin.layout')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')
<style>
    .grid-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    .grid-container .card-stat {
        flex: 1;
        background: #ffffff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .stat-title {
        color: #6b7280;
        margin-bottom: 10px;
        font-weight: bold;
        font-size: 13px;
        text-transform: uppercase;
    }
    .stat-value {
        font-size: 22px;
        font-weight: 800;
        margin: 0;
        color: #111827;
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }
    .table-custom th, .table-custom td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    .table-custom th {
        background: #f8fafc;
        color: #111827;
        font-weight: bold;
    }
    .badge-fasilitas {
        background: #e0e7ff;
        color: #3730a3;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        margin-right: 4px;
        border: 1px solid #c7d2fe;
    }
    .status-pill {
        padding: 5px 15px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: bold;
        color: white;
        text-transform: uppercase;
    }
    /* Warna Status */
    .prosess { background: #f59e0b; } /* Orange untuk proses cek */
    .disetujui { background: #10b981; } /* Hijau untuk disetujui */
    .selesai { background: #3b82f6; } /* Biru untuk selesai */
    .batal { background: #ef4444; } /* Merah untuk batal */
</style>

<div class="grid-container">
    <div class="card-stat" style="border-top: 4px solid #3b82f6;">
        <div class="stat-title">Total Pendapatan</div>
        <div class="stat-value" style="color: #1e3a8a;">
            Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}
        </div>
    </div>

    <div class="card-stat" style="border-top: 4px solid #10b981;">
        <div class="stat-title">Total Fasilitas</div>
        <div class="stat-value">{{ $totalFasilitas ?? 0 }}</div>
    </div>

    <div class="card-stat" style="border-top: 4px solid #6366f1;">
        <div class="stat-title">Total Penyewaan</div>
        <div class="stat-value">{{ $totalPenyewaan ?? 0 }}</div>
    </div>

    <div class="card-stat" style="border-top: 4px solid #f59e0b;">
        <div class="stat-title">Pending</div>
        <div class="stat-value" style="color: #f59e0b;">{{ $pending ?? 0 }}</div>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom: 15px; font-size: 16px;">
        Penyewaan Terbaru
    </h3>
    <table class="table-custom">
        <thead>
            <tr>
                <th style="width: 50px; text-align: center;">No</th>
                <th>Penyewa</th>
                <th>Fasilitas</th>
                <th>Tanggal Mulai</th>
                <th style="text-align: center;">Status Fasilitas</th>
                <th style="text-align: center;">Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penyewaan as $group)
                @php 
                    $p = $group->first(); 
                    $status = strtolower($p->status_sewa);
                    $statusBayar = strtolower($p->status_pembayaran);
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $loop->iteration }}</td>
                    <td>
                        <strong style="color: #111827;">{{ $p->user->name ?? $p->nama_penyewa }}</strong><br>
                        <small style="color: #94a3b8;">NIK: {{ $p->nik ?? '-' }}</small>
                    </td>
                    <td>
                        @foreach($group as $item)
                            <span class="badge-fasilitas">{{ $item->fasilitas->nama_fasilitas ?? '-' }}</span>
                        @endforeach
                    </td>
                    <td style="color: #4b5563;">{{ \Carbon\Carbon::parse($p->tgl_mulai)->format('d/m/Y') }}</td>
                    
                    <td style="text-align: center;">
                        <span class="status-pill {{ $status }}">
                            {{ $status == 'prosess' ? 'PERLU CEK' : $status }}
                        </span>
                    </td>

                    <td style="text-align: center;">
                        @if($statusBayar == 'lunas')
                            <strong style="color: #10b981; font-size: 11px;">LUNAS</strong>
                        @else
                            <strong style="color: #ef4444; font-size: 11px;">PENDING</strong>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #94a3b8;">Belum ada data penyewaan terbaru.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection