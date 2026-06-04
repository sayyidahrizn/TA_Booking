<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Validasi Pengembalian Fasilitas Desa Kesamben</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333333; line-height: 1.6; background-color: #f8fafc; padding: 20px;">

    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        
        <h2 style="color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-top: 0;">Validasi Pengembalian Fasilitas</h2>

        <p>Yth. <strong>{{ $penyewaan->user->name ?? $penyewaan->nama_penyewa ?? 'Pelanggan' }}</strong>,</p>

        <p>
            Kami menginformasikan bahwa proses pengembalian fasilitas yang Anda sewa melalui <strong>Sistem Informasi Booking Fasilitas Desa Kesamben</strong> telah selesai divalidasi oleh pihak Admin.
        </p>

        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 35%; font-weight: bold; padding: 5px 0;">Kode Booking</td>
                    <td style="width: 5%; padding: 5px 0;">:</td>
                    <td style="font-weight: bold; padding: 5px 0; color: #2563eb;">{{ $penyewaan->kode_booking }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 5px 0; vertical-align: top;">Fasilitas disewa</td>
                    <td style="padding: 5px 0; vertical-align: top;">:</td>
                    <td style="padding: 5px 0;">
                        @if($penyewaan->details && $penyewaan->details->count() > 0)
                            <ul style="margin: 0; padding-left: 20px;">
                                @foreach($penyewaan->details as $detail)
                                    <li>{{ $detail->fasilitas->nama_fasilitas }}</li>
                                @endforeach
                            </ul>
                        @else
                            {{ $penyewaan->fasilitas->nama_fasilitas ?? '-' }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        {{-- Logika Pengecekan Apakah Ada Denda --}}
        @if($totalDenda > 0)
            <div style="background-color: #fff7ed; border-left: 4px solid #ea580c; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <b style="color: #c2410c;">Informasi Tanggungan Denda:</b>
                <p style="margin: 5px 0 10px 0; color: #7c2d12;">
                    Berdasarkan hasil validasi kelengkapan, waktu pengembalian, atau kondisi fasilitas, Anda dikenakan denda sebesar:
                </p>
                <h3 style="margin: 0; color: #ea580c; font-size: 22px;">
                    Rp {{ number_format($totalDenda, 0, ',', '.') }}
                </h3>
                <p style="margin: 10px 0 0 0; font-size: 13px; color: #7c2d12;">
                    *Silakan log in ke dalam sistem untuk melihat rincian denda dan melakukan penyelesaian pembayaran denda.
                </p>
            </div>
        @else
            <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; color: #14532d; font-weight: bold;">
                    ✓ Fasilitas telah dikembalikan dengan lengkap dan tepat waktu. Anda tidak dikenakan denda pembayaran (Denda: Rp 0).
                </p>
            </div>
        @endif

        <p>Terima kasih telah menggunakan fasilitas Desa Kesamben dengan tertib dan bertanggung jawab.</p>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        <small style="color: #64748b; display: block; text-align: center;">Email ini dikirim secara otomatis oleh Sistem Informasi Booking Fasilitas Desa Kesamben.</small>
    
    </div>

</body>
</html>