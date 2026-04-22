@extends('admin.layout')

@section('title', 'Laporan Ringkasan - Desa Kesamben')

@section('page-title', 'Laporan Ringkasan')

@section('content')
<style>
    /* Baris untuk tombol di atas card */
    .action-row {
        display: flex;
        justify-content: flex-end; /* Memastikan tombol mepet ke kanan */
        margin-bottom: 20px;
    }

    .btn-cetak {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background-color: #059669;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        font-size: 13px;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-decoration: none;
    }

    .btn-cetak:hover { background-color: #047857; }

    /* Desain Card Laporan */
    .card-laporan {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .header-laporan { text-align: center; margin-bottom: 25px; }
    .stats-grid { display: flex; gap: 15px; margin-bottom: 30px; }
    .stat-box { flex: 1; border: 1px solid #e2e8f0; padding: 20px; text-align: center; border-radius: 8px; }
    .table-laporan { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .table-laporan th, .table-laporan td { border: 1px solid #e2e8f0; padding: 12px; text-align: left; }

    /* Tombol Kembali di Bawah */
    .btn-kembali {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background-color: #1f2937;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: bold;
        margin-top: 25px;
        transition: 0.3s;
    }
    .btn-kembali:hover { background-color: #111827; }

    @media print {
        .no-print, .sidebar, .header { display: none !important; }
        .content { margin: 0 !important; width: 100% !important; padding: 0 !important; }
        .card-laporan { box-shadow: none !important; border: none !important; }
    }
</style>

<div class="action-row no-print">
    <button onclick="window.print()" class="btn-cetak">
        <i class="fa fa-print"></i> Cetak Laporan (PDF)
    </button>
</div>

<div class="card-laporan">
    <div class="header-laporan">
        <h2 style="margin:0">LAPORAN RINGKASAN</h2>
        <p style="color: #666;">Sistem Informasi Pengelolaan Fasilitas Desa Kesamben</p>
        <hr style="border: 0; border-top: 2px solid #111; margin-top: 15px;">
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <h4 style="font-size:11px; color:#64748b; margin:0">TOTAL PENDAPATAN</h4>
            <p style="font-size:20px; font-weight:bold; margin:8px 0">Rp {{ number_format($total_pendapatan, 0, ',', '.') }}</p>
        </div>
        <div class="stat-box">
            <h4 style="font-size:11px; color:#64748b; margin:0">BOOKING PENDING</h4>
            <p style="font-size:20px; font-weight:bold; margin:8px 0; color:#ef4444;">{{ $booking_pending }}</p>
        </div>
        <div class="stat-box">
            <h4 style="font-size:11px; color:#64748b; margin:0">FASILITAS DISEWA</h4>
            <p style="font-size:20px; font-weight:bold; margin:8px 0">{{ $fasilitas_disewa }}</p>
        </div>
        <div class="stat-box">
            <h4 style="font-size:11px; color:#64748b; margin:0">TOTAL FASILITAS</h4>
            <p style="font-size:20px; font-weight:bold; margin:8px 0">{{ $total_fasilitas }}</p>
        </div>
    </div>

    <table class="table-laporan">
        <thead>
            <tr style="background:#f8fafc">
                <th>No</th>
                <th>Nama</th>
                <th>NIK</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $index => $u)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $u->name }}</strong></td>
                <td>{{ $u->nik ?? '-' }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->role == 'kaur' ? 'Admin' : 'Penyewa' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="no-print" style="margin-bottom: 50px;">
    <a href="{{ route('users.index') }}" class="btn-kembali">
        <i class="fa fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>

@endsection