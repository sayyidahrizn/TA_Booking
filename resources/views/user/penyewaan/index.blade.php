@extends('user.layouts.app')

@section('page_title_content')
    <h1 style="margin: 0; font-size: 28px; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">Daftar Penyewaan Aktif</h1>
@endsection

@section('content')
<div class="container" style="max-width: 1200px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #f1f5f9;">
    
    <!-- Header Tabel -->
    <div style="padding: 25px 30px; display: flex; justify-content: space-between; align-items: center; background: #ffffff; border-bottom: 1px solid #f1f5f9;">
        <div>
            <h2 style="color: #0f172a; margin: 0; font-weight: 700; font-size: 1.25rem;">Transaksi Berjalan</h2>
            <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">Kelola pesanan dan pantau status pembayaran Anda.</p>
        </div>
        <a href="{{ route('user.penyewaan.create') }}" style="background:#4f46e5; color:white; padding:12px 24px; border-radius:10px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4);">
            + Booking Baru
        </a>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background:#f8fafc; color:#64748b; text-transform: uppercase; font-size: 12px; letter-spacing: 0.05em;">
                    <th style="padding: 16px 24px;">No</th>
                    <th style="padding: 16px 24px;">Penyewa & Kode</th>
                    <th style="padding: 16px 24px;">Fasilitas</th>
                    <th style="padding: 16px 24px;">Tanggal Sewa</th>
                    <th style="padding: 16px 24px;">Total Tagihan</th>
                    <th style="padding: 16px 24px;">Status Sewa</th>
                    <th style="padding: 16px 24px;">Pembayaran</th>
                    <th style="padding: 16px 24px; text-align: center;">Aksi / Bukti</th>
                </tr>
            </thead>
            <tbody style="color: #334155; font-size: 14px;">
                @forelse($data as $kode_booking => $group)
                @php
                    $p = $group->first();
                    $statusSewa = strtolower($p->status_sewa);
                    
                    $totalHargaGrup = \App\Models\Penyewaan::where('kode_booking', $kode_booking)->sum('total_harga');

                    // AMBIL SEMUA PEMBAYARAN YANG BERHASIL UNTUK KODE BOOKING INI
                    $listPembayaranBerhasil = \App\Models\Pembayaran::whereHas('penyewaan', function($q) use ($kode_booking) {
                                        $q->where('kode_booking', $kode_booking);
                                    })
                                    ->where('status_pembayaran', 'berhasil')
                                    ->get();

                    $totalMasuk = $listPembayaranBerhasil->sum('jumlah_bayar');
                    
                    $sisaTagihan = $totalHargaGrup - $totalMasuk;
                    $lunas = $sisaTagihan <= 0; 
                    $sudahAdaBayar = $totalMasuk > 0;

                    $idUntukBayar = $p->id_penyewaan; 
                @endphp

                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                    <td style="padding: 20px 24px;">{{ $loop->iteration }}</td>
                    <td style="padding: 20px 24px;">
                        <span style="font-weight: 700; color: #1e293b; display: block;">{{ $p->nama_penyewa }}</span>
                        <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 12px; color: #475569;">#{{ $kode_booking }}</code>
                    </td>

                    <td style="padding: 20px 24px;">
                        @foreach($group as $item)
                            <div style="margin-bottom: 4px; display: flex; align-items: center; gap: 5px;">
                                <span style="color: #4f46e5;">•</span> {{ $item->fasilitas->nama_fasilitas }}
                            </div>
                        @endforeach
                    </td>

                    <td style="padding: 20px 24px; white-space: nowrap;">
                        <span style="display:block; font-weight: 500;">{{ \Carbon\Carbon::parse($p->tgl_mulai)->format('d M Y') }}</span>
                        <span style="font-size: 12px; color: #94a3b8;">s/d {{ \Carbon\Carbon::parse($p->tgl_selesai)->format('d M Y') }}</span>
                    </td>

                    <td style="padding: 20px 24px; font-weight: 700; color: #0f172a;">
                        Rp {{ number_format($totalHargaGrup, 0, ',', '.') }}
                    </td>

                    <td style="padding: 20px 24px;">
                        @php
                            $bg = '#f1f5f9'; $color = '#475569';
                            if($statusSewa == 'disetujui') { $bg = '#dcfce7'; $color = '#166534'; }
                            elseif($statusSewa == 'proses') { $bg = '#fef9c3'; $color = '#854d0e'; }
                            elseif($statusSewa == 'batal') { $bg = '#fee2e2'; $color = '#991b1b'; }
                        @endphp
                        <span style="background: {{ $bg }}; color: {{ $color }}; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ $statusSewa == 'proses' ? 'menunggu' : $statusSewa }}
                        </span>
                    </td>

                    <td style="padding: 20px 24px;">
                        @if($lunas)
                            <div style="color: #059669; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                <div style="width: 8px; height: 8px; background: #059669; border-radius: 50%;"></div>
                                TERBAYAR LUNAS
                            </div>
                        @elseif($statusSewa == 'disetujui')
                            <a href="{{ route('user.pembayaran.index', $idUntukBayar) }}" 
                               style="display: inline-block; background: {{ $sudahAdaBayar ? '#f59e0b' : '#059669' }}; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; transition: opacity 0.3s;">
                                {{ $sudahAdaBayar ? 'Bayar Sisa' : 'Bayar Sekarang' }}
                            </a>
                            @if($sudahAdaBayar)
                                <div style="font-size: 11px; color: #ef4444; margin-top: 6px; font-weight: 700;">
                                    Sisa: Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                                </div>
                            @endif
                        @else
                            <span style="color: #94a3b8; font-style: italic; font-size: 13px;">Menunggu Konfirmasi Admin</span>
                        @endif
                    </td>

                    <td style="padding: 20px 24px; text-align: center;">
                        <div style="display: flex; flex-direction: column; gap: 5px; align-items: center;">
                            @forelse($listPembayaranBerhasil as $bukti)
                                <a href="{{ route('user.cetak_bukti', $bukti->id) }}" target="_blank" 
                                   style="display: inline-flex; align-items: center; gap: 6px; color: #4f46e5; text-decoration: none; font-weight: 700; border: 2px solid #e0e7ff; padding: 6px 12px; border-radius: 8px; font-size: 11px; transition: all 0.2s; width: 100%; justify-content: center;"
                                   onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='transparent'">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Cetak {{ strtoupper($bukti->jenis_pembayaran) }}
                                </a>
                            @empty
                                <span style="color: #cbd5e1; font-size: 12px; font-style: italic;">Belum ada pembayaran</span>
                            @endforelse
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding: 60px 24px; text-align: center;">
                        <div style="color: #94a3b8; font-size: 16px;">
                            <p style="margin-bottom: 10px;">Belum ada riwayat transaksi aktif.</p>
                            <a href="{{ route('user.penyewaan.create') }}" style="color: #4f46e5; font-weight: 600; text-decoration: none;">Mulai booking fasilitas sekarang &rarr;</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

{{-- @extends('user.layouts.app')

@section('page_title_content')
    <h1 style="margin: 0; font-size: 28px; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">Daftar Penyewaan Aktif</h1>
@endsection

@section('content')
<div class="container" style="max-width: 1200px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #f1f5f9;">
    
    <!-- Header Tabel -->
    <div style="padding: 25px 30px; display: flex; justify-content: space-between; align-items: center; background: #ffffff; border-bottom: 1px solid #f1f5f9;">
        <div>
            <h2 style="color: #0f172a; margin: 0; font-weight: 700; font-size: 1.25rem;">Transaksi Berjalan</h2>
            <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">Kelola pesanan dan pantau status pembayaran Anda.</p>
        </div>
        <a href="{{ route('user.penyewaan.create') }}" style="background:#4f46e5; color:white; padding:12px 24px; border-radius:10px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4);">
            + Booking Baru
        </a>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background:#f8fafc; color:#64748b; text-transform: uppercase; font-size: 12px; letter-spacing: 0.05em;">
                    <th style="padding: 16px 24px;">No</th>
                    <th style="padding: 16px 24px;">Penyewa & Kode</th>
                    <th style="padding: 16px 24px;">Fasilitas</th>
                    <th style="padding: 16px 24px;">Tanggal Sewa</th>
                    <th style="padding: 16px 24px;">Total Tagihan</th>
                    <th style="padding: 16px 24px;">Status Sewa</th>
                    <th style="padding: 16px 24px;">Pembayaran</th>
                    <th style="padding: 16px 24px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody style="color: #334155; font-size: 14px;">
                @forelse($data as $kode_booking => $group)
                @php
                    $p = $group->first();
                    $statusSewa = strtolower($p->status_sewa);
                    
                    // ========================================================================
                    // PERBAIKAN LOGIKA: HITUNG LANGSUNG DARI DATABASE AGAR AKURAT
                    // ========================================================================
                    
                    // 1. Ambil Total Tagihan asli dari semua item penyewaan dengan kode booking ini
                    $totalHargaGrup = \App\Models\Penyewaan::where('kode_booking', $kode_booking)->sum('total_harga');

                    // 2. Ambil Total Pembayaran yang statusnya 'berhasil' untuk seluruh item di kode booking ini
                    $totalMasuk = \App\Models\Pembayaran::whereHas('penyewaan', function($q) use ($kode_booking) {
                                        $q->where('kode_booking', $kode_booking);
                                    })
                                    ->whereIn('status_pembayaran', ['berhasil', 'diverifikasi'])
                                    ->sum('jumlah_bayar');
                    
                    // 3. Tentukan status lunas dengan toleransi selisih kecil (misal pembulatan)
                    $sisaTagihan = $totalHargaGrup - $totalMasuk;
                    $lunas = $sisaTagihan <= 0; 
                    $sudahAdaBayar = $totalMasuk > 0;

                    // ID penyewaan pertama untuk link target pembayaran
                    $idUntukBayar = $p->id_penyewaan; 
                @endphp

                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                    <td style="padding: 20px 24px;">{{ $loop->iteration }}</td>
                    <td style="padding: 20px 24px;">
                        <span style="font-weight: 700; color: #1e293b; display: block;">{{ $p->nama_penyewa }}</span>
                        <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 12px; color: #475569;">#{{ $kode_booking }}</code>
                    </td>

                    <td style="padding: 20px 24px;">
                        @foreach($group as $item)
                            <div style="margin-bottom: 4px; display: flex; align-items: center; gap: 5px;">
                                <span style="color: #4f46e5;">•</span> {{ $item->fasilitas->nama_fasilitas }}
                            </div>
                        @endforeach
                    </td>

                    <td style="padding: 20px 24px; white-space: nowrap;">
                        <span style="display:block; font-weight: 500;">{{ \Carbon\Carbon::parse($p->tgl_mulai)->format('d M Y') }}</span>
                        <span style="font-size: 12px; color: #94a3b8;">s/d {{ \Carbon\Carbon::parse($p->tgl_selesai)->format('d M Y') }}</span>
                    </td>

                    <td style="padding: 20px 24px; font-weight: 700; color: #0f172a;">
                        Rp {{ number_format($totalHargaGrup, 0, ',', '.') }}
                    </td>

                    <td style="padding: 20px 24px;">
                        @php
                            $bg = '#f1f5f9'; $color = '#475569';
                            if($statusSewa == 'disetujui') { $bg = '#dcfce7'; $color = '#166534'; }
                            elseif($statusSewa == 'proses') { $bg = '#fef9c3'; $color = '#854d0e'; }
                            elseif($statusSewa == 'batal') { $bg = '#fee2e2'; $color = '#991b1b'; }
                        @endphp
                        <span style="background: {{ $bg }}; color: {{ $color }}; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ $statusSewa == 'proses' ? 'menunggu' : $statusSewa }}
                        </span>
                    </td>

                    <td style="padding: 20px 24px;">
                        @if($lunas)
                            <div style="color: #059669; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                <div style="width: 8px; height: 8px; background: #059669; border-radius: 50%;"></div>
                                TERBAYAR LUNAS
                            </div>
                        @elseif($statusSewa == 'disetujui')
                            <a href="{{ route('user.pembayaran.index', $idUntukBayar) }}" 
                               style="display: inline-block; background: {{ $sudahAdaBayar ? '#f59e0b' : '#059669' }}; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; transition: opacity 0.3s;">
                                {{ $sudahAdaBayar ? 'Bayar Sisa' : 'Bayar Sekarang' }}
                            </a>
                            @if($sudahAdaBayar)
                                <div style="font-size: 11px; color: #ef4444; margin-top: 6px; font-weight: 700;">
                                    Sisa: Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                                </div>
                            @endif
                        @else
                            <span style="color: #94a3b8; font-style: italic; font-size: 13px;">Menunggu Konfirmasi Admin</span>
                        @endif
                    </td>

                    <td style="padding: 20px 24px; text-align: center;">
                        @if($sudahAdaBayar)
                            <a href="{{ route('user.penyewaan.bukti', $kode_booking) }}" target="_blank" 
                               style="display: inline-flex; align-items: center; gap: 6px; color: #4f46e5; text-decoration: none; font-weight: 700; border: 2px solid #e0e7ff; padding: 8px 14px; border-radius: 10px; font-size: 13px; transition: all 0.2s;"
                               onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='transparent'">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Cetak Bukti
                            </a>
                        @else
                            <span style="color: #cbd5e1; font-size: 12px; font-style: italic;">Belum ada pembayaran</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding: 60px 24px; text-align: center;">
                        <div style="color: #94a3b8; font-size: 16px;">
                            <p style="margin-bottom: 10px;">Belum ada riwayat transaksi aktif.</p>
                            <a href="{{ route('user.penyewaan.create') }}" style="color: #4f46e5; font-weight: 600; text-decoration: none;">Mulai booking fasilitas sekarang &rarr;</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection --}}