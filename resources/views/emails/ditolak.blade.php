<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Penolakan Pengajuan Penyewaan Fasilitas</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333333; line-height: 1.6; background-color: #f8fafc; padding: 20px;">

    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        
        <h2 style="color: #991b1b; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-top: 0;">Penyewaan Ditolak</h2>

        <p>Yth. <strong>{{ $penyewaan->user->name ?? $penyewaan->nama_penyewa ?? 'Pelanggan' }}</strong>,</p>

        <p>
            Mohon maaf, pengajuan penyewaan fasilitas Anda melalui <strong>Sistem Informasi Booking Fasilitas Desa Kesamben</strong> saat ini <strong>BELUM DAPAT DISETUJUI</strong> oleh Admin.
        </p>

        <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                <tr>
                    <td style="width: 35%; font-weight: bold; padding: 5px 0; color: #991b1b;">Kode Booking</td>
                    <td style="width: 5%; padding: 5px 0; color: #991b1b;">:</td>
                    <td style="font-weight: bold; padding: 5px 0; color: #ef4444;">{{ $penyewaan->kode_booking }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 5px 0; vertical-align: top; color: #991b1b;">Fasilitas disewa</td>
                    <td style="padding: 5px 0; vertical-align: top; color: #991b1b;">:</td>
                    <td style="padding: 5px 0; color: #991b1b;">
                        {{ $penyewaan->fasilitas->nama_fasilitas ?? '-' }}
                    </td>
                </tr>
            </table>

            @if(!empty($alasan))
                <div style="border-top: 1px dashed #fca5a5; padding-top: 10px; margin-top: 10px;">
                    <b style="color: #991b1b;">Alasan Penolakan:</b>
                    <p style="margin: 5px 0 0 0; color: #b91c1c; font-style: italic;">"{{ $alasan }}"</p>
                </div>
            @endif
        </div>

        <p>
            Apabila Anda memerlukan informasi, silakan menghubungi pihak Kantor Admin Desa Kesamben.
        </p>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        <small style="color: #64748b; display: block; text-align: center;">Email ini dikirim secara otomatis oleh Sistem Informasi Booking Fasilitas Desa Kesamben.</small>
    
    </div>

</body>
</html>