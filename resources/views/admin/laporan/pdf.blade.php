<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Sistem Desa - {{ $jenis }}</title>

    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #333;
            margin: 20px;
            font-size: 11px;
            line-height: 1.5;
        }

        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
        }

        .kop-surat h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .kop-surat h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }

        .kop-surat p {
            margin: 2px 0;
            font-size: 11px;
        }

        .logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 70px;
        }

        .judul-laporan {
            text-align: center;
            margin-bottom: 20px;
        }

        .judul-laporan h3 {
            margin-bottom: 5px;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th {
            background: #f2f2f2;
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }

        table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: middle;
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            font-style: italic;
            color: #555;
        }

        .ttd-box {
            width: 250px;
            float: right;
            text-align: center;
            margin-top: 30px;
        }

        .ttd-space {
            height: 60px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .badge-success {
            font-weight: bold;
            color: green;
        }

        .badge-danger {
            font-weight: bold;
            color: red;
        }

        .badge-warning {
            font-weight: bold;
            color: #d97706;
        }

        .badge-info {
            font-weight: bold;
            color: #2563eb;
        }
    </style>
</head>

<body>

    {{-- ========================================= --}}
    {{-- KOP SURAT --}}
    {{-- ========================================= --}}

    <div class="kop-surat">

        <img
            src="{{ public_path('images/LOGODESA.png') }}"
            class="logo">

        <h1>PEMERINTAH KABUPATEN BLITAR</h1>

        <h2>KANTOR KEPALA DESA KESAMBEN</h2>

        <p>
            Jl. Jaksa Agung Suprapto No.01,
            Kesamben, Kabupaten Blitar
        </p>

        <p>Telp: 0342 331128</p>

    </div>

    {{-- ========================================= --}}
    {{-- JUDUL --}}
    {{-- ========================================= --}}

    <div class="judul-laporan">

        <h3>
            LAPORAN {{ strtoupper($jenis) }}
        </h3>

        <p>
            Periode:
            <strong>{{ $periodeTeks }}</strong>
        </p>

    </div>

    {{-- ========================================= --}}
    {{-- TABLE --}}
    {{-- ========================================= --}}

    <table>

        <thead>

            <tr>

                <th>No</th>

                <th>Kode Booking</th>

                <th>NIK</th>

                <th>Penyewa</th>

                <th>Fasilitas</th>

                <th>Jumlah</th>

                <th>Total</th>

                <th>Status Sewa</th>

                <th>Kondisi Barang</th>

                <th>Jumlah Denda</th>

                <th>Alasan Denda</th>

                <th>Status Denda</th>

                <th>Tanggal</th>

            </tr>

        </thead>

        <tbody>

            @php
                $grouped = $detailLaporan->groupBy('kode_booking');
            @endphp

            @forelse($grouped as $kodeBooking => $items)

                @php

                    $first = $items->first();

                    $rowspan = $items->count();

                    // =====================================
                    // STATUS SEWA
                    // =====================================

                    $statusSewa = ucfirst($first->status_sewa ?? '-');

                    if($first->status_sewa == 'dibatalkan_user'){
                        $statusSewa = 'Dibatalkan Penyewa';
                    }

                    elseif($first->status_sewa == 'menunggu_pengembalian'){
                        $statusSewa = 'Menunggu Pengembalian';
                    }

                    elseif($first->status_sewa == 'menunggu_validasi_pengembalian'){
                        $statusSewa = 'Validasi Pengembalian';
                    }

                    // STATUS DENDA TIDAK MASUK STATUS SEWA
                    elseif($first->status_sewa == 'menunggu_pembayaran_denda'){
                        $statusSewa = 'Selesai Pengembalian';
                    }

                    // =====================================
                    // DATA DENDA
                    // =====================================

                    $kondisiBarang = '-';
                    $jumlahDenda = '-';
                    $alasanDenda = '-';
                    $statusDenda = '-';

                    if($first->denda){

                        $kondisiBarang =
                            ucfirst($first->denda->kondisi_barang ?? '-');

                        $jumlahDenda =
                            'Rp ' . number_format(
                                $first->denda->jumlah_denda ?? 0,
                                0,
                                ',',
                                '.'
                            );

                        $alasanDenda =
                            $first->denda->alasan_denda ?? '-';

                        // =================================
                        // STATUS DENDA
                        // =================================

                        if(
                            $first->status_sewa ==
                            'menunggu_pembayaran_denda'
                        ){

                            $statusDenda = 'Belum Bayar';

                        }

                        elseif(
                            $first->denda->status_pembayaran ==
                            'lunas'
                        ){

                            $statusDenda = 'Lunas';

                        }

                        else{

                            $statusDenda =
                                ucfirst(
                                    $first->denda->status_pembayaran ?? '-'
                                );
                        }
                    }

                @endphp

                @foreach($items as $index => $item)

                    <tr>

                        {{-- ================================= --}}
                        {{-- DATA UTAMA --}}
                        {{-- ================================= --}}

                        @if($index == 0)

                            <td
                                class="text-center"
                                rowspan="{{ $rowspan }}">

                                {{ $loop->parent->iteration }}

                            </td>

                            <td
                                class="text-center"
                                rowspan="{{ $rowspan }}">

                                {{ $kodeBooking }}

                            </td>

                            <td
                                class="text-center"
                                rowspan="{{ $rowspan }}">

                                {{ $first->user->nik ?? '-' }}

                            </td>

                            <td rowspan="{{ $rowspan }}">

                                {{ $first->user->name ?? '-' }}

                            </td>

                        @endif

                        {{-- ================================= --}}
                        {{-- FASILITAS --}}
                        {{-- ================================= --}}

                        <td>

                            {{ $item->fasilitas->nama_fasilitas ?? '-' }}

                        </td>

                        {{-- ================================= --}}
                        {{-- JUMLAH --}}
                        {{-- ================================= --}}

                        <td class="text-center">

                            {{ $item->jumlah_sewa }}

                        </td>

                        {{-- ================================= --}}
                        {{-- ROWSPAN DATA --}}
                        {{-- ================================= --}}

                        @if($index == 0)

                            {{-- TOTAL --}}
                            <td
                                class="text-right"
                                rowspan="{{ $rowspan }}">

                                Rp {{ number_format(
                                    $items->sum('total_harga'),
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </td>

                            {{-- STATUS SEWA --}}
                            <td
                                class="text-center"
                                rowspan="{{ $rowspan }}">

                                {{ $statusSewa }}

                            </td>

                            {{-- KONDISI --}}
                            <td
                                class="text-center"
                                rowspan="{{ $rowspan }}">

                                {{ $kondisiBarang }}

                            </td>

                            {{-- JUMLAH DENDA --}}
                            <td
                                class="text-right"
                                rowspan="{{ $rowspan }}">

                                {{ $jumlahDenda }}

                            </td>

                            {{-- ALASAN DENDA --}}
                            <td
                                rowspan="{{ $rowspan }}">

                                {{ $alasanDenda }}

                            </td>

                            {{-- STATUS DENDA --}}
                            <td
                                class="text-center"
                                rowspan="{{ $rowspan }}">

                                {{ $statusDenda }}

                            </td>

                            {{-- TANGGAL --}}
                            <td
                                class="text-center"
                                rowspan="{{ $rowspan }}">

                                {{ $first->created_at->format('d M Y') }}

                            </td>

                        @endif

                    </tr>

                @endforeach

            @empty

                <tr>

                    <td colspan="13" class="text-center">

                        Data laporan tidak ditemukan

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

    {{-- ========================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================= --}}

    <div class="clearfix">

        <div class="footer">

            <p>
                * Laporan otomatis dihasilkan oleh
                Sistem Desa Kesamben
                pada {{ $tglCetak }}
                pukul {{ $waktuCetak }} WIB.
            </p>

        </div>

        <div class="ttd-box">

            <p>
                Blitar, {{ $tglCetak }}
            </p>

            <p>
                Admin Desa Kesamben
            </p>

            <div class="ttd-space"></div>

            <p class="text-bold">
                ( ____________________ )
            </p>

        </div>

    </div>

</body>
</html>