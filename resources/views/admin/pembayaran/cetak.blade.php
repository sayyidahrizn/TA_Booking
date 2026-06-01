<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Penyewaan - {{ $data->first()->kode_booking }}</title>

    <style>
        body{
            font-family:'Segoe UI', Arial, sans-serif;
            color:#333;
            line-height:1.6;
            padding:20px;
            background:#f9f9f9;
        }

        .invoice-box{
            max-width:900px;
            margin:auto;
            padding:40px;
            border:1px solid #ddd;
            border-radius:8px;
            background:#fff;
        }

        /* HEADER */
        .header{
            display:flex;
            align-items:center;
            border-bottom:4px double #1e3a8a;
            padding-bottom:15px;
            margin-bottom:25px;
        }

        .logo{
            width:90px;
            margin-right:25px;
        }

        .desa-info h2{
            margin:0;
            color:#1e3a8a;
            font-size:1.6rem;
        }

        .desa-info p{
            margin:2px 0;
            font-size:13px;
            color:#555;
        }

        /* TITLE */
        .doc-title{
            text-align:center;
            margin-bottom:30px;
        }

        .doc-title h3{
            margin-bottom:5px;
            text-decoration:underline;
        }

        .doc-title p{
            margin:0;
            color:#1e3a8a;
            font-weight:bold;
        }

        /* INFO */
        .info-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:15px 40px;
            margin-bottom:30px;
        }

        .info-item{
            border-bottom:1px solid #eee;
            padding-bottom:5px;
        }

        .info-item label{
            display:block;
            font-size:11px;
            text-transform:uppercase;
            color:#777;
            font-weight:bold;
        }

        .info-item p{
            margin:3px 0;
            font-size:14px;
            font-weight:600;
        }

        /* TABLE */
        table{
            width:100%;
            border-collapse:collapse;
            margin-bottom:25px;
        }

        table th{
            background:#1e3a8a;
            color:white;
            padding:12px;
            font-size:13px;
            text-align:left;
        }

        table td{
            padding:12px;
            border-bottom:1px solid #eee;
            font-size:14px;
        }

        .row-even{
            background:#fafafa;
        }

        /* TOTAL */
        .footer-flex{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            margin-top:20px;
        }

        .stamp-container{
            flex:1;
        }

        .total-section{
            flex:1;
            text-align:right;
        }

        .total-item{
            margin-bottom:8px;
        }

        .total-item label{
            font-size:13px;
            color:#666;
        }

        .total-item span{
            font-weight:bold;
            font-size:15px;
            margin-left:10px;
        }

        .grand-total{
            border-top:2px solid #1e3a8a;
            padding-top:8px;
            margin-top:8px;
            color:#1e3a8a;
        }

        .stamp{
            border:3px solid #22c55e;
            color:#22c55e;
            padding:10px 20px;
            border-radius:8px;
            display:inline-block;
            transform:rotate(-15deg);
            font-weight:900;
            text-transform:uppercase;
            font-size:1.4rem;
            opacity:0.8;
        }

        .footer-note{
            text-align:center;
            margin-top:60px;
            font-size:12px;
            color:#777;
            border-top:1px solid #eee;
            padding-top:15px;
        }

        @media print{
            .no-print{
                display:none;
            }

            body{
                padding:0;
                background:white;
            }

            .invoice-box{
                border:none;
                box-shadow:none;
                width:100%;
                max-width:none;
            }
        }
    </style>
</head>

<body>

@php

    $penyewaan = $data->first();

    // =========================
    // AMBIL HANYA PEMBAYARAN TUNAI
    // =========================
    $pembayaran = collect();

    foreach($data as $item){

        foreach($item->pembayaran as $pay){

            if($pay->metode_pembayaran == 'tunai'){
                $pembayaran->push($pay);
            }

        }

    }

    // =========================
    // TOTAL SEWA
    // =========================
    $totalSewa = $data->sum('total_harga');

    // =========================
    // TOTAL BAYAR TUNAI
    // =========================
    $totalBayar = $pembayaran
        ->whereIn('status_pembayaran', ['berhasil', 'diverifikasi'])
        ->sum('jumlah_bayar');

    // =========================
    // TOTAL TAGIHAN
    // =========================
    $totalTagihan = $totalSewa;

    // =========================
    // SISA TAGIHAN
    // =========================
    $sisaTagihan = $totalSewa - $totalBayar;

    if($sisaTagihan < 0){
        $sisaTagihan = 0;
    }

    // =========================
    // LAST PAYMENT
    // =========================
    $lastPayment = $pembayaran->sortByDesc('updated_at')->first();

@endphp

<div class="no-print" style="text-align:center; margin-bottom:20px;">

    <button onclick="window.print()"
        style="padding:10px 25px;
        background:#1e3a8a;
        color:white;
        border:none;
        border-radius:5px;
        cursor:pointer;
        font-weight:bold;">

        Cetak Bukti Pembayaran
    </button>

    <a href="{{ route('admin.pembayaran.index') }}"
        style="padding:10px 25px;
        background:#6b7280;
        color:white;
        border-radius:5px;
        text-decoration:none;
        margin-left:10px;
        font-weight:bold;">

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

            <h2>PEMERINTAH DESA KESAMBEN KAB. BLITAR</h2>

            <p>
                Jl. Jaksa Agung Suprapto No.01,
                Kesamben, Kec. Kesamben,
                Kabupaten Blitar, Jawa Timur
            </p>

            <p>
                Instagram: @pemdes_kesamben |
                Facebook: Pemdes Kesamben |
                Kode Pos: 61419
            </p>

        </div>
    </div>

    <!-- TITLE -->
    <div class="doc-title">

        <h3>
            @if($sisaTagihan <= 0)
                BUKTI PELUNASAN
            @else
                BUKTI PEMBAYARAN (DP)
            @endif
        </h3>

        <p>
            NOMOR: {{ $penyewaan->kode_booking }}
        </p>

    </div>

    <!-- INFO -->
    <div class="info-grid">

        <div class="info-item">
            <label>Nama Penyewa</label>
            <p>{{ strtoupper($penyewaan->user->name) }}</p>
        </div>

        <div class="info-item">
            <label>NIK</label>
            <p>{{ $penyewaan->user->nik }}</p>
        </div>

        <div class="info-item">
            <label>Tanggal & Waktu Sewa</label>

            <p>
                {{ \Carbon\Carbon::parse($penyewaan->tgl_mulai)->translatedFormat('d F Y') }}
                <br>

                <small>
                    (
                    {{ \Carbon\Carbon::parse($penyewaan->tgl_mulai)->format('H:i') }}
                    s/d
                    {{ \Carbon\Carbon::parse($penyewaan->tgl_selesai)->format('H:i') }}
                    WIB
                    )
                </small>
            </p>
        </div>

        <div class="info-item">

            <label>Metode Pembayaran</label>

            @forelse($pembayaran as $pay)

                <p style="margin-bottom:5px;">

                    {{ strtoupper($pay->metode_pembayaran) }}
                    -
                    {{ strtoupper($pay->jenis_pembayaran) }}

                </p>

            @empty

                <p>-</p>

            @endforelse

        </div>

    </div>

    <!-- TABEL FASILITAS -->
    <table>

        <thead>
            <tr>
                <th>NAMA FASILITAS</th>
                <th style="text-align:center;">JUMLAH</th>
                <th style="text-align:right;"> SATUAN</th>
                <th style="text-align:right;">TOTAL</th>
            </tr>
        </thead>

        <tbody>

            @foreach($data as $key => $item)

            <tr class="{{ $key % 2 == 0 ? '' : 'row-even' }}">

                <td>
                    {{ $item->fasilitas->nama_fasilitas }}
                </td>

                <td style="text-align:center;">
                    {{ $item->jumlah_sewa }}
                    <div style="font-size:11px; color:#666;">
                        unit
                    </div>
                </td>

                <td style="text-align:right;">
                    Rp {{ number_format($item->fasilitas->harga_sewa,0,',','.') }}

                    <div style="font-size:11px; color:#666;">
                        per unit
                    </div>
                </td>

                <td style="text-align:right; font-weight:bold;">
                    Rp {{ number_format($item->total_harga,0,',','.') }}

                    <div style="font-size:11px; color:#666;">
                        {{ $item->jumlah_sewa }} × {{ number_format($item->fasilitas->harga_sewa,0,',','.') }}
                    </div>
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

    <div style="text-align:right; margin-top:15px; font-size:16px; font-weight:bold; color:#1e3a8a;">
        SUBTOTAL SEMUA:
        Rp {{ number_format($totalSewa,0,',','.') }}
    </div>

    <!-- RIWAYAT PEMBAYARAN -->
    <h3 style="margin-bottom:10px; color:#1e3a8a;">

        RIWAYAT PEMBAYARAN TUNAI

    </h3>

    <table>

        <thead>
            <tr>
                <th>TANGGAL</th>
                <th>JENIS</th>
                <th>METODE</th>
                <th style="text-align:right;">JUMLAH</th>
                <th style="text-align:center;">STATUS</th>
            </tr>
        </thead>

        <tbody>

        @if($totalBayar > 0)

            <tr>

                <td>
                    {{ $lastPayment ? \Carbon\Carbon::parse($lastPayment->created_at)->translatedFormat('d F Y H:i') : '-' }}
                </td>

                <td>
                    @if($sisaTagihan > 0)
                        DP
                    @else
                        PELUNASAN
                    @endif
                </td>

                <td>
                    TUNAI
                </td>

                <td style="text-align:right;">
                    Rp {{ number_format($totalBayar,0,',','.') }}
                </td>

                <td style="text-align:center;">
                    @if($sisaTagihan <= 0)
                        <span style="color:#16a34a; font-weight:bold;">BERHASIL</span>
                    @else
                        <span style="color:#f59e0b; font-weight:bold;">PROSES</span>
                    @endif
                </td>

            </tr>

        @else

            <tr>
                <td colspan="5" style="text-align:center;">
                    Belum ada pembayaran
                </td>
            </tr>

        @endif

        </tbody>

    </table>

    <!-- TOTAL -->
    <div class="footer-flex">

        <div class="stamp-container">

            @if($totalBayar <= 0)

                <div class="stamp" style="border-color:#d97706; color:#d97706;">
                    BELUM BAYAR
                </div>

            @elseif($sisaTagihan > 0)

                <div class="stamp" style="border-color:#f59e0b; color:#f59e0b;">
                    DP
                </div>

            @else

                <div class="stamp">
                    LUNAS
                </div>

            @endif

        </div>

        <div class="total-section">

            <div class="total-item">

                <label>Total Harga Sewa:</label>

                <span>
                    Rp {{ number_format($totalSewa,0,',','.') }}
                </span>

            </div>

            <div class="total-item">

                <label>Total Dibayar:</label>

                <span style="color:#16a34a;">
                    Rp {{ number_format($totalBayar,0,',','.') }}
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
    <div class="footer-note">

        <p>

            <strong>Terakhir Diperbarui:</strong>

            @if($lastPayment)
                {{ \Carbon\Carbon::parse($lastPayment->updated_at)->translatedFormat('d/m/Y H:i:s') }} WIB
            @else
                -
            @endif

        </p>

    </div>

</div>

</body>
</html>