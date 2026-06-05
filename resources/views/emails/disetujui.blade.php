<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Persetujuan Pengajuan Penyewaan Fasilitas</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333333; line-height: 1.6; background-color: #f8fafc; padding: 20px;">

    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        
        <h2 style="color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-top: 0;">Penyewaan Disetujui</h2>

        <p>Yth. <strong>{{ $penyewaan->user->name ?? $penyewaan->nama_penyewa ?? 'Pelanggan' }}</strong>,</p>

        <p>
            Melalui email ini, kami menginformasikan bahwa pengajuan penyewaan fasilitas Anda melalui <strong>Sistem Informasi Booking Fasilitas Desa Kesamben</strong> telah <strong>DISETUJUI</strong> oleh Admin.
        </p>

        <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 35%; font-weight: bold; padding: 5px 0; color: #14532d;">Kode Booking</td>
                    <td style="width: 5%; padding: 5px 0; color: #14532d;">:</td>
                    <td style="font-weight: bold; padding: 5px 0; color: #16a34a;">{{ $penyewaan->kode_booking }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 5px 0; vertical-align: top; color: #14532d;">Fasilitas disewa</td>
                    <td style="padding: 5px 0; vertical-align: top; color: #14532d;">:</td>
                    <td style="padding: 5px 0; color: #14532d;">
                        {{-- Menampilkan daftar fasilitas yang disewa --}}
                        {{ $penyewaan->fasilitas->nama_fasilitas ?? '-' }}
                    </td>
                </tr>
            </table>
        </div>

        <p>
            Silakan melakukan log in ke dalam sistem untuk melanjutkan ke proses pembayaran guna mengamankan jadwal penyewaan Anda.
        </p>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        <small style="color: #64748b; display: block; text-align: center;">Email ini dikirim secara otomatis oleh Sistem Informasi Booking Fasilitas Desa Kesamben.</small>
    
    </div>

</body>
</html>