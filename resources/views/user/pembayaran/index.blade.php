@extends('user.layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body {
        background-color: #f4f7fa;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .checkout-container {
        max-width: 450px;
        margin: 60px auto;
    }
    .main-card {
        border: none;
        border-radius: 32px;
        background: #ffffff;
        padding: 40px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.04);
    }
    .brand-logo {
        width: 100px;
        height: 100px;
        background: #ffffff;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        color: #fff;
        font-size: 1.5rem;
    }
    .booking-id {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #adb5bd;
        font-weight: 700;
        display: block;
        margin-bottom: 8px;
    }
    .amount-title {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .amount-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: #1a1a1a;
        letter-spacing: -1px;
    }
    .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f1f3f5;
    }
    .detail-item:last-child {
        border-bottom: none;
    }
    .detail-label {
        color: #868e96;
        font-size: 0.9rem;
    }
    .detail-value {
        font-weight: 600;
        color: #212529;
        font-size: 0.9rem;
    }
    .btn-pay-now {
        background: #3e47f4;
        color: #fff;
        border: none;
        border-radius: 16px;
        padding: 16px;
        font-weight: 600;
        width: 100%;
        margin-top: 30px;
        transition: all 0.3s ease;
    }
    .btn-pay-now:hover {
        background: #333;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        color: #fff;
    }
    .btn-back {
        color: #ff8c5a;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 500;
        display: inline-block;
        margin-top: 20px;
        transition: color 0.2s;
    }
    .btn-back:hover {
        color: #495057;
    }
</style>

<div class="container checkout-container">
    <div class="main-card text-center">
        <div class="brand-logo">
            <img src="{{ asset('images/LOGODESA.png') }}" alt="Logo" style="width: 80%; height: 80%; object-fit: contain;">
        </div>

        <span class="booking-id">#{{ $penyewaan->kode_booking }}</span>
        <p class="amount-title mb-1">Total Pembayaran</p>
        <h2 class="amount-value mb-4">Rp{{ number_format($penyewaan->total_harga, 0, ',', '.') }}</h2>

        <div class="text-start mt-4">
            <div class="detail-item">
                <span class="detail-label">Fasilitas</span>
                <span class="detail-value">{{ $penyewaan->fasilitas->nama_fasilitas }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Check-in</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($penyewaan->tgl_mulai)->format('d M Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Check-out</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($penyewaan->tgl_selesai)->format('d M Y') }}</span>
            </div>
        </div>

        <button id="pay-button" class="btn btn-pay-now">
            Konfirmasi & Bayar
        </button>

        <a href="{{ route('user.penyewaan.index') }}" class="btn btn-back">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke riwayat
        </a>
    </div>

    <div class="text-center mt-4">
        {{-- <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a2/Logo_Midtrans.png/1200px-Logo_Midtrans.png" alt="Midtrans Secure" style="height: 15px; opacity: 0.5; filter: grayscale(100%);"> --}}
    </div>
</div>

{{-- Script Midtrans --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>

<script>
    document.getElementById('pay-button').onclick = function () {
        snap.pay('{{ $snapToken }}', {
            onSuccess: function(result) { window.location.href = "{{ route('user.riwayat') }}"; },
            onPending: function(result) { window.location.href = "{{ route('user.penyewaan.index') }}"; },
            onError: function(result) { alert('Terjadi kesalahan, silakan coba lagi.'); },
            onClose: function() { console.log('Customer closed the popup'); }
        });
    };
</script>
@endsection