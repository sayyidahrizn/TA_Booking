@extends('admin.layout')

@section('title', 'Laporan Dashboard')
@section('page-title', 'Laporan Sistem')

@section('content')
<style>
    /* Grid Statistik Responsif */
    .report-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        border-top: 4px solid #2563eb;
    }

    .stat-card h4 { margin: 10px 0 5px 0; font-size: 18px; color: #1e293b; }
    .stat-card span { font-size: 11px; color: #64748b; font-weight: bold; text-transform: uppercase; }

    /* UI Filter Tanggal Modern */
    .filter-wrapper {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 20px;
    }

    .filter-inputs {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .input-group-custom {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .input-group-custom label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
    }

    .input-date {
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
        color: #1e293b;
        outline: none;
        transition: 0.2s;
    }

    .input-date:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }

    .btn-filter {
        background: #2563eb;
        color: white;
        border: none;
        padding: 9px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-filter:hover { background: #1d4ed8; }

    /* Layout Utama */
    .main-row {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 25px;
    }

    .card-box {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    /* Table Styling */
    .table-custom { width: 100%; border-collapse: collapse; }
    .table-custom tr { border-bottom: 1px solid #f1f5f9; }
    .table-custom td { padding: 15px 5px; font-size: 14px; }

    @media (max-width: 1024px) {
        .report-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .report-grid { grid-template-columns: repeat(2, 1fr); }
        .main-row { grid-template-columns: 1fr; }
        .filter-wrapper { flex-direction: column; align-items: stretch; }
    }
</style>

<div class="filter-wrapper">
    <form action="{{ route('admin.laporan') }}" method="GET" class="filter-inputs">
        <div class="input-group-custom">
            <label>MULAI TANGGAL</label>
            <input type="date" name="start_date" class="input-date" value="{{ request('start_date') }}">
        </div>
        <div class="input-group-custom">
            <label>SAMPAI TANGGAL</label>
            <input type="date" name="end_date" class="input-date" value="{{ request('end_date') }}">
        </div>
        <button type="submit" class="btn-filter">Filter</button>
        @if(request('start_date'))
            <a href="{{ route('admin.laporan') }}" style="color: #ef4444; font-size: 13px; text-decoration: none; font-weight: 600;">Reset</a>
        @endif
    </form>

    <div style="display: flex; gap: 8px;">
        <a href="{{ route('admin.laporan.pdf', request()->all()) }}" style="background:#ef4444; color:white; padding: 8px 16px; border-radius: 6px; text-decoration:none; font-weight:bold; font-size:12px;">📥 PDF</a>
        <a href="{{ route('admin.laporan.excel', request()->all()) }}" style="background:#10b981; color:white; padding: 8px 16px; border-radius: 6px; text-decoration:none; font-weight:bold; font-size:12px;">📊 EXCEL</a>
    </div>
</div>

<div class="report-grid">
    <div class="stat-card" style="border-top-color: #2563eb;">
        <span>Total Pendapatan</span>
        <h4>Rp {{ number_format($totalUang + $totalDenda, 0, ',', '.') }}</h4>
    </div>
    <div class="stat-card" style="border-top-color: #10b981;">
        <span>Sewa Lunas</span>
        <h4>{{ $sudahBayar }}</h4>
    </div>
    <div class="stat-card" style="border-top-color: #f59e0b;">
        <span>Sewa Pending</span>
        <h4>{{ $belumBayar }}</h4>
    </div>
    <div class="stat-card" style="border-top-color: #ef4444;">
        <span>Sewa Ditolak</span>
        <h4>{{ $ditolak }}</h4>
    </div>
    <div class="stat-card" style="border-top-color: #6366f1;">
        <span>Total Penyewa</span>
        <h4>{{ $totalPenyewa }}</h4>
    </div>
</div>

<div class="main-row">
    <div class="card-box">
        <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 20px; color: #1e293b;">Status Sewa</h3>
        <div style="height: 300px;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <div class="card-box">
        <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 20px; color: #1e293b;">
            <i class=></i> Fasilitas Yang Sering Disewa
        </h3>
        <table class="table-custom">
            @forelse($fasilitasTerlaris as $f)
            <tr>
                <td style="font-weight: 600; color: #334155; padding: 15px 0;">
                    {{ $f->nama_fasilitas }}
                </td>
                <td style="text-align: right; font-weight: 800; color: #2563eb;">
                    {{ $f->total }}x Sewa
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2" style="text-align: center; color: #94a3b8; padding: 20px;">
                    Data tidak ditemukan
                </td>
            </tr>
            @endforelse
        </table>
        
        <div style="margin-top: 30px; display: flex; justify-content: space-between; font-size: 12px; color: #64748b; font-weight: bold; border-top: 1px dashed #e2e8f0; padding-top: 15px;">
            <span>Stok Tersedia: <span style="color: #10b981">{{ $fasilitasTersedia }}</span></span>
            <span>Belum Kembali: <span style="color: #ef4444">{{ $belumKembali }}</span></span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Lunas', 'Pending', 'Batal'],
            datasets: [{
                data: [{{ $sudahBayar }}, {{ $belumBayar }}, {{ $ditolak }}],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 20, font: { weight: 'bold' } } }
            }
        }
    });
</script>
@endsection

