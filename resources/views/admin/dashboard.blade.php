@extends('admin.layout')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .grid-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .grid-container .card-stat {
        flex: 1;
        min-width: 200px;
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        justify-content: center;
        border-left: 5px solid #ececec;
    }
    .stat-title {
        color: #6b7280;
        margin-bottom: 8px;
        font-weight: bold;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-value {
        font-size: 22px;
        font-weight: 800;
        margin: 0;
        color: #111827;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }
    .chart-card {
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }
    .table-custom th, .table-custom td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #f3f4f6;
    }
    .table-custom th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 13px;
    }
    .badge-fasilitas {
        background: #f1f5f9;
        color: #475569;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        margin-right: 4px;
        border: 1px solid #e2e8f0;
        display: inline-block;
        margin-bottom: 2px;
    }
    .status-pill {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
        color: white;
        text-transform: uppercase;
        display: inline-block;
    }
    .proses { background: #f59e0b; }
    .disetujui { background: #10b981; }
    .selesai { background: #3b82f6; }
    .batal { background: #ef4444; }
</style>

<div class="grid-container">
    <div class="card-stat" style="border-left-color: #3b82f6;">
        <div class="stat-title">Total Pendapatan</div>
        <div class="stat-value" style="color: #1e3a8a;">
            Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}
        </div>
    </div>

    <div class="card-stat" style="border-left-color: #10b981;">
        <div class="stat-title">Total Fasilitas</div>
        <div class="stat-value">{{ $totalFasilitas ?? 0 }}</div>
    </div>

    <div class="card-stat" style="border-left-color: #8b5cf6;">
        <div class="stat-title">Fasilitas Kembali</div>
        <div class="stat-value" style="color: #6d28d9;">{{ $totalKembali ?? 0 }}</div>
    </div>

    <div class="card-stat" style="border-left-color: #6366f1;">
        <div class="stat-title">Total Penyewaan</div>
        <div class="stat-value">{{ $totalPenyewaan ?? 0 }}</div>
    </div>

    <div class="card-stat" style="border-left-color: #f59e0b;">
        <div class="stat-title">Cek Booking (Pending)</div>
        <div class="stat-value" style="color: #f59e0b;">{{ $pending ?? 0 }}</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <h3 style="margin: 0 0 20px 0; font-size: 15px; color: #1f2937;">Ikhtisar Pendapatan (Bulanan)</h3>
        <div style="height: 300px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h3 style="margin: 0 0 20px 0; font-size: 15px; color: #1f2937;">Status Transaksi</h3>
        <div style="height: 300px;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="card" style="background: white; border-radius: 12px; padding: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <div style="padding: 20px;">
        <h3 style="margin: 0; font-size: 15px; color: #1f2937;">Penyewaan Terbaru</h3>
    </div>
    <table class="table-custom">
        <thead>
            <tr>
                <th style="width: 50px; text-align: center;">No</th>
                <th>Penyewa</th>
                <th>Fasilitas</th>
                <th>Tanggal Mulai</th>
                <th style="text-align: center;">Status Fasilitas</th>
                <th style="text-align: center;">Pembayaran</th>
                <th style="text-align: center;">Pengembalian</th> 
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
                    <td style="text-align: center; font-weight: bold; color: #64748b;">{{ $loop->iteration }}</td>
                    <td>
                        <strong style="color: #1e293b; font-size: 14px;">{{ $p->user->name ?? $p->nama_penyewa }}</strong><br>
                        <small style="color: #94a3b8;">NIK: {{ $p->nik ?? '-' }}</small>
                    </td>
                    <td>
                        @foreach($group as $item)
                            <span class="badge-fasilitas">{{ $item->fasilitas->nama_fasilitas ?? '-' }}</span>
                        @endforeach
                    </td>
                    <td style="color: #475569; font-size: 13px;">{{ \Carbon\Carbon::parse($p->tgl_mulai)->format('d M Y') }}</td>
                    
                    <td style="text-align: center;">
                        <span class="status-pill {{ $status }}">
                            {{ $status == 'proses' ? 'PERLU CEK' : $status }}
                        </span>
                    </td>

                    <td style="text-align: center;">
                        @if($statusBayar == 'lunas')
                            <span style="color: #10b981; font-weight: 800; font-size: 11px;">
                                <i class="fas fa-check-circle"></i> LUNAS
                            </span>
                        @else
                            <span style="color: #ef4444; font-weight: 800; font-size: 11px;">
                                <i class="fas fa-clock"></i> PENDING
                            </span>
                        @endif
                    </td>

                    <td style="text-align: center;">
                        {{-- Logika: Jika status sudah 'selesai' ATAU user sudah mengirim data pengembalian --}}
                        @if($status == 'selesai' || $p->pengembalian)
                            <span style="color: #3b82f6; font-weight: 800; font-size: 11px;">
                                <i class="fas fa-undo-alt"></i> DIKEMBALIKAN
                            </span>
                        @else
                            <span style="color: #94a3b8; font-weight: 800; font-size: 11px;">
                                <i class="fas fa-history"></i> BELUM
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">Belum ada data penyewaan terbaru.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

    const dataPendapatan = {!! json_encode($dataGrafik ?? []) !!};
    const namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const labelBulan = namaBulan.slice(0, dataPendapatan.length);

    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: labelBulan, 
            datasets: [{
                label: 'Pendapatan',
                data: dataPendapatan,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#3b82f6',
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + value.toLocaleString('id-ID') } },
                x: { grid: { display: false } }
            }
        }
    });

    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Selesai/Disetujui', 'Pending/Proses'],
            datasets: [{
                data: [{{ ($totalPenyewaan ?? 0) - ($pending ?? 0) }}, {{ $pending ?? 0 }}],
                backgroundColor: ['#10b981', '#f59e0b'],
                borderWidth: 0,
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endsection