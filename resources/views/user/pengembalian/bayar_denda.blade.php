@extends('user.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-credit-card me-2"></i>Pembayaran Denda</h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h6 class="text-muted mb-1">Total yang harus dibayar:</h6>
                        <h2 class="fw-bold text-danger">Rp {{ number_format($pengembalian->total_denda, 0, ',', '.') }}</h2>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-4 border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Fasilitas:</span>
                            <span class="fw-bold text-dark">{{ $pengembalian->penyewaan->fasilitas->nama_fasilitas }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Denda Keterlambatan:</span>
                            <span class="text-dark">Rp {{ number_format($pengembalian->denda_telat, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Denda Kerusakan:</span>
                            <span class="text-dark">Rp {{ number_format($pengembalian->denda_rusak, 0, ',', '.') }}</span>
                        </div>
                        <hr>
                        <div class="small">
                            <strong class="text-muted">Catatan Admin:</strong><br>
                            <p class="mb-0 italic text-secondary">{{ $pengembalian->catatan_admin ?? 'Tidak ada catatan khusus dari admin.' }}</p>
                        </div>
                    </div>

                    <button id="pay-button" class="btn btn-danger w-100 py-3 fw-bold shadow-sm rounded-3">
                        <i class="fas fa-shield-alt me-2"></i>BAYAR SEKARANG
                    </button>
                    
                    <a href="{{ route('user.pengembalian') }}" class="btn btn-link w-100 mt-2 text-muted text-decoration-none small">
                        Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Skrip Midtrans Snap --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script type="text/javascript">
    const payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function () {
        window.snap.pay('{{ $snapToken }}', {
            onSuccess: function (result) {
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil!',
                    text: 'Denda Anda telah terbayar. Status akan segera diperbarui.',
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
                    text: 'Silakan selesaikan transaksi Anda di aplikasi pembayaran.'
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