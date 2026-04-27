<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Resmi Sistem Admin Desa</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #333; line-height: 1.2; margin: 20px; }
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 5px; margin-bottom: 20px; text-align: center; position: relative; }
        .kop-surat h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .kop-surat h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .kop-surat p { margin: 2px 0; font-size: 12px; font-style: italic; }
        .logo { position: absolute; left: 0; top: 0; width: 70px; }
        .judul-laporan { text-align: center; margin-bottom: 20px; }
        .judul-laporan h3 { text-decoration: underline; margin-bottom: 5px; font-size: 16px; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; vertical-align: middle; }
        th { background-color: #f2f2f2; text-align: center; text-transform: uppercase; font-weight: bold; }
        
        .total-row { background-color: #f9f9f9; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .ttd-box { float: right; width: 230px; text-align: center; font-size: 13px; margin-top: 40px; }
        h4 { text-transform: uppercase; font-size: 14px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="kop-surat">
        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Lencana_Garuda.png" class="logo">
        <h1>PEMERINTAH KABUPATEN JOMBANG</h1>
        <h2>KANTOR KEPALA DESA KESAMBEN</h2>
        <p>Jl. Raya Kesamben No. 123, Kec. Kesamben, Kabupaten Jombang, Jawa Timur</p>
        <p>Email: desakesamben@gmail.com | Kode Pos: 61484</p>
    </div>

    <div class="judul-laporan">
        <h3>LAPORAN PENYEWAAN FASILITAS DESA</h3>
        <p>Periode: <strong>{{ $periodeTeks }}</strong></p>
    </div>

    <h4>I. Ringkasan Aktivitas & Keuangan</h4>
    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="20%">KATEGORI</th>
                <th width="35%">KETERANGAN</th>
                <th width="15%">JUMLAH</th>
                <th width="25%">NOMINAL (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td class="text-center">1</td><td>User</td><td>Total Penyewa Terdaftar</td><td class="text-center">{{ $totalPenyewa }} Orang</td><td class="text-right">-</td></tr>
            <tr><td class="text-center">2</td><td>Pembayaran</td><td>Penyewaan Lunas (Sukses)</td><td class="text-center">{{ $sudahBayar }} Kali</td><td class="text-right">-</td></tr>
            <tr><td class="text-center">3</td><td>Pembayaran</td><td style="color: rgb(0, 0, 0);">Penyewaan Ditolak / Batal</td><td class="text-center">{{ $ditolak }} Kali</td><td class="text-right">-</td></tr>
            <tr><td class="text-center">4</td><td>Pengembalian</td><td style="color: rgb(0, 0, 0);">Fasilitas BELUM Kembali</td><td class="text-center">{{ $belumKembali }} Kali</td><td class="text-right">-</td></tr>
            <tr><td class="text-center">5</td><td>Keuangan</td><td>Total Uang Sewa Masuk</td><td class="text-center">-</td><td class="text-right">{{ number_format($totalUang, 0, ',', '.') }}</td></tr>
            <tr><td class="text-center">6</td><td>Keuangan</td><td>Total Pendapatan Denda</td><td class="text-center">-</td><td class="text-right">{{ number_format($totalDenda, 0, ',', '.') }}</td></tr>
            <tr class="total-row"><td colspan="4" class="text-center">GRAND TOTAL PENDAPATAN</td><td class="text-right">Rp {{ number_format($totalUang + $totalDenda, 0, ',', '.') }}</td></tr>
        </tbody>
    </table>

    <h4>II. Rincian Stok Fasilitas Tersedia</h4>
    <table>
        <thead>
            <tr>
                <th width="10%">NO</th>
                <th width="60%">NAMA FASILITAS</th>
                <th width="30%">TOTAL STOK</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daftarFasilitas as $key => $item)
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>
                <td>{{ $item->nama_fasilitas }}</td>
                <td class="text-center">{{ $item->jumlah }} Unit</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL KESELURUHAN STOK</td>
                <td class="text-center">{{ $totalStok }} Unit</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10px; font-style: italic;">
        <p>* Laporan otomatis dihasilkan oleh Sistem Manajemen Desa Kesamben pada {{ $tglCetak }} pukul {{ $waktuCetak }} WIB.</p>
    </div>

    <div class="ttd-box">
        <p>Jombang, {{ $tglCetak }}</p>
        <p>Admin Desa Kesamben,</p>
        <div style="margin-top: 60px;"></div>
        <p><strong>( ____________________ )</strong></p>
        <p>NIP. ...........................</p>
    </div>
</body>
</html>