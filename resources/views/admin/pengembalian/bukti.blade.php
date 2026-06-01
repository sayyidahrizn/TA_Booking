<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pembayaran Denda - {{ $denda->penyewaan->kode_booking ?? '-' }}</title>

    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            background: #f9f9f9;
        }

        .invoice-box {
            max-width: 850px;
            margin: auto;
            padding: 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* HEADER KOP SURAT */
        .header {
            display: table;
            width: 100%;
            border-bottom: 4px double #1e3a8a;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .logo-cell {
            display: table-cell;
            vertical-align: middle;
            width: 100px;
        }

        .logo {
            width: 90px;
            height: auto;
        }

        .desa-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 20px;
        }

        .desa-info h2 {
            margin: 0;
            color: #1e3a8a;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .desa-info p {
            margin: 3px 0;
            font-size: 13px;
            color: #555;
        }

        /* TITLE */
        .doc-title {
            text-align: center;
            margin-bottom: 35px;
        }

        .doc-title h3 {
            margin: 0 0 5px 0;
            color: #111;
            font-size: 1.4rem;
            font-weight: 700;
            text-decoration: underline;
            letter-spacing: 1px;
        }

        .doc-title p {
            margin: 0;
            color: #1e3a8a;
            font-weight: bold;
            font-size: 14px;
        }

        /* INFO GRID */
        .info-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }

        .info-table td {
            width: 50%;
            padding: 8px 15px;
            vertical-align: top;
            border: none;
        }

        .info-item {
            border-bottom: 1px solid #eee;
            padding-bottom: 6px;
        }

        .info-item label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .info-item p {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #111;
        }

        /* DETAIL FINES TABLE */
        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.detail-table th {
            background: #1e3a8a;
            color: white;
            padding: 12px;
            font-size: 13px;
            text-align: left;
            font-weight: 600;
        }

        table.detail-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            vertical-align: middle;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .bg-danger { background: #fee2e2; color: #dc2626; }
        .bg-success { background: #dcfce7; color: #16a34a; }

        /* TOTAL & STAMP SECTION */
        .footer-section {
            width: 100%;
            margin-top: 10px;
        }

        .footer-section td {
            vertical-align: top;
            padding: 10px;
        }

        .stamp-container {
            width: 40%;
        }

        .stamp {
            border: 3px solid #22c55e;
            color: #22c55e;
            padding: 10px 25px;
            border-radius: 8px;
            display: inline-block;
            transform: rotate(-10deg);
            font-weight: 900;
            text-transform: uppercase;
            font-size: 1.3rem;
            letter-spacing: 1px;
            opacity: 0.85;
            margin-top: 15px;
        }

        .total-container {
            width: 60%;
            text-align: right;
        }

        .total-box {
            display: inline-block;
            width: 100%;
        }

        .total-item {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .total-item label {
            color: #555;
            margin-right: 10px;
        }

        .total-item span {
            font-weight: 700;
            font-size: 15px;
        }

        .grand-total {
            border-top: 2px solid #1e3a8a;
            padding-top: 10px;
            margin-top: 10px;
            color: #1e3a8a;
            font-size: 16px;
        }

        .grand-total span {
            font-size: 20px;
        }

        /* SIGNATURE */
        .signature-section {
            width: 100%;
            margin-top: 30px;
        }

        .signature-box {
            float: right;
            text-align: center;
            width: 200px;
            font-size: 14px;
        }

        .signature-space {
            height: 65px;
        }

        .footer-note {
            text-align: center;
            margin-top: 45px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 15px;
            clear: both;
        }

        /* PRINT CONFIG */
        @media print {
            .no-print { display: none; }
            body { padding: 0; background: white; }
            .invoice-box { border: none; box-shadow: none; padding: 10px; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align:center; margin-bottom:20px;">
    <button onclick="window.print()" 
            style="padding:10px 25px; background:#1e3a8a; color:white; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">
        Cetak Bukti Denda
    </button>
    <a href="{{ url()->previous() }}" 
       style="padding:10px 25px; background:#6b7280; color:white; border-radius:5px; text-decoration:none; margin-left:10px; font-weight:bold;">
        Kembali
    </a>
</div>

<div class="invoice-box">

    <div class="header">
        <div class="logo-cell">
            <img src="{{ asset('images/LOGODESA.png') }}" class="logo" alt="Logo Desa">
        </div>
        <div class="desa-info">
            <h2>PEMERINTAH DESA KESAMBEN KAB. BLITAR</h2>
            <p>Jl. Jaksa Agung Suprapto No.01, Kesamben, Kec. Kesamben, Kabupaten Blitar, Jawa Timur</p>
            <p>Instagram: @pemdes_kesamben | Kode Pos: 61419</p>
        </div>
    </div>

    <div class="doc-title">
        <h3>RINCIAN & BUKTI PEMBAYARAN DENDA</h3>
        <p>NOMOR BUKTI: {{ $denda->penyewaan->kode_booking ?? '-' }}/DND</p>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="info-item">
                    <label>Nama Penyewa</label>
                    <p>{{ strtoupper($denda->penyewaan->user->name ?? '-') }}</p>
                </div>
            </td>
            <td>
                <div class="info-item">
                    <label>Kode Booking Utama</label>
                    <p>{{ $denda->penyewaan->kode_booking ?? '-' }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="info-item">
                    <label>Tanggal Cetak / Bayar</label>
                    <p>{{ now()->format('d-m-Y H:i') }} WIB</p>
                </div>
            </td>
            <td>
                <div class="info-item">
                    <label>Metode Pembayaran</label>
                    <p>TUNAI (CASH)</p>
                </div>
            </td>
        </tr>
    </table>

    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 25%;">KATEGORI DENDA</th>
                <th style="width: 20%;">STATUS / KONDISI</th>
                <th style="width: 35%;">CATATAN KETERANGAN</th>
                <th style="text-align: right; width: 20%;">NOMINAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Denda Keterlambatan</strong></td>
                <td>
                    @if($denda->biaya_keterlambatan > 0)
                        <span class="status-badge bg-danger">Terlambat</span>
                    @else
                        <span class="status-badge bg-success">Tepat Waktu</span>
                    @endif
                </td>
                <td>
                    @if($denda->biaya_keterlambatan > 0)
                        Denda atas keterlambatan pengembalian fasilitas desa.
                    @else
                        Tidak ada denda keterlambatan.
                    @endif
                </td>
                <td style="text-align: right; font-weight: 600;">
                    Rp {{ number_format($denda->biaya_keterlambatan, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td><strong>Denda Kerusakan</strong></td>
                <td>
                    @if($denda->biaya_kerusakan > 0)
                        <span class="status-badge bg-danger">{{ strtoupper($denda->jenis_kerusakan) }}</span>
                    @else
                        <span class="status-badge bg-success">Tidak Rusak</span>
                    @endif
                </td>
                <td>
                    @if(!empty($denda->keterangan_kerusakan))
                        {!! nl2br(e($denda->keterangan_kerusakan)) !!}
                    @else
                        <span class="text-muted">Tidak ada catatan tambahan.</span>
                    @endif
                </td>
                <td style="text-align: right; font-weight: 600;">
                    Rp {{ number_format($denda->biaya_kerusakan, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="footer-section">
        <tr>
            <td class="stamp-container">
                <div class="stamp">LUNAS</div>
            </td>
            <td class="total-container">
                <div class="total-box">
                    <div class="total-item">
                        <label>Subtotal Keterlambatan:</label>
                        <span>Rp {{ number_format($denda->biaya_keterlambatan, 0, ',', '.') }}</span>
                    </div>
                    <div class="total-item">
                        <label>Subtotal Kerusakan:</label>
                        <span>Rp {{ number_format($denda->biaya_kerusakan, 0, ',', '.') }}</span>
                    </div>
                    <div class="total-item grand-total">
                        <label>Total Dibayar:</label>
                        <span>Rp {{ number_format($denda->total_denda, 0, ',', '.') }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <p>Kesamben, {{ now()->translatedFormat('d F Y') }}</p>
            <p><strong>Admin Desa</strong></p>
            <div class="signature-space"></div>
            <p>(......................................)</p>
        </div>
    </div>

    <div class="footer-note">
        <p>Bukti ini diterbitkan secara resmi oleh Sistem Aplikasi Desa Kesamben dan sah sebagai bukti pembayaran tunai.</p>
    </div>

</div>

</body>
</html>