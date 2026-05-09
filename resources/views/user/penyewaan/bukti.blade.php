<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Penyewaan - {{ $data->first()->kode_booking }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #333; line-height: 1.6; padding: 20px; background-color: #f9f9f9; }
        .invoice-box { max-width: 850px; margin: auto; padding: 40px; border: 1px solid #ddd; border-radius: 8px; background: #fff; position: relative; }
        
        /* Header / Kop Surat */
        .header { display: flex; align-items: center; border-bottom: 4px double #1e3a8a; padding-bottom: 15px; margin-bottom: 25px; }
        .logo { width: 90px; height: auto; margin-right: 25px; }
        .desa-info h2 { margin: 0; color: #1e3a8a; font-size: 1.6rem; letter-spacing: 1px; }
        .desa-info p { margin: 2px 0; font-size: 13px; color: #555; }

        /* Judul Dokumen */
        .doc-title { text-align: center; margin-bottom: 30px; }
        .doc-title h3 { text-decoration: underline; margin-bottom: 5px; font-size: 1.3rem; }
        .doc-title p { margin: 0; font-size: 14px; font-weight: bold; color: #1e3a8a; }

        /* Grid Informasi */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 40px; margin-bottom: 30px; }
        .info-item { border-bottom: 1px solid #f1f1f1; padding-bottom: 5px; }
        .info-item label { display: block; font-size: 11px; color: #777; text-transform: uppercase; font-weight: bold; }
        .info-item p { margin: 3px 0; font-weight: 600; font-size: 14px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table th { background: #1e3a8a; color: white; padding: 12px; text-align: left; font-size: 13px; }
        table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .row-even { background-color: #fafafa; }

        /* Summary & Stamp */
        .footer-flex { display: flex; justify-content: space-between; align-items: flex-start; margin-top: 20px; }
        .stamp-container { flex: 1; }
        .total-section { flex: 1; text-align: right; }
        
        .total-item { margin-bottom: 8px; }
        .total-item label { font-size: 13px; color: #666; }
        .total-item span { font-weight: bold; font-size: 15px; margin-left: 10px; }
        .grand-total { border-top: 2px solid #1e3a8a; padding-top: 8px; margin-top: 8px; color: #1e3a8a; font-size: 18px !important; }

        .stamp { border: 3px solid #22c55e; color: #22c55e; padding: 10px 20px; border-radius: 8px; display: inline-block; transform: rotate(-15deg); font-weight: 900; text-transform: uppercase; font-size: 1.4rem; opacity: 0.8; }

        .footer-note { text-align: center; margin-top: 60px; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 15px; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; background: white; }
            .invoice-box { border: none; box-shadow: none; width: 100%; max-width: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 25px; background: #1e3a8a; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
            Cetak Bukti Sewa
        </button>
        <a href="{{ route('user.penyewaan.index') }}" style="padding: 10px 25px; background: #6b7280; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none; margin-left: 10px;">
            Kembali
        </a>
    </div>

    <div class="invoice-box">
        <!-- Kop Surat -->
        <div class="header">
            <img src="{{ asset('images/LOGODESA.png') }}" class="logo" alt="Logo Desa">
            <div class="desa-info">
                <h2>PEMERINTAH DESA KESAMBEN KAB.BLITAR</h2>
                <p>Jl. Jaksa Agung Suprapto No.01, Kesamben, Kec. Kesamben, Kabupaten Blitar, Jawa Timur </p>
                <p>Instagram: @pemdes_kesamben | Facebook: Pemdes Kesamben | Kode Pos:61419</p>
            </div>
        </div>

        <div class="doc-title">
            <h3>BUKTI PENYEWAAN FASILITAS</h3>
            <p>NOMOR: {{ $data->first()->kode_booking }}</p>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Nama Penyewa</label>
                <p>{{ strtoupper($data->first()->user->name) }}</p>
            </div>
            <div class="info-item">
                <label>NIK</label>
                <p>{{ $data->first()->user->nik }}</p>
            </div>
            <div class="info-item">
                <label>Tanggal & Waktu Sewa</label>
                {{-- Menyesuaikan dengan input jam sewa yang dipilih --}}
                <p>
                    {{ \Carbon\Carbon::parse($data->first()->tgl_mulai)->translatedFormat('d F Y') }} <br>
                    <small>({{ \Carbon\Carbon::parse($data->first()->tgl_mulai)->format('H:i') }} s/d {{ \Carbon\Carbon::parse($data->first()->tgl_selesai)->format('H:i') }} WIB)</small>
                </p>
            </div>
            <div class="info-item">
                <label>Metode Pembayaran</label>
                @php $pay = $data->first()->pembayaran->first(); @endphp
                <p>{{ strtoupper($pay->metode_pembayaran) }} ({{ strtoupper($pay->jenis_pembayaran) }})</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>NAMA FASILITAS</th>
                    <th style="text-align: center;">JUMLAH</th>
                    <th style="text-align: right;">HARGA SATUAN</th>
                    <th style="text-align: right;">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $key => $item)
                <tr class="{{ $key % 2 == 0 ? '' : 'row-even' }}">
                    <td>{{ $item->fasilitas->nama_fasilitas }}</td>
                    <td style="text-align: center;">{{ $item->jumlah_sewa }} Unit</td>
                    <td style="text-align: right;">Rp {{ number_format($item->fasilitas->harga_sewa, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer-flex">
            <div class="stamp-container">
                @if($pay->status_pembayaran == 'berhasil')
                    <div class="stamp">LUNAS</div>
                @else
                    <div class="stamp" style="border-color: #d97706; color: #d97706;">PROSES</div>
                @endif
            </div>
            
            <div class="total-section">
                <div class="total-item">
                    <label>Total Harga Sewa:</label>
                    <span>Rp {{ number_format($data->sum('total_harga'), 0, ',', '.') }}</span>
                </div>
                <div class="total-item">
                    <label>Total Dibayar:</label>
                    <span style="color: #22c55e;">Rp {{ number_format($pay->jumlah_bayar, 0, ',', '.') }}</span>
                </div>
                <div class="total-item grand-total">
                    <label>Sisa Tagihan:</label>
                    <span>Rp {{ number_format($data->sum('total_harga') - $pay->jumlah_bayar, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="footer-note">
            {{-- Menggunakan updated_at dari pembayaran sebagai bukti waktu validasi lunas --}}
            <p><strong>Validasi Lunas Pada:</strong> {{ \Carbon\Carbon::parse($pay->updated_at)->translatedFormat('d/m/Y H:i:s') }} WIB</p>
        </div>
    </div>
</body>
</html>