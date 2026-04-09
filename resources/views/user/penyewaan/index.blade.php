@extends('user.layouts.app')

@section('content')
<div class="container" style="max-width: 1100px; margin: 40px auto; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e5e7eb;">
    
    <div style="padding: 25px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6;">
        <div>
            <h2 style="color: #111827; margin: 0; font-weight: 800; font-size: 1.5rem;">Daftar Penyewaan Aktif</h2>
            <p style="margin: 5px 0 0; color: #6b7280; font-size: 14px;">Kelola reservasi dan lakukan pembayaran untuk booking yang disetujui.</p>
        </div>
        <a href="{{ route('user.penyewaan.create') }}" style="text-decoration: none; background: #4f46e5; color: white; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);">
            + Buat Booking Baru
        </a>
    </div>

    @if(session('success'))
        <div style="margin: 20px 30px 0; padding: 15px; background: #ecfdf5; color: #065f46; border-radius: 8px; border: 1px solid #bbf7d0; font-size: 14px;">
            <strong>Berhasil!</strong> {{ session('success') }}
        </div>
    @endif

    <div style="overflow-x: auto; padding: 20px 30px 30px;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr style="background: #1e3a8a;">
                    <th style="padding: 15px 20px; color: white; border-radius: 8px 0 0 8px; text-align: left;">Penyewa</th>
                    <th style="padding: 15px 20px; color: white; text-align: left;">Fasilitas</th>
                    <th style="padding: 15px 20px; color: white; text-align: center;">Tanggal</th>
                    <th style="padding: 15px 20px; color: white; text-align: right;">Total Harga</th>
                    <th style="padding: 15px 20px; color: white; text-align: center;">Status Fasilitas</th>
                    <th style="padding: 15px 20px; color: white; text-align: center; border-radius: 0 8px 8px 0;">Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $group)
                @php 
                    $p = $group->first(); 
                    $totalHargaGrup = $group->sum('total_harga'); 
                    $status = strtolower($p->status_sewa);
                @endphp
                <tr>
                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6;">
                        <strong>{{ $p->nama_penyewa }}</strong><br>
                        <small style="color: #6b7280;">NIK: {{ $p->nik }}</small>
                    </td>
                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6;">
                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                            @foreach($group as $item)
                                <span style="padding: 4px 10px; background: #eff6ff; color: #1e40af; border-radius: 6px; font-size: 11px; font-weight: bold; border: 1px solid #dbeafe;">
                                    {{ $item->fasilitas->nama_fasilitas }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6; font-size: 13px; text-align: center;">
                        {{ \Carbon\Carbon::parse($p->tgl_mulai)->format('d M Y') }} <br>
                        <small>s/d</small> <br>
                        {{ \Carbon\Carbon::parse($p->tgl_selesai)->format('d M Y') }}
                    </td>
                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6; font-weight: bold; text-align: right; color: #111827;">
                        Rp {{ number_format($totalHargaGrup, 0, ',', '.') }}
                    </td>
                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6; text-align: center;">
                        @if($status == 'disetujui')
                            <span style="padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; background: #dcfce7; color: #166534;">
                                ● DISETUJUI
                            </span>
                        @else
                            <span style="padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; background: #fef3c7; color: #92400e;">
                                ● {{ strtoupper($status) }}
                            </span>
                        @endif
                    </td>
                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6; text-align: center;">
                        @if($status == 'disetujui')
                            <a href="{{ route('user.pembayaran.index', ['id' => $p->id_penyewaan]) }}" 
                               style="background: #10b981; color: white; text-decoration: none; font-weight: bold; font-size: 13px; padding: 8px 16px; border-radius: 6px; display: inline-block;">
                               Bayar Sekarang
                            </a>
                        @else
                            <button disabled title="Menunggu persetujuan admin"
                                    style="background: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb; font-weight: bold; font-size: 13px; padding: 8px 16px; border-radius: 6px; cursor: not-allowed;">
                                Menunggu
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 50px; text-align: center; color: #9ca3af;">Belum ada pengajuan aktif.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection