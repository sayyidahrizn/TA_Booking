<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Penyewaan - {{ $data->first()->kode_booking }}</title>

    <style>
        /* RESET & BASE */
        body {
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            padding: 20px;
            background: #f4f4f9;
            margin: 0;
        }

        .invoice-box {
            max-width: 850px;
            margin: auto;
            padding: 30px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* HEADER SECTION */
        .header {
            display: flex;
            align-items: center;
            border-bottom: 3px double #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-right: 20px;
        }

        .desa-info h2 {
            margin: 0;
            color: #1e3a8a;
            font-size: 1.5rem;
            text-transform: uppercase;
        }

        .desa-info p {
            margin: 2px 0;
            font-size: 12px;
            color: #666;
        }

        /* DOCUMENT TITLE */
        .doc-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .doc-title h3 {
            margin: 0;
            font-size: 1.2rem;
            text-decoration: underline;
        }

        .doc-title p {
            margin: 5px 0 0;
            color: #1e3a8a;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* INFORMATION GRID */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            background: #fcfcfc;
            padding: 15px;
            border-radius: 6px;
        }

        .info-item label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #888;
            font-weight: 800;
        }

        .info-item p {
            margin: 4px 0 0;
            font-size: 14px;
            font-weight: 600;
        }

        /* TABLE STYLING */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th {
            background: #1e3a8a;
            color: #ffffff;
            padding: 10px 12px;
            font-size: 12px;
            text-align: left;
            text-transform: uppercase;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .row-even {
            background: #fdfdfd;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* BADGES */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-warning {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        /* BOX DENDA */
        .denda-box {
            background: #fff7ed;
            border: 2px solid #fdba74;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .denda-box h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #c2410c;
        }

        /* TOTAL SECTION */
        .footer-flex {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .stamp {
            border: 4px solid #22c55e;
            color: #22c55e;
            padding: 15px 30px;
            border-radius: 10px;
            display: inline-block;
            transform: rotate(-12deg);
            font-weight: 900;
            font-size: 1.8rem;
            opacity: 0.6;
            margin-top: 20px;
        }

        .total-section {
            width: 320px;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
        }

        .grand-total {
            border-top: 2px solid #1e3a8a;
            margin-top: 10px;
            padding-top: 10px;
            font-weight: bold;
            font-size: 15px;
            color: #1e3a8a;
        }

        /* PRINT SETTINGS */
        @media print {

            body {
                background: white;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .invoice-box {
                box-shadow: none;
                border: none;
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

@php

    $penyewaan = $data->first();

    // =========================
    // AMBIL PEMBAYARAN TRANSFER
    // =========================
    $pembayaran = collect();

    foreach($data as $item){

        foreach($item->pembayaran as $pay){

            if(in_array($pay->metode_pembayaran, ['midtrans', 'transfer'])){

                $pembayaran->push($pay);

            }

        }

    }

    // HAPUS DUPLIKAT
    $pembayaran = $pembayaran->unique('kode_pembayaran');

    // =========================
    // DATA TOTAL
    // =========================
    $totalSewa = $data->sum('total_harga');

    $dendaList = $penyewaan->denda;

    $totalDenda = $dendaList
        ->where('status_denda', '!=', 'dibatalkan')
        ->sum('total_denda');

    $totalBayar = $pembayaran
        ->whereIn('status_pembayaran', ['berhasil', 'diverifikasi'])
        ->sum('jumlah_bayar');

    $totalDendaDibayar = $dendaList
        ->where('status_denda', 'lunas')
        ->sum('total_denda');

    $totalTagihan = $totalSewa + $totalDenda;

    $sisaTagihan =
        max(
            0,
            ($totalSewa - $totalBayar)
            +
            ($totalDenda - $totalDendaDibayar)
        );

    $lastPayment = $pembayaran
        ->sortByDesc('updated_at')
        ->first();

@endphp

<div class="no-print" style="text-align:center; padding:20px 0;">

    <button onclick="window.print()"
        style="
            padding:10px 25px;
            background:#1e3a8a;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
            font-weight:bold;
        ">

        Cetak Bukti Sewa

    </button>

    <a href="{{ route('user.penyewaan.index') }}"
        style="
            padding:10px 25px;
            background:#6b7280;
            color:white;
            border-radius:5px;
            text-decoration:none;
            margin-left:10px;
            font-weight:bold;
            display:inline-block;
        ">

        Kembali

    </a>

</div>

<div class="invoice-box">

    <!-- HEADER -->
    <div class="header">

        <img src="{{ asset('images/LOGODESA.png') }}"
            class="logo"
            alt="Logo Desa">

        <div class="desa-info">

            <h2>Pemerintah Desa Kesamben</h2>

            <p>
                Jl. Jaksa Agung Suprapto No.01,
                Kec. Kesamben, Kab. Blitar,
                Jawa Timur
            </p>

            <p>
                Instagram: @pemdes_kesamben |
                Kode Pos: 61419
            </p>

        </div>

    </div>

    <!-- TITLE -->
    <div class="doc-title">

        <h3>BUKTI PENYEWAAN FASILITAS</h3>

        <p>
            NO: {{ $penyewaan->kode_booking }}
        </p>

    </div>

    <!-- INFO -->
    <div class="info-grid">

        <div class="info-item">

            <label>Nama Penyewa</label>

            <p>
                {{ strtoupper($penyewaan->user->name) }}
                ({{ $penyewaan->user->nik }})
            </p>

        </div>

        <div class="info-item">

            <label>Waktu Sewa</label>

            <p>
                {{ \Carbon\Carbon::parse($penyewaan->tgl_mulai)->translatedFormat('d M Y') }}

                <br>

                {{ \Carbon\Carbon::parse($penyewaan->tgl_mulai)->format('H:i') }}
                -
                {{ \Carbon\Carbon::parse($penyewaan->tgl_selesai)->format('H:i') }}
                WIB
            </p>

        </div>

        <!-- KOLOM METODE -->
        <div class="info-item">

            <label>Metode Pembayaran</label>

            @forelse($pembayaran as $pay)

                <p style="margin-bottom:5px;">

                    TRANSFER
                    -

                    {{ strtoupper($pay->jenis_pembayaran) }}

                </p>

            @empty

                <p>-</p>

            @endforelse

        </div>

        <div class="info-item">

            <label>Total Fasilitas</label>

            <p>
                Rp {{ number_format($totalSewa,0,',','.') }}
            </p>

        </div>

    </div>

    <!-- TABEL FASILITAS -->
    <table>

        <thead>

            <tr>

                <th>Fasilitas</th>

                <th class="text-center">
                    Jumlah
                </th>

                <th class="text-right">
                    Harga Satuan
                </th>

                <th class="text-right">
                    Subtotal
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($data as $key => $item)

            <tr class="{{ $key % 2 == 0 ? '' : 'row-even' }}">

                <td>
                    {{ $item->fasilitas->nama_fasilitas }}
                </td>

                <td class="text-center">
                    {{ $item->jumlah_sewa }} Unit
                </td>

                <td class="text-right">

                    Rp {{ number_format($item->fasilitas->harga_sewa,0,',','.') }}

                </td>

                <td class="text-right">

                    <strong>
                        Rp {{ number_format($item->total_harga,0,',','.') }}
                    </strong>

                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

    <!-- RIWAYAT PEMBAYARAN -->
    <h4 style="
        color:#1e3a8a;
        border-left:4px solid #1e3a8a;
        padding-left:10px;
    ">

        Riwayat Pembayaran

    </h4>

    <table>

        <thead>

            <tr>

                <th>Tanggal</th>

                <th>Jenis</th>

                <th>Metode</th>

                <th class="text-right">
                    Jumlah
                </th>

                <th class="text-center">
                    Status
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($pembayaran as $pay)

            <tr>

                <td>

                    {{ \Carbon\Carbon::parse($pay->created_at)->translatedFormat('d M Y H:i') }}

                </td>

                <td>

                    {{ strtoupper($pay->jenis_pembayaran) }}

                </td>

                <td>

                    TRANSFER

                </td>

                <td class="text-right">

                    Rp {{ number_format($pay->jumlah_bayar,0,',','.') }}

                </td>

                <td class="text-center">

                    @if($pay->status_pembayaran == 'berhasil')

                        <span class="badge badge-success">
                            BERHASIL
                        </span>

                    @else

                        <span class="badge badge-warning">
                            PROSES
                        </span>

                    @endif

                </td>

            </tr>

            @empty

            <tr>

                <td colspan="5" class="text-center">
                    Belum ada riwayat pembayaran
                </td>

            </tr>

            @endforelse

        </tbody>

    </table>

    <!-- BOX DENDA -->
    @if($dendaList->count() > 0)

    <div class="denda-box">

        <h4>
            RIWAYAT DENDA
        </h4>

        <table>

            <thead>

                <tr>

                    <th>Tanggal</th>

                    <th>Keterangan</th>

                    <th class="text-right">
                        Total Denda
                    </th>

                    <th class="text-center">
                        Status
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($dendaList as $key => $denda)

                <tr class="{{ $key % 2 == 0 ? '' : 'row-even' }}">

                    <td>

                        {{ \Carbon\Carbon::parse($denda->created_at)->translatedFormat('d M Y H:i') }}

                    </td>

                    <td>
                        Denda keterlambatan pengembalian
                    </td>

                    <td class="text-right">

                        Rp {{ number_format($denda->total_denda,0,',','.') }}

                    </td>

                    <td class="text-center">

                        @if($denda->status_denda == 'lunas')

                            <span class="badge badge-success">
                                LUNAS
                            </span>

                        @elseif($denda->status_denda == 'pending')

                            <span class="badge badge-warning">
                                PENDING
                            </span>

                        @else

                            <span class="badge badge-danger">
                                BELUM BAYAR
                            </span>

                        @endif

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

    @endif

    <!-- TOTAL -->
    <div class="footer-flex">

        <div class="stamp-container">

            @if($sisaTagihan <= 0)

                <div class="stamp">
                    LUNAS
                </div>

            @else

                <div class="stamp"
                    style="
                        border-color:#d97706;
                        color:#d97706;
                    ">

                    PROSES

                </div>

            @endif

        </div>

        <div class="total-section">

            <div class="total-item">

                <label>Total Sewa:</label>

                <span>
                    Rp {{ number_format($totalSewa,0,',','.') }}
                </span>

            </div>

            @if($totalDenda > 0)

            <div class="total-item">

                <label>Total Denda:</label>

                <span style="color:#dc2626;">

                    + Rp {{ number_format($totalDenda,0,',','.') }}

                </span>

            </div>

            @endif

            <div class="total-item">

                <label>Total Dibayar:</label>

                <span style="color:#16a34a;">

                    - Rp {{ number_format($totalBayar + $totalDendaDibayar,0,',','.') }}

                </span>

            </div>

            <div class="total-item grand-total">

                <label>Sisa Tagihan:</label>

                <span>
                    Rp {{ number_format($sisaTagihan,0,',','.') }}
                </span>

            </div>

        </div>

    </div>

    <!-- FOOTER -->
    <div style="
        text-align:center;
        margin-top:50px;
        font-size:11px;
        color:#aaa;
        border-top:1px solid #eee;
        padding-top:10px;
    ">

        Dokumen ini dihasilkan secara otomatis
        oleh sistem informasi Desa Kesamben.

    </div>

</div>

</body>
</html>