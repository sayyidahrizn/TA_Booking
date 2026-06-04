<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Sistem Desa - {{ $jenis }}</title>

    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #333;
            margin: 10px;
            font-size: 10px;
            line-height: 1.4;
        }

        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
            text-align: center;
            position: relative;
        }

        .kop-surat h1 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .kop-surat h2 { margin: 0; font-size: 14px; text-transform: uppercase; }
        .kop-surat p { margin: 2px 0; font-size: 10px; }

        .logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 60px;
        }

        .judul-laporan {
            text-align: center;
            margin-bottom: 15px;
        }

        .judul-laporan h3 {
            margin-bottom: 5px;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed; /* Menjaga agar tabel tidak melebihi kertas */
        }

        table th {
            background: #f2f2f2;
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            word-wrap: break-word;
        }

        table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: middle;
            font-size: 9px;
            word-wrap: break-word;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            font-style: italic;
            color: #555;
            width: 60%;
            float: left;
        }

        .ttd-box {
            width: 200px;
            float: right;
            text-align: center;
            margin-top: 20px;
        }

        .ttd-space { height: 50px; }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <div class="kop-surat">
        <img src="{{ public_path('images/LOGODESA.png') }}" class="logo">
        <h1>PEMERINTAH KABUPATEN BLITAR</h1>
        <h2>KANTOR KEPALA DESA KESAMBEN</h2>
        <p>Jl. Jaksa Agung Suprapto No.01, Kesamben, Kabupaten Blitar</p>
        <p>Telp: 0342 331128</p>
    </div>

    {{-- JUDUL --}}
    <div class="judul-laporan">
        <h3>LAPORAN {{ $jenis == 'fasilitas' ? 'FASILITAS TERPOPULER' : strtoupper($jenis) }}</h3>
        <p>Periode: <strong>{{ $periodeTeks }}</strong></p>
    </div>

    {{-- TABLE --}}
    @if($jenis == 'fasilitas')
        {{-- TAMPILAN KHUSUS LAPORAN FASILITAS TERPOPULER --}}
        <table>
            <thead>
                <tr>
                    <th width="10%">No</th>
                    <th width="65%">Nama Fasilitas</th>
                    <th width="25%">Total Dipinjam</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dataFasilitas as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td style="padding-left: 6px;"><strong>{{ $item->nama_fasilitas }}</strong></td>
                        <td class="text-center">{{ $item->total_peminjaman }} kali</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">Data laporan tidak ditemukan</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        {{-- TAMPILAN BAWAAN UNTUK LAPORAN SEWA / DENDA / SEMUA --}}
        <table>
            <thead>
                <tr>
                    <th width="3%">No</th>
                    <th width="10%">Kode Booking</th>
                    <th width="10%">NIK</th>
                    <th width="12%">Penyewa</th>
                    <th width="12%">Fasilitas</th>
                    <th width="4%">Jml</th>
                    <th width="10%">Total Sewa</th>
                    <th width="10%">Status Sewa</th>
                    <th width="8%">Kondisi</th>
                    <th width="10%">Jml Denda</th>
                    <th width="12%">Alasan / Catatan</th>
                    <th width="8%">Status Denda</th>
                    <th width="10%">Tanggal</th>
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

                        // Logic Status Sewa
                        $statusSewa = ucfirst($first->status_sewa ?? '-');
                        if($first->status_sewa == 'dibatalkan_user') $statusSewa = 'Dibatalkan';
                        elseif($first->status_sewa == 'menunggu_pengembalian') $statusSewa = 'Mng. Kembali';
                        elseif($first->status_sewa == 'menunggu_validasi_pengembalian') $statusSewa = 'Validasi';
                        elseif($first->status_sewa == 'menunggu_pembayaran_denda') $statusSewa = 'Selesai Pengembalian';

                        // DATA DENDA (Aggregated for multiple items)
                        $totalNominalDenda = $first->denda->sum('total_denda');
                        $semuaKondisi = $first->denda->pluck('jenis_kerusakan')->unique()->filter()->implode(', ');
                        $semuaAlasan = $first->denda->pluck('keterangan_kerusakan')->unique()->filter()->implode(' | ');
                        
                        // Status Denda: Jika ada satu saja yang belum lunas
                        $isAdaTunggakan = $first->denda->contains('status_denda', 'belum_bayar');
                        $statusDenda = '-';
                        if($first->denda->isNotEmpty()){
                            $statusDenda = $isAdaTunggakan ? 'Belum Bayar' : 'Lunas';
                        }
                    @endphp

                    @foreach($items as $index => $item)
                        <tr>
                            @if($index == 0)
                                <td class="text-center" rowspan="{{ $rowspan }}">{{ $loop->parent->iteration }}</td>
                                <td class="text-center" rowspan="{{ $rowspan }}">{{ $kodeBooking }}</td>
                                <td class="text-center" rowspan="{{ $rowspan }}">{{ $first->user->nik ?? '-' }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $first->user->name ?? '-' }}</td>
                            @endif

                            <td>{{ $item->fasilitas->nama_fasilitas ?? '-' }}</td>
                            <td class="text-center">{{ $item->jumlah_sewa }}</td>

                            @if($index == 0)
                                <td class="text-right" rowspan="{{ $rowspan }}">
                                    Rp {{ number_format($items->sum('total_harga'), 0, ',', '.') }}
                                </td>
                                <td class="text-center" rowspan="{{ $rowspan }}">{{ $statusSewa }}</td>
                                <td class="text-center" rowspan="{{ $rowspan }}">{{ $semuaKondisi ?: '-' }}</td>
                                <td class="text-right" rowspan="{{ $rowspan }}">
                                    {{ $totalNominalDenda > 0 ? 'Rp ' . number_format($totalNominalDenda, 0, ',', '.') : '-' }}
                                </td>
                                <td rowspan="{{ $rowspan }}">{{ $semuaAlasan ?: '-' }}</td>
                                <td class="text-center" rowspan="{{ $rowspan }}">{{ $statusDenda }}</td>
                                <td class="text-center" rowspan="{{ $rowspan }}">
                                    {{ $first->created_at->format('d/m/Y') }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="13" class="text-center">Data laporan tidak ditemukan</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="clearfix">
        <div class="footer">
            <p>* Laporan otomatis dihasilkan oleh Sistem Desa Kesamben pada {{ $tglCetak }} pukul {{ $waktuCetak ?? date('H:i') }} WIB.</p>
        </div>
        <div class="ttd-box">
            <p>Blitar, {{ $tglCetak }}</p>
            <p>Admin Desa Kesamben</p>
            <div class="ttd-space"></div>
            <p class="text-bold">( ____________________ )</p>
        </div>
    </div>

</body>
</html>