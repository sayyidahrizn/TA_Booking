@extends('user.layouts.app')

@section('content')
<div class="container" style="max-width: 1100px; margin: 40px auto; background: white; padding: 0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e5e7eb;">
    
    <div style="padding: 25px 30px; background: #ffffff; border-bottom: 1px solid #f3f4f6;">
        <h2 style="color: #1e3a8a; margin: 0; font-weight: 800; font-size: 1.5rem;">Riwayat Penyewaan Selesai</h2>
        <p style="margin: 5px 0 0; color: #64748b; font-size: 14px;">Daftar penyewaan Anda yang sudah diproses oleh admin.</p>
    </div>

    <div style="overflow-x: auto; padding: 20px 30px 30px;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0; background: white;">
            <thead>
                <tr style="background: #1e3a8a;">
                    <th style="padding: 15px 20px; color: #ffffff; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 8px 0 0 8px;">Fasilitas</th>
                    <th style="padding: 15px 20px; color: #ffffff; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">Tanggal Pinjam</th>
                    <th style="padding: 15px 20px; color: #ffffff; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Total Harga</th>
                    <th style="padding: 15px 20px; color: #ffffff; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; text-align: center; border-radius: 0 8px 8px 0;">Status Akhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $group)
                @php 
                    $first = $group->first(); 
                    $totalHargaGrup = $group->sum('total_harga');
                    $isApproved = $first->status_sewa == 'disetujui';
                    $bgColor = $isApproved ? '#dcfce7' : '#fee2e2';
                    $textColor = $isApproved ? '#166534' : '#991b1b';
                    $dotColor = $isApproved ? '#22c55e' : '#ef4444';
                @endphp
                <tr style="transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6;">
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            @foreach($group as $item)
                                <span style="font-weight: 700; color: #111827; font-size: 13px; background: #f3f4f6; padding: 4px 10px; border-radius: 6px;">
                                    {{ $item->fasilitas->nama_fasilitas }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6; text-align: center;">
                        <div style="font-size: 13px; color: #4b5563; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <span style="color: #9ca3af;">📅</span>
                            {{ \Carbon\Carbon::parse($first->tgl_mulai)->translatedFormat('d M Y') }}
                        </div>
                    </td>
                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6; text-align: right;">
                        <div style="font-weight: 700; color: #111827; font-size: 14px;">
                            Rp {{ number_format($totalHargaGrup, 0, ',', '.') }}
                        </div>
                    </td>
                    <td style="padding: 20px; border-bottom: 1px solid #f3f4f6; text-align: center;">
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 9999px; font-size: 11px; font-weight: 800; text-transform: uppercase; background: {{ $bgColor }}; color: {{ $textColor }}; border: 1px solid rgba(0,0,0,0.05);">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $dotColor }};"></span>
                            {{ $first->status_sewa }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding: 50px; text-align: center; color: #9ca3af; font-style: italic;">
                        Tidak ada riwayat penyewaan yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection