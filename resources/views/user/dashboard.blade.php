@extends('user.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div style="font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding: 30px; max-width: 1200px; margin: 0 auto; color: #333;">
    
    <div style="margin-bottom: 25px;">
        <h1 style="margin: 0; font-size: 28px; font-weight: 700; color: #1a202c;">Dashboard Pengunjung</h1>
        <p style="margin: 5px 0 0; color: #718096; font-size: 15px;">Ringkasan aktivitas penyewaan Anda.</p>
    </div>

    <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 25px 30px; border-radius: 15px; color: white; margin-bottom: 25px; box-shadow: 0 10px 20px rgba(59, 130, 246, 0.15);">
        <h3 style="margin: 0; font-size: 14px; font-weight: 400; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px;">Informasi Akun</h3>
        <p style="margin: 8px 0 0; font-size: 22px; font-weight: 500;">Selamat datang kembali, <strong style="font-weight: 800;">{{ Auth::user()->name }}</strong>! </p>
    </div>

    <div style="display: flex; gap: 20px; margin-bottom: 30px;">
        <div style="flex: 1; background: #fff; padding: 20px 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #edf2f7;">
            <div style="color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Penyewaan</div>
            <div style="font-size: 30px; font-weight: 800; color: #2d3748; margin-top: 5px;">{{ $totalPenyewaan }}</div>
        </div>
        <div style="flex: 1; background: #fff; padding: 20px 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #edf2f7;">
            <div style="color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Penyewaan Aktif</div>
            <div style="font-size: 30px; font-weight: 800; color: #38a169; margin-top: 5px;">{{ $penyewaanAktif }}</div>
        </div>
    </div>

    <div style="background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #edf2f7;">
        <div style="padding: 20px 25px; background: #f8fafc; border-bottom: 1px solid #edf2f7;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #2d3748;">Status Penyewaan Terbaru</h3>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
                <thead>
                    <tr style="text-align: left; background: #fff;">
                        <th style="padding: 15px 25px; color: #a0aec0; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #edf2f7; width: 50px;">No</th>
                        <th style="padding: 15px 25px; color: #a0aec0; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #edf2f7;">Fasilitas</th>
                        <th style="padding: 15px 25px; color: #a0aec0; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #edf2f7; text-align: center;">Tanggal Mulai</th>
                        <th style="padding: 15px 25px; color: #a0aec0; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #edf2f7; text-align: center;">Total Harga</th>
                        <th style="padding: 15px 25px; color: #a0aec0; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #edf2f7; text-align: center;">Status Sewa</th>
                        <th style="padding: 15px 25px; color: #a0aec0; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #edf2f7; text-align: center;">Pembayaran</th>
                        <th style="padding: 15px 25px; color: #a0aec0; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #edf2f7; text-align: center;">Pengembalian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penyewaan as $index => $group)
                        @php 
                            $p = $group->first(); 
                            $statusSewa = strtolower($p->status_sewa);
                            $statusBayar = strtolower($p->status_pembayaran);
                            
                            $totalHargaSewa = $group->sum('total_harga'); 
                            $dataKembali = $p->pengembalian; 
                            $totalDenda = $dataKembali ? $dataKembali->total_denda : 0;
                            $grandTotal = $totalHargaSewa + $totalDenda;
                        @endphp
                        <tr style="border-bottom: 1px solid #f7fafc;">
                            <td style="padding: 20px 25px; font-size: 14px; color: #718096; font-weight: 600;">{{ $loop->iteration }}</td>
                            <td style="padding: 20px 25px;">
                                <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                    @foreach($group as $item)
                                        <span style="background: #ebf8ff; color: #2b6cb0; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                            {{ $item->fasilitas->nama_fasilitas ?? '-' }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td style="padding: 20px 25px; text-align: center; font-size: 14px; color: #4a5568;">
                                {{ \Carbon\Carbon::parse($p->tgl_mulai)->format('d M Y') }}
                            </td>
                            <td style="padding: 20px 25px; text-align: center;">
                                <div style="font-size: 14px; font-weight: 700; color: #2d3748;">Rp{{ number_format($grandTotal, 0, ',', '.') }}</div>
                                @if($totalDenda > 0)
                                    <small style="color: #e53e3e; font-size: 10px;">+ Denda: Rp{{ number_format($totalDenda, 0, ',', '.') }}</small>
                                @endif
                            </td>
                            <td style="padding: 20px 25px; text-align: center;">
                                @if($statusSewa == 'disetujui')
                                    <span style="background: #c6f6d5; color: #22543d; padding: 5px 12px; border-radius: 50px; font-size: 10px; font-weight: 700; text-transform: uppercase;">✔ Disetujui</span>
                                @else
                                    <span style="background: #feebc8; color: #744210; padding: 5px 12px; border-radius: 50px; font-size: 10px; font-weight: 700; text-transform: uppercase;">● {{ $statusSewa }}</span>
                                @endif
                            </td>
                            <td style="padding: 20px 25px; text-align: center;">
                                @if(in_array($statusBayar, ['lunas', 'dibayar']))
                                    <span style="background: #38a169; color: white; padding: 5px 12px; border-radius: 8px; font-size: 10px; font-weight: 800;">LUNAS</span>
                                @else
                                    <span style="background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; padding: 5px 12px; border-radius: 8px; font-size: 10px; font-weight: 800;">PENDING</span>
                                @endif
                            </td>
                            <td style="padding: 20px 25px; text-align: center;">
                                @if($dataKembali)
                                    @php $sv = strtolower($dataKembali->status_validasi); @endphp
                                    @if($sv == 'disetujui')
                                        <span style="color: #38a169; font-size: 11px; font-weight: 700;">DIKEMBALIKAN</span>
                                    @elseif($sv == 'ditolak')
                                        <span style="color: #e53e3e; font-size: 11px; font-weight: 700;">DITOLAK</span>
                                    @else
                                        <span style="color: #2b6cb0; font-size: 11px; font-weight: 700;">VALIDASI</span>
                                    @endif
                                @else
                                    <span style="color: #a0aec0; font-size: 11px; font-weight: 500; font-style: italic;">Belum Kembali</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="padding: 50px; text-align: center; color: #a0aec0;">Belum ada data penyewaan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection