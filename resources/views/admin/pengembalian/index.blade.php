@extends('admin.layout')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    .admin-wrapper { padding: 1.5rem; }
    .table-container { 
        background: #ffffff; border-radius: 12px; border: 1px solid #000; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; margin-bottom: 2.5rem; 
    }
    .custom-table { width: 100%; border-collapse: collapse; }
    .custom-table th, .custom-table td { border: 1px solid #000; padding: 12px; text-align: center; vertical-align: middle; }
    .custom-table th { background-color: #f1f5f9; color: #475569; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; }
    .nama-utama { font-weight: 700; color: #1e293b; text-align: left !important; }
    .img-preview { width: 55px; height: 55px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
    .input-sm { border: 1px solid #cbd5e1; padding: 6px; border-radius: 4px; width: 100%; font-size: 0.8rem; }
    .btn-submit { 
        background: #7c3aed; color: white; border: none; padding: 10px 20px; 
        border-radius: 8px; font-weight: 600; cursor: pointer; float: right; margin: 15px;
    }
    .btn-submit:hover { background: #6d28d9; }
    .badge-telat { background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.75rem; }
    .badge-tepat { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.75rem; }
    .tagihan-live { font-size: 0.7rem; font-weight: 800; color: #7c3aed; margin-top: 4px; display: block; }
</style>

<div class="admin-wrapper">
    <div class="mb-4">
        <h1 style="font-weight: 800; color: #1e293b;">Validasi Pengembalian</h1>
    </div>

    @forelse($data as $tanggal => $items)
    @php $rowCount = count($items); @endphp
    <div class="table-container">
        <form action="{{ route('admin.pengembalian.validasi') }}" method="POST">
            @csrf
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th style="width: 150px;">TANGGAL PENYEWAAN</th>
                        <th>NAMA PENYEWA</th>
                        <th>NAMA FASILITAS</th>
                        <th style="width: 80px;">FOTO</th>
                        <th>DEADLINE</th>
                        <th>TGL KEMBALI</th>
                        <th>DENDA TELAT</th>
                        <th style="width: 160px;">DENDA RUSAK</th>
                        <th>CATATAN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                    <tr>
                        @if($loop->first)
                            <td rowspan="{{ $rowCount }}">{{ $loop->parent->iteration }}.</td>
                            <td rowspan="{{ $rowCount }}">
                                <strong>{{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</strong>
                            </td>
                        @endif

                        <td>{{ $item->penyewaan->user->name ?? 'User' }}</td>
                        <td class="nama-utama">{{ $item->penyewaan->fasilitas->nama_fasilitas }}</td>
                        <td>
                            <a href="{{ asset('storage/'.$item->bukti_pengembalian) }}" target="_blank">
                                <img src="{{ asset('storage/'.$item->bukti_pengembalian) }}" class="img-preview">
                            </a>
                        </td>
                        <td><span class="small">{{ \Carbon\Carbon::parse($item->penyewaan->tgl_selesai)->format('d/m/Y') }}</span></td>
                        <td><span class="small">{{ \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d/m/Y') }}</span></td>
                        
                        <td>
                            @if(isset($item->denda_telat_otomatis) && $item->denda_telat_otomatis > 0)
                                <div class="badge-telat">Rp {{ number_format($item->denda_telat_otomatis, 0, ',', '.') }}</div>
                                <div style="font-size: 0.7rem; color: #dc2626;">Telat {{ $item->hari_telat }} Hari</div>
                            @else
                                <div class="badge-tepat">Rp 0</div>
                                <div style="font-size: 0.7rem; color: #16a34a;">Tepat Waktu</div>
                            @endif
                        </td>

                        <td>
                            <div style="display:flex; flex-direction: column; align-items:center;">
                                <div style="display:flex; align-items:center; width: 100%;">
                                    <span style="margin-right:4px; font-size:0.7rem;">Rp</span>
                                    <input type="text" 
                                           class="input-sm input-denda-mask" 
                                           placeholder="0" 
                                           onkeyup="formatInputRupiah(this, 'denda_real_{{ $item->id }}'); hitungTotalTagihan({{ $item->id }}, {{ $item->denda_telat_otomatis }})">
                                    <input type="hidden" name="denda_rusak[{{ $item->id }}]" id="denda_real_{{ $item->id }}" value="0">
                                </div>
                                <span class="tagihan-live" id="total_tagihan_{{ $item->id }}">
                                    Tagihan: Rp {{ number_format($item->denda_telat_otomatis, 0, ',', '.') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="catatan_admin[{{ $item->id }}]" class="input-sm" placeholder="Catatan kerusakan...">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn-submit">Selesaikan Validasi & Kirim Tagihan</button>
            <div style="clear: both;"></div>
        </form>
    </div>
    @empty
        <div class="table-container p-5 text-center text-muted">
            Belum ada pengajuan pengembalian untuk divalidasi.
        </div>
    @endforelse
</div>

<script>
    function formatInputRupiah(element, targetId) {
        let value = element.value.replace(/[^0-9]/g, '');
        document.getElementById(targetId).value = value || 0;
        if (value) {
            element.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            element.value = '';
        }
    }

    function hitungTotalTagihan(id, dendaTelat) {
        let dendaRusak = parseInt(document.getElementById('denda_real_' + id).value) || 0;
        let total = dendaTelat + dendaRusak;
        document.getElementById('total_tagihan_' + id).innerText = 'Tagihan: Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }
</script>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", showConfirmButton: false, timer: 2500 });
</script>
@endif
@endsection