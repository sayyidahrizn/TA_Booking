@extends('admin.layout')

{{-- Mengirim judul ke header yang ada di layout admin --}}
@section('page-title', 'Validasi Pengembalian')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Styling khusus konten agar lebih modern dan clean */
    .admin-wrapper-inner { font-family: 'Inter', sans-serif; }

    .table-container { 
        background: #ffffff; 
        border-radius: 12px; 
        border: 1px solid #e2e8f0; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); 
        overflow: hidden; 
        margin-bottom: 2rem; 
    }
    
    .table-responsive { 
        width: 100%; 
        overflow-x: auto; 
        -webkit-overflow-scrolling: touch; 
    }
    
    .custom-table { 
        width: 100%; 
        border-collapse: collapse; 
        min-width: 1050px; 
    }

    .custom-table th, .custom-table td { 
        border: 1px solid #f1f5f9; 
        padding: 14px 12px; 
        text-align: center; 
        vertical-align: middle; 
    }
    
    /* Header Tabel Modern */
    .custom-table th { 
        background-color: #f8fafc; 
        color: #64748b; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        font-weight: 700; 
        letter-spacing: 0.05em; 
    }
    
    .nama-utama { font-weight: 700; color: #1e293b; text-align: left !important; }
    
    .img-preview { 
        width: 48px; 
        height: 48px; 
        object-fit: cover; 
        border-radius: 8px; 
        border: 1px solid #e2e8f0; 
        transition: transform 0.2s;
    }
    .img-preview:hover { transform: scale(1.1); }
    
    .input-sm { 
        border: 1px solid #cbd5e1; 
        padding: 8px 12px; 
        border-radius: 8px; 
        width: 100%; 
        font-size: 0.85rem; 
        outline: none; 
        transition: all 0.2s; 
    }
    .input-sm:focus { 
        border-color: #7c3aed; 
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1); 
    }

    /* Tombol Sesuai Warna Dashboard */
    .btn-submit { 
        background: #7c3aed; 
        color: white; 
        border: none; 
        padding: 12px 24px; 
        border-radius: 8px; 
        font-weight: 600; 
        cursor: pointer; 
        float: right; 
        margin: 15px;
        transition: all 0.3s;
    }
    .btn-submit:hover { 
        background: #6d28d9; 
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
    }

    .badge-telat { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; display: inline-block; }
    .badge-tepat { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; display: inline-block; }
    
    .tagihan-live { 
        font-size: 0.75rem; 
        font-weight: 800; 
        color: #7c3aed; 
        margin-top: 6px; 
        display: block; 
    }

    .small-text { font-size: 0.8rem; color: #64748b; }
</style>

<div class="admin-wrapper-inner">
    @forelse($data as $tanggal => $items)
    @php $rowCount = count($items); @endphp
    <div class="table-container">
        <form action="{{ route('admin.pengembalian.validasi') }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th style="width: 160px;">TANGGAL PENYEWAAN</th>
                            <th>NAMA PENYEWA</th>
                            <th>NAMA FASILITAS</th>
                            <th style="width: 70px;">FOTO</th>
                            <th>DEADLINE</th>
                            <th>TGL KEMBALI</th>
                            <th>DENDA TELAT</th>
                            <th style="width: 180px;">DENDA RUSAK</th>
                            <th>CATATAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                        <tr>
                            {{-- Grouping Baris berdasarkan Tanggal --}}
                            @if($loop->first)
                                <td rowspan="{{ $rowCount }}" style="background: #fcfcfc; font-weight: 600; color: #64748b;">
                                    {{ $loop->parent->iteration }}.
                                </td>
                                <td rowspan="{{ $rowCount }}" style="background: #fcfcfc;">
                                    <strong style="color: #1e293b; font-size: 0.9rem;">
                                        {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                                    </strong>
                                </td>
                            @endif

                            <td class="small-text">{{ $item->penyewaan->user->name ?? 'User' }}</td>
                            <td class="nama-utama">{{ $item->penyewaan->fasilitas->nama_fasilitas }}</td>
                            <td>
                                <a href="{{ asset('storage/'.$item->bukti_pengembalian) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$item->bukti_pengembalian) }}" class="img-preview" title="Klik untuk lihat foto asli">
                                </a>
                            </td>
                            <td class="small-text">{{ \Carbon\Carbon::parse($item->penyewaan->tgl_selesai)->format('d/m/Y') }}</td>
                            <td class="small-text">{{ \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d/m/Y') }}</td>
                            
                            <td>
                                @if(isset($item->denda_telat_otomatis) && $item->denda_telat_otomatis > 0)
                                    <div class="badge-telat">Rp {{ number_format($item->denda_telat_otomatis, 0, ',', '.') }}</div>
                                    <div style="font-size: 0.65rem; color: #dc2626; margin-top:4px;">Telat {{ $item->hari_telat }} Hari</div>
                                @else
                                    <div class="badge-tepat">Rp 0</div>
                                    <div style="font-size: 0.65rem; color: #16a34a; margin-top:4px;">Tepat Waktu</div>
                                @endif
                            </td>

                            <td>
                                <div style="display:flex; flex-direction: column; align-items:center; gap: 4px;">
                                    <div style="display:flex; align-items:center; width: 100%;">
                                        <span style="margin-right:6px; font-size:0.75rem; font-weight:bold; color:#94a3b8;">Rp</span>
                                        <input type="text" 
                                               class="input-sm" 
                                               placeholder="0" 
                                               onkeyup="formatInputRupiah(this, 'denda_real_{{ $item->id }}'); hitungTotalTagihan({{ $item->id }}, {{ $item->denda_telat_otomatis ?? 0 }})">
                                        
                                        {{-- Hidden input untuk value murni angka ke database --}}
                                        <input type="hidden" name="denda_rusak[{{ $item->id }}]" id="denda_real_{{ $item->id }}" value="0">
                                    </div>
                                    <span class="tagihan-live" id="total_tagihan_{{ $item->id }}">
                                        Tagihan: Rp {{ number_format($item->denda_telat_otomatis ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="catatan_admin[{{ $item->id }}]" class="input-sm" placeholder="Contoh: Meja lecet...">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn-submit">
                <i class="fas fa-check-circle" style="margin-right: 5px;"></i> Selesaikan & Kirim Tagihan
            </button>
            <div style="clear: both;"></div>
        </form>
    </div>
    @empty
        <div class="card p-5 text-center" style="color: #64748b; border: 1px solid #e2e8f0; border-radius: 12px;">
            <i class="fas fa-clipboard-check" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>Tidak ada data pengembalian yang perlu divalidasi saat ini.</p>
        </div>
    @endforelse
</div>

<script>
    /**
     * Format input angka menjadi ribuan secara real-time (id-ID)
     */
    function formatInputRupiah(element, targetId) {
        let value = element.value.replace(/[^0-9]/g, '');
        document.getElementById(targetId).value = value || 0;
        
        if (value) {
            element.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            element.value = '';
        }
    }

    /**
     * Kalkulasi live: Denda Telat (Otomatis) + Denda Rusak (Input Manual)
     */
    function hitungTotalTagihan(id, dendaTelat) {
        let dendaRusak = parseInt(document.getElementById('denda_real_' + id).value) || 0;
        let total = parseInt(dendaTelat) + dendaRusak;
        document.getElementById('total_tagihan_' + id).innerText = 'Tagihan: Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }
</script>

{{-- Notifikasi Sukses via SweetAlert2 --}}
@if(session('success'))
<script>
    Swal.fire({ 
        icon: 'success', 
        title: 'Berhasil!', 
        text: "{{ session('success') }}", 
        showConfirmButton: false, 
        timer: 2500,
        timerProgressBar: true 
    });
</script>
@endif

@endsection