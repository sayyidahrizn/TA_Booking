@extends('user.layouts.app')

@section('content')
<!-- Google Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #1e293b;
    }

    .checkout-container {
        max-width: 480px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .main-card {
        border: none;
        border-radius: 28px;
        background: #ffffff;
        padding: 32px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04),
                    0 8px 10px -6px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }

    .main-card::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle,
                rgba(62, 71, 244, 0.05) 0%,
                rgba(255,255,255,0) 70%);
        border-radius: 50%;
    }

    .brand-logo {
        width: 80px;
        height: 80px;
        background: #f1f5f9;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        padding: 10px;
    }

    .booking-id {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #94a3b8;
        font-weight: 700;
        background: #f1f5f9;
        padding: 4px 12px;
        border-radius: 100px;
        display: inline-block;
        margin-bottom: 16px;
    }

    .amount-section {
        margin-bottom: 30px;
    }

    .amount-title {
        font-size: 0.85rem;
        color: #64748b;
    }

    .amount-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -1.5px;
    }

    .detail-box {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 8px 16px;
        margin-bottom: 24px;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
    }

    .detail-item:not(:last-child) {
        border-bottom: 1px solid #f8fafc;
    }

    .detail-label {
        color: #94a3b8;
        font-size: 0.85rem;
    }

    .detail-value {
        font-weight: 600;
        color: #334155;
        font-size: 0.85rem;
    }

    .input-nominal-box {
        background: #f8fafc;
        border-radius: 24px;
        padding: 24px;
        margin-top: 10px;
        border: 1px dashed #cbd5e1;
    }

    .custom-input-group {
        display: flex;
        align-items: center;
        background: white;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 12px 16px;
        transition: all 0.2s ease;
    }

    .custom-input-group:focus-within {
        border-color: #3e47f4;
        box-shadow: 0 0 0 4px rgba(62, 71, 244, 0.1);
    }

    .currency-prefix {
        font-weight: 700;
        color: #3e47f4;
        margin-right: 10px;
    }

    .styled-input {
        border: none;
        outline: none;
        width: 100%;
        font-weight: 700;
        font-size: 1.1rem;
        color: #1e293b;
        background: transparent;
    }

    .btn-pay-now {
        background: #3e47f4;
        color: #fff;
        border: none;
        border-radius: 18px;
        padding: 16px;
        font-weight: 700;
        width: 100%;
        margin-top: 24px;
        transition: all 0.3s;
    }

    .btn-pay-now:hover {
        background: #2d36d9;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(62, 71, 244, 0.2);
        color: #fff;
    }

    .btn-back {
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-block;
        margin-top: 20px;
    }

    .sisa-badge {
        background: #fffbeb;
        color: #b45309;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 10px;
        border: 1px solid #fef3c7;
        display: inline-block;
    }
</style>

@php
    $terbayar = $totalBayar; // Gunakan totalBayar dari controller
    $sisaTagihanFinal = $sisaTagihan; // Gunakan sisaTagihan dari controller

    if(isset($snapToken)) {
        $nominalAwal = $pembayaran->jumlah_bayar;
    } else {
        if($terbayar > 0){
            $nominalAwal = $sisaTagihanFinal;
        } else {
            // Gunakan $totalTagihan (akumulasi), bukan $penyewaan->total_harga (satuan)
            $nominalAwal = round($totalTagihan * 0.5);
        }
    }
@endphp

<div class="container checkout-container">
    <div class="main-card text-center">

        <div class="brand-logo">
            <img src="{{ asset('images/LOGODESA.png') }}"
                 alt="Logo"
                 style="width:100%; height:100%; object-fit:contain;">
        </div>

        <span class="booking-id">
            #{{ $penyewaan->kode_booking }}
        </span>

        <div class="amount-section">
            <p class="amount-title mb-1">
                Total Tagihan Fasilitas
            </p>

            <h2 class="amount-value">
                Rp{{ number_format($totalTagihan, 0, ',', '.') }}
            </h2>

            @if($terbayar > 0)
                <div class="sisa-badge mt-2">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Sisa Bayar:
                    Rp{{ number_format($sisaTagihanFinal, 0, ',', '.') }}
                </div>
            @endif
        </div>

        <div class="detail-box text-start">
            <div class="detail-item">
                <span class="detail-label">Fasilitas</span>
                <span class="detail-value">
                    {{-- Ubah ke variabel baru agar muncul semua fasilitas --}}
                    {{ $semuaFasilitas }}
                </span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Waktu</span>

                <span class="detail-value">
                    {{ \Carbon\Carbon::parse($penyewaan->tgl_mulai)->format('d M') }}
                    -
                    {{ \Carbon\Carbon::parse($penyewaan->tgl_selesai)->format('d M Y') }}
                </span>
            </div>
        </div>

        <form action="{{ route('user.pembayaran.proses', $penyewaan->id_penyewaan) }}"
              method="POST"
              id="payment-form">

            @csrf

            <div class="input-nominal-box text-start">

                <label class="detail-label d-block mb-2 fw-bold text-dark">
                    Masukkan Nominal Bayar
                </label>

                <div class="custom-input-group">

                    <span class="currency-prefix">Rp</span>

                    <input type="text"
                           id="nominal_display"
                           class="styled-input @error('nominal_bayar') is-invalid @enderror"
                           placeholder="0"
                           value="{{ number_format($nominalAwal, 0, ',', '.') }}"
                           {{ isset($snapToken) ? 'readonly' : '' }}>

                    <input type="hidden"
                           name="nominal_bayar"
                           id="nominal_asli"
                           value="{{ $nominalAwal }}">
                </div>

                @error('nominal_bayar')
                    <div class="text-danger mt-2" style="font-size: 0.75rem;">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            @if(!isset($snapToken))
                <button type="submit" class="btn btn-pay-now">
                    Konfirmasi Pembayaran
                </button>
            @endif
        </form>

        @if(isset($snapToken))
            <button id="pay-button"
                    class="btn btn-pay-now"
                    style="background:#10b981;">

                <i class="bi bi-shield-lock-fill me-2"></i>

                Bayar Sekarang
                (Rp{{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }})
            </button>
        @endif

        <a href="{{ route('user.penyewaan.index') }}"
           class="btn btn-back">

            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke riwayat
        </a>

    </div>
</div>

<script>
    const displayInput = document.getElementById('nominal_display');
    const hiddenInput = document.getElementById('nominal_asli');

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    if(displayInput){

        displayInput.addEventListener('input', function () {

            let angka = this.value.replace(/[^0-9]/g, '');

            hiddenInput.value = angka;

            if (angka) {
                this.value = formatRupiah(angka);
            } else {
                this.value = '';
            }
        });

    }
</script>

@if(isset($snapToken))
    {{-- 1. Load Snap.js --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>

    <script type="text/javascript">
        // Fungsi tunggal untuk memicu popup Midtrans
        function triggerSnap() {
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    window.location.href = "{{ route('user.penyewaan.index') }}";
                },
                onPending: function(result) {
                    window.location.href = "{{ route('user.penyewaan.index') }}";
                },
                onError: function(result) {
                    alert("Terjadi kesalahan, silakan coba lagi.");
                    console.error(result);
                },
                onClose: function() {
                    alert('Anda belum menyelesaikan pembayaran.');
                }
            });
        }

        // Jalankan otomatis saat halaman selesai dimuat (Setelah redirect dari controller)
        window.onload = function() {
            triggerSnap();
        };

        // Tambahkan event listener ke tombol "Bayar Sekarang" jika user menutup popup dan ingin buka lagi
        document.addEventListener('DOMContentLoaded', function() {
            const payBtn = document.getElementById('pay-button');
            if (payBtn) {
                payBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    triggerSnap();
                });
            }
        });
    </script>
@endif
@endsection