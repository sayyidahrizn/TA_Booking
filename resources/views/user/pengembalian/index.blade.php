@extends('user.layouts.app')

{{-- Bagian ini akan tampil di Topbar sebelah kiri, sejajar dengan profil --}}
@section('page_title_content')
    <h1 style="margin: 0; font-size: 30px; font-weight: 700; color: #1a202c;">Manajemen Pengembalian</h1>
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .page-title { font-size: 2.5rem; font-weight: 800; color: #1a202c; margin-bottom: 30px; }
    .card-custom { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; margin-bottom: 40px; border: 2px solid #dee2e6; }
    
    /* Section Denda */
    .card-denda { border: 2px solid #feb2b2; background-color: #fff5f5; }
    .btn-bayar-denda { background-color: #e53e3e; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; transition: 0.3s; }
    .btn-bayar-denda:hover { background-color: #c53030; transform: scale(1.05); color: white; }

    .table-pengembalian { width: 100%; border-collapse: collapse; margin-bottom: 0; border: 2px solid #2d3748; }
    .table-pengembalian th { background-color: #f1f5f9 !important; color: #1a202c !important; font-weight: 800; text-transform: uppercase; font-size: 0.85rem; border: 2px solid #2d3748 !important; padding: 15px; text-align: center; }
    .table-pengembalian td { border: 1px solid #2d3748 !important; padding: 12px; vertical-align: middle; text-align: center; color: #2d3748; font-weight: 500; }
    .text-lunas { color: #059669; font-weight: 800; font-size: 1.1rem; }
    .text-pending { color: #d97706; font-weight: 800; font-size: 1.1rem; }
    .fasilitas-name { text-align: left !important; font-weight: 700; text-transform: capitalize; }
    
    .btn-submit-group { background-color: #7c3aed; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 700; float: right; margin-top: 20px; transition: all 0.3s ease; display: flex; align-items: center; gap: 10px; }
    .btn-submit-group:hover:not(:disabled) { background-color: #6d28d9; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3); }
    .btn-submit-group:disabled { background-color: #9ca3af; cursor: not-allowed; }
    
    .form-check-input { width: 1.25em; height: 1.25em; cursor: pointer; border: 1px solid #2d3748; }
    .table-responsive { border-radius: 8px; overflow: hidden; border: 1px solid #2d3748; }

    @media (max-width: 992px) {
        .page-title { font-size: 1.8rem; }
        .card-custom { padding: 15px; }
        .btn-submit-group { width: 100%; justify-content: center; float: none; }
    }
</style>

<div class="container py-5">

    {{-- 1. NOTIFIKASI TAGIHAN DENDA --}}
    @if(isset($denda_tunggakan) && $denda_tunggakan->count() > 0)
        <div class="card-custom card-denda shadow-sm animate__animated animate__headShake">
            <h4 class="text-danger fw-bold mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Tagihan Denda Perlu Dibayar</h4>
            <div class="table-responsive">
                <table class="table table-bordered bg-white">
                    <thead class="table-danger">
                        <tr>
                            <th>Fasilitas</th>
                            <th>Denda Telat</th>
                            <th>Denda Rusak</th>
                            <th>Total Bayar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($denda_tunggakan as $denda)
                        <tr>
                            <td class="fw-bold">{{ $denda->penyewaan->fasilitas->nama_fasilitas }}</td>
                            <td>Rp {{ number_format($denda->denda_telat, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($denda->denda_rusak, 0, ',', '.') }}</td>
                            <td class="text-danger fw-bold fs-5">Rp {{ number_format($denda->total_denda, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('user.pengembalian.bayar', $denda->id) }}" class="btn btn-bayar-denda shadow-sm">
                                    <i class="fas fa-credit-card me-1"></i> Bayar Denda
                                </a>
                            </td>
                        </tr>
                        @if($denda->catatan_admin)
                        <tr>
                            <td colspan="5" class="bg-light px-3 py-1 small italic"><strong>Catatan Admin:</strong> {{ $denda->catatan_admin }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- 2. FORM PENGEMBALIAN --}}
    <div class="card-custom">
        <form action="{{ route('user.pengembalian.store') }}" method="POST" enctype="multipart/form-data" class="form-pengembalian">
            @csrf
            <div class="table-responsive">
                <table class="table-pengembalian">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="150">Tanggal Sewa</th>
                            <th>Nama Fasilitas</th>
                            <th width="80">Pilih</th>
                            <th>Sisa Bayar</th>
                            <th>Status</th>
                            <th>Upload Bukti Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($penyewaan->isEmpty())
                            <tr>
                                <td colspan="7" class="py-5 text-center text-muted">
                                    <i class="fas fa-info-circle mb-2 d-block" style="font-size: 2rem;"></i>
                                    <strong>Belum ada fasilitas yang perlu dikembalikan saat ini.</strong>
                                </td>
                            </tr>
                        @else
                            @php $nomorUrut = 1; @endphp
                            @foreach($penyewaan as $tanggal => $items)
                                @php
                                    $rowCount = $items->count();
                                @endphp
                                @foreach($items as $index => $item)
                                <tr>
                                    @if($index == 0)
                                        <td rowspan="{{ $rowCount }}">{{ $nomorUrut++ }}.</td>
                                        <td rowspan="{{ $rowCount }}" class="fw-bold">
                                            {{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMMM YYYY') }}
                                        </td>
                                    @endif

                                    <td class="fasilitas-name">{{ $item->fasilitas->nama_fasilitas }}</td>
                                    <td>
                                        <input type="checkbox" name="id_penyewaan[]" value="{{ $item->id_penyewaan }}" 
                                               class="form-check-input check-item" 
                                               {{ $item->sisa_pembayaran <= 0 ? 'checked' : 'disabled' }}>
                                    </td>
                                    <td>Rp {{ number_format($item->sisa_pembayaran, 0, ',', '.') }}</td>
                                    <td>
                                        @if($item->sisa_pembayaran <= 0)
                                            <span class="text-lunas">Lunas</span>
                                        @else
                                            <span class="text-pending">Belum Lunas</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->sisa_pembayaran <= 0)
                                            <input type="file" name="bukti_pengembalian[{{ $item->id_penyewaan }}]" class="form-control form-control-sm" required>
                                        @else
                                            <small class="text-muted"><i class="fas fa-lock me-1"></i>Selesaikan Pembayaran</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            @if(!$penyewaan->isEmpty())
                <button type="submit" class="btn-submit-group">
                    <i class="fas fa-paper-plane me-2"></i> Ajukan Pengembalian Fasilitas
                </button>
            @endif
            <div style="clear: both;"></div>
        </form>
    </div>
</div>

{{-- --- JAVASCRIPT UNTUK NOTIFIKASI --- --}}
<script>
    document.querySelectorAll('.form-pengembalian').forEach(form => {
        form.addEventListener('submit', function(e) {
            const checkedBoxes = this.querySelectorAll('.check-item:checked');
            
            // 1. Notifikasi Jika Belum Centang
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Pilihan Kosong', 
                    text: 'Silahkan centang fasilitas yang ingin dikembalikan!', 
                    confirmButtonColor: '#7c3aed' 
                });
                return;
            }

            // 2. Notifikasi Sedang Loading (Mencegah klik double)
            Swal.fire({ 
                title: 'Sedang Mengirim...', 
                text: 'Mohon tunggu, foto sedang diunggah ke sistem.', 
                allowOutsideClick: false, 
                didOpen: () => { Swal.showLoading(); } 
            });
        });
    });
</script>

{{-- 3. Notifikasi Sukses dari Controller --}}
@if(session('success'))
<script>
    Swal.fire({ 
        icon: 'success', 
        title: 'Berhasil!', 
        text: "{{ session('success') }}", 
        timer: 4000, 
        showConfirmButton: false 
    });
</script>
@endif

{{-- 4. Notifikasi Gagal dari Controller --}}
@if(session('error'))
<script>
    Swal.fire({ 
        icon: 'error', 
        title: 'Gagal!', 
        text: "{{ session('error') }}", 
        confirmButtonColor: '#7c3aed' 
    });
</script>
@endif

@endsection