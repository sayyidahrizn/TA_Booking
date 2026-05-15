@extends('user.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-danger text-white py-3 shadow-sm">
                    <h5 class="mb-0 fw-bold text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>Rincian Pembayaran Denda
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <span class="badge bg-light text-danger border border-danger mb-2 px-3 py-2">TAGIHAN AKTIF</span>
                        <h6 class="text-muted mb-1">Total yang harus dibayar:</h6>
                        <h2 class="fw-bold text-danger display-6">Rp {{ number_format($denda->total_denda, 0, ',', '.') }}</h2>
                    </div>

                    <div class="card bg-light border-0 rounded-3 mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                <span class="text-muted small">Fasilitas:</span>
                                <span class="fw-bold text-dark text-end">{{ $denda->penyewaan->fasilitas->nama_fasilitas }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Tenggat Kembali:</span>
                                <span class="fw-semibold small">{{ \Carbon\Carbon::parse($denda->penyewaan->tgl_selesai)->format('d M Y') }} {{ $denda->penyewaan->jam_selesai }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Dikembalikan Pada:</span>
                                <span class="fw-semibold small text-danger">{{ \Carbon\Carbon::parse($denda->penyewaan->pengembalian->tanggal_pengembalian)->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 small text-uppercase text-muted">Detail Biaya</h6>
                    <div class="bg-white border rounded-3 p-3 mb-4">
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <span class="d-block fw-bold">Denda Keterlambatan</span>
                                @php
                                    $deadline = \Carbon\Carbon::parse($denda->penyewaan->tgl_selesai . ' ' . $denda->penyewaan->jam_selesai);
                                    $kembali = \Carbon\Carbon::parse($denda->penyewaan->pengembalian->tanggal_pengembalian);
                                    $diff = $deadline->diffInDays($kembali);
                                @endphp
                                <small class="text-muted">Terlambat {{ $diff > 0 ? $diff : 'Beberapa' }} Hari</small>
                            </div>
                            <span class="fw-bold text-dark">Rp {{ number_format($denda->biaya_keterlambatan, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="d-block fw-bold">Denda Kerusakan</span>
                                <small class="text-muted">Kondisi: {{ ucfirst(str_replace('_', ' ', $denda->jenis_kerusakan)) }}</small>
                            </div>
                            <span class="fw-bold text-dark">Rp {{ number_format($denda->biaya_kerusakan, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="small mb-4">
                        <strong class="text-muted text-uppercase" style="font-size: 10px;">Catatan Admin:</strong>
                        <p class="mb-0 italic text-secondary p-2 bg-light rounded mt-1 border-start border-danger border-3">
                            {{ $denda->keterangan_kerusakan ?? 'Tidak ada catatan khusus dari admin.' }}
                        </p>
                    </div>

                    <button id="pay-button" class="btn btn-danger w-100 py-3 fw-bold shadow-sm rounded-3">
                        <i class="fas fa-shield-alt me-2"></i>BAYAR SEKARANG
                    </button>
                    
                    <a href="{{ route('user.pengembalian') }}" class="btn btn-link w-100 mt-2 text-muted text-decoration-none small">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Skrip Midtrans Snap --}}
<script 
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('services.midtrans.client_key') }}">
</script>

{{-- Skrip Pembayaran --}}
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