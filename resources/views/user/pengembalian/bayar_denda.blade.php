@extends('user.layouts.app')

@section('content')
<style>
    .pay-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding: 20px;
        font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
    }

    .pay-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 50px 40px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 600px;
        padding: 60px 60px;
        text-align: center;
        border: 1px solid #f0f0f0;
    }

    .pay-logo {
        width: 40px;   
        height: auto;       
        object-fit: contain; 
        margin-bottom: 15px; 
        display: block;      
        margin-left: auto;   
        margin-right: auto;
    }

    .pay-badge {
        background: #f3f4f6;
        color: #6b7280;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 25px;
        letter-spacing: 0.5px;
    }

    .pay-label {
        color: #182d51;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .pay-total {
        font-size: 36px;
        font-weight: 850;
        color: #ff0000;
        margin-bottom: 30px;
    }

    .pay-info-group {
        border-top: 1px solid #f3f4f6;
        border-bottom: 1px solid #f3f4f6;
        padding: 20px 0;
        margin-bottom: 25px;
        text-align: left;
    }

    .pay-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .pay-info-label {
        color: #6b7280;
    }

    .pay-info-value {
        font-weight: 700;
        color: #000000;
        text-align: right;
    }

    .pay-input-mimic {
        border: 2px dashed #edb0b0;
        background: #fffafa;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 25px;
        text-align: left;
    }

    .pay-input-label {
        color: #9ca3af;
        font-size: 13px;
        margin-bottom: 12px;
        display: block;
    }

    .pay-input-box {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        padding: 12px 18px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .pay-currency {
        color: #000000;
        font-weight: 800;
        font-size: 18px;
        margin-right: 12px;
    }

    .pay-amount-text {
        font-weight: 700;
        font-size: 20px;
        color: #000000;
    }

    .pay-details {
        text-align: left;
        font-size: 13px;
        margin-bottom: 30px;
        background: #f9fafb;
        padding: 15px;
        border-radius: 12px;
    }

    .pay-detail-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .pay-detail-price {
        font-weight: 600;
        color: #ef4444;
    }

    .pay-btn-confirm {
        background: #4f46e5;
        color: #ffffff;
        border: none;
        width: 100%;
        padding: 18px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 16px;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .pay-btn-confirm:hover {
        background: #4338ca;
        transform: translateY(-2px);
        box-shadow: 0 15px 20px -3px rgba(79, 70, 229, 0.4);
    }

    .pay-btn-confirm:active {
        transform: translateY(0);
    }

    .pay-back {
        display: inline-block;
        margin-top: 20px;
        color: #9ca3af;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.2s;
    }

    .pay-back:hover {
        color: #4b5563;
    }
</style>

<div class="pay-container">
    <div class="pay-card">
        <img src="{{ asset('images/LOGODESA.png') }}">
        
        <div>
            <span class="pay-badge">#BOOK-RMZSY7{{ $denda->id }}</span>
        </div>

        <p class="pay-label">Total Tagihan Denda</p>
        <h2 class="pay-total">Rp{{ number_format($denda->total_denda, 0, ',', '.') }}</h2>

        <div class="pay-info-group">
            <div class="pay-info-row">
                <span class="pay-info-label">Fasilitas</span>
                <span class="pay-info-value">{{ $denda->penyewaan->fasilitas->nama_fasilitas }}</span>
            </div>
            <div class="pay-info-row">
                <span class="pay-info-label">Waktu</span>
                <span class="pay-info-value">
                    {{ \Carbon\Carbon::parse($denda->penyewaan->tgl_selesai)->format('d M') }} - 
                    {{ \Carbon\Carbon::parse($denda->penyewaan->pengembalian->tanggal_pengembalian)->format('d M Y') }}
                </span>
            </div>
        </div>

        <div class="pay-input-mimic">
            <span class="pay-input-label">Nominal Bayar</span>
            <div class="pay-input-box">
                <span class="pay-currency">Rp</span>
                <span class="pay-amount-text">{{ number_format($denda->total_denda, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="pay-details">
            @php
                $deadline = \Carbon\Carbon::parse($denda->penyewaan->tgl_selesai . ' ' . $denda->penyewaan->jam_selesai);
                $kembali = \Carbon\Carbon::parse($denda->penyewaan->pengembalian->tanggal_pengembalian);
                $diff = $deadline->diffInDays($kembali);
            @endphp
            <div class="pay-detail-item">
                <span class="text-muted">Denda Telat ({{ $diff > 0 ? $diff : '0' }} hari)</span>
                <span class="pay-detail-price">Rp {{ number_format($denda->biaya_keterlambatan, 0, ',', '.') }}</span>
            </div>
            <div class="pay-detail-item">
                <span class="text-muted">Denda Kerusakan</span>
                <span class="pay-detail-price">Rp {{ number_format($denda->biaya_kerusakan, 0, ',', '.') }}</span>
            </div>
            <hr class="my-2" style="border-top: 1px dashed #e5e7eb;">
            <div style="font-size: 15px; color: #4a5c7b;">
                <strong>Catatan Admin:</strong> {{ $denda->keterangan_kerusakan ?? 'Tidak ada catatan khusus.' }}
            </div>
        </div>

        <button id="pay-button" class="pay-btn-confirm">
            Konfirmasi Pembayaran
        </button>
        
        <a href="{{ route('user.pengembalian') }}" class="pay-back">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke riwayat
        </a>
    </div>
</div>

{{-- Skrip Midtrans Snap (PENTING: Jangan Dihapus) --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>

<script type="text/javascript">
    const payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function () {
        // Logika Integrasi Midtrans
        window.snap.pay('{{ $snapToken }}', {
            onSuccess: function (result) {
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil!',
                    text: 'Denda Anda telah terbayar.',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "{{ route('user.pengembalian') }}";
                });
            },
            onPending: function (result) {
                Swal.fire({
                    icon: 'info',
                    title: 'Menunggu Pembayaran',
                    text: 'Silakan selesaikan transaksi Anda.'
                });
            },
            onError: function (result) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Gagal',
                    text: 'Terjadi kesalahan saat memproses pembayaran.'
                });
            },
            onClose: function () {
                console.log('User closed the popup without finishing the payment');
            }
        });
    });
</script>
@endsection