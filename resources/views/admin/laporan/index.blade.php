@extends('admin.layout')

@section('title', 'Laporan')
@section('page-title', 'Laporan Sistem')

@section('content')
<style>
    /* Menggunakan CSS Variables untuk konsistensi */
    :root {
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --bg-main: #f8fafc;
        --text-dark: #0f172a;
        --text-light: #64748b;
        --white: #ffffff;
        --border: #e2e8f0;
    }

    * { box-sizing: border-box; }

    .report-container {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background: var(--bg-main);
        padding: 10px;
    }

    /* Card Styling */
    .filter-card, .table-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.05);
        padding: 28px;
        margin-bottom: 30px;
    }

    /* Form & Input */
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        align-items: end;
    }

    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group label {
        font-size: .8rem;
        font-weight: 600;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .input-modern {
        height: 48px;
        padding: 0 16px;
        background: #f1f5f9;
        border: 2px solid transparent;
        border-radius: 12px;
        font-size: .95rem;
        color: var(--text-dark);
        transition: all .2s;
    }

    .input-modern:focus {
        background: var(--white);
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 4px rgba(79,70,229,.1);
    }

    /* Buttons */
    .btn-group {
        display: flex;
        gap: 12px;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
        flex-wrap: wrap;
    }

    .btn-action {
        height: 48px;
        padding: 0 24px;
        border-radius: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: .2s;
        cursor: pointer;
        border: none;
        font-size: .9rem;
        text-decoration: none;
    }

    .btn-submit { background: var(--primary); color: white; }
    .btn-submit:hover { background: var(--primary-hover); transform: translateY(-2px); }

    .btn-export { background: white; border: 1.5px solid var(--border); color: var(--text-dark); }
    .btn-export:hover { background: #f8fafc; border-color: var(--text-light); transform: translateY(-2px); }

    /* Modern Table */
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .table-modern { width: 100%; border-collapse: collapse; min-width: 900px; }
    .table-modern th {
        padding: 15px 20px;
        color: var(--text-light);
        font-weight: 700;
        font-size: .85rem;
        text-transform: uppercase;
        border-bottom: 2px solid var(--border);
        text-align: left;
    }

    .table-modern td {
        padding: 18px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: .95rem;
        color: var(--text-dark);
        vertical-align: middle;
    }

    .table-modern tr:hover td { background: #f8fafc; }

    /* Badge & Utility */
    .badge {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-success { background: #dcfce7; color: #166534; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-danger  { background: #fee2e2; color: #991b1b; }
    .status-info    { background: #dbeafe; color: #1d4ed8; }

    .count-pill {
        background: #eef2ff;
        color: var(--primary);
        padding: 8px 16px;
        border-radius: 999px;
        font-weight: 700;
    }

    .empty-state { text-align: center; padding: 50px; }
    .empty-state img { width: 140px; opacity: .6; margin-bottom: 15px; }

    /* Pagination Styling */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
        flex-wrap: wrap;
        gap: 15px;
    }

    .pagination-nav { display: flex; gap: 5px; list-style: none; padding: 0; margin: 0; }
    .pagination-nav li a, .pagination-nav li span {
        display: flex; align-items: center; justify-content: center;
        min-width: 38px; height: 38px; border-radius: 10px;
        border: 1px solid var(--border); background: white;
        color: var(--primary); text-decoration: none; font-weight: 600;
    }

    .pagination-nav li.active span { background: var(--primary); color: white; border-color: var(--primary); }
    .pagination-nav li.disabled span { color: #cbd5e1; cursor: not-allowed; }

    @media(max-width:768px){
        .filter-grid { grid-template-columns: 1fr; }
        .btn-action { width: 100%; }
        .table-header { flex-direction: column; }
    }
</style>

<div class="report-container">
    <!-- SECTION FILTER -->
    <div class="filter-card">
        <form action="{{ route('admin.laporan') }}" method="GET">
            <div class="filter-grid">
                <div class="form-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="start_date" class="input-modern" value="{{ request('start_date') }}">
                </div>

                <div class="form-group">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="end_date" class="input-modern" value="{{ request('end_date') }}">
                </div>

                <div class="form-group">
                    <label>Kategori Laporan</label>
                    <select name="jenis" class="input-modern" onchange="this.form.submit()">
                        <option value="semua" {{ request('jenis') == 'semua' || !request('jenis') ? 'selected' : '' }}>Semua Laporan</option>
                        <option value="penyewaan" {{ request('jenis') == 'penyewaan' ? 'selected' : '' }}>Penyewaan</option>
                        <option value="pembayaran" {{ request('jenis') == 'pembayaran' ? 'selected' : '' }}>Pembayaran</option>
                        <option value="pengembalian" {{ request('jenis') == 'pengembalian' ? 'selected' : '' }}>Pengembalian</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Filter Status</label>
                    <select name="status" class="input-modern">
                        <option value="">Semua Status</option>
                        @if(request('jenis') == 'penyewaan')
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        @elseif(request('jenis') == 'pembayaran')
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="batal" {{ request('status') == 'batal' ? 'selected' : '' }}>Batal</option>
                        @elseif(request('jenis') == 'pengembalian')
                            <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Kembali</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-action btn-submit">
                    <i class="fas fa-filter"></i> Terapkan Filter
                </button>
                <a href="{{ route('admin.laporan.pdf', request()->all()) }}" class="btn-action btn-export">
                    <i class="fas fa-file-pdf text-danger"></i> Export PDF
                </a>
                <a href="{{ route('admin.laporan.excel', request()->all()) }}" class="btn-action btn-export">
                    <i class="fas fa-file-excel text-success"></i> Export Excel
                </a>
            </div>
        </form>
    </div>

    <!-- SECTION DATA TABLE -->
    <div class="table-card">
        <div class="table-header">
            <div>
                @php
                    $judul = match(request('jenis')) {
                        'penyewaan'    => 'Data Penyewaan Fasilitas',
                        'pembayaran'   => 'Rekapitulasi Pembayaran',
                        'pengembalian' => 'Status Pengembalian Fasilitas',
                        default        => 'Semua Data Laporan'
                    };
                @endphp
                <h2 style="font-size:1.5rem; font-weight:800; color:var(--text-dark); margin:0;">{{ $judul }}</h2>
                <p style="color:var(--text-light); font-size:.9rem; margin-top:6px;">
                    @if(request('start_date') && request('end_date'))
                        Periode: <strong>{{ \Carbon\Carbon::parse(request('start_date'))->translatedFormat('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse(request('end_date'))->translatedFormat('d M Y') }}</strong>
                    @else
                        Menampilkan seluruh data laporan
                    @endif
                </p>
            </div>
            <div class="count-pill">
                Total {{ $detailLaporan->total() }} Data
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th width="60">No</th>
                        <th>Kode Booking</th>
                        <th>Penyewa</th>
                        <th>Fasilitas</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailLaporan as $item)
                        <tr>
                            <td>{{ ($detailLaporan->currentPage() - 1) * $detailLaporan->perPage() + $loop->iteration }}</td>
                            <td>
                                <span style="font-family:monospace; font-weight:700; background:#f1f5f9; padding:4px 8px; border-radius:6px; color:var(--primary);">
                                    #{{ $item->kode_booking }}
                                </span>
                            </td>
                            <td><strong>{{ $item->user->name ?? '-' }}</strong></td>
                            <td>
                                {{ $item->fasilitas->nama_fasilitas ?? '-' }}
                                <small style="color:var(--text-light);">(x{{ $item->jumlah_sewa }})</small>
                            </td>
                            <td style="font-weight:700;">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $statusClass = 'status-pending';
                                    $statusText = '';

                                    if(request('jenis') == 'penyewaan') {
                                        $statusText = $item->status_sewa;
                                        $statusClass = $item->status_sewa == 'disetujui' ? 'status-success' : ($item->status_sewa == 'ditolak' ? 'status-danger' : 'status-pending');
                                    } elseif(request('jenis') == 'pembayaran') {
                                        $statusText = $item->status_pembayaran;
                                        $statusClass = $item->status_pembayaran == 'lunas' ? 'status-success' : ($item->status_pembayaran == 'batal' ? 'status-danger' : 'status-pending');
                                    } elseif(request('jenis') == 'pengembalian') {
                                        $statusText = $item->status_pengembalian;
                                        $statusClass = $item->status_pengembalian == 'selesai' ? 'status-info' : 'status-pending';
                                    } else {
                                        $statusText = 'Sewa: ' . ucfirst($item->status_sewa);
                                        $statusClass = 'status-info';
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                            </td>
                            <td>
                                {{ $item->created_at->translatedFormat('d/m/Y') }}<br>
                                <small style="color:var(--text-light);">{{ $item->created_at->format('H:i') }} WIB</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <img src="https://illustrations.popsy.co/flat/searching.svg" alt="Empty">
                                    <p>Data laporan tidak ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="pagination-container">
            <div class="pagination-info">
                Menampilkan <strong>{{ $detailLaporan->firstItem() ?? 0 }}</strong> - <strong>{{ $detailLaporan->lastItem() ?? 0 }}</strong> dari <strong>{{ $detailLaporan->total() }}</strong> data
            </div>

            @if($detailLaporan->hasPages())
                <ul class="pagination-nav">
                    {{-- Previous Page Link --}}
                    @if($detailLaporan->onFirstPage())
                        <li class="disabled"><span><i class="fas fa-chevron-left"></i></span></li>
                    @else
                        <li><a href="{{ $detailLaporan->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a></li>
                    @endif

                    {{-- Page Number Links --}}
                    @foreach($detailLaporan->getUrlRange(max(1, $detailLaporan->currentPage() - 2), min($detailLaporan->lastPage(), $detailLaporan->currentPage() + 2)) as $page => $url)
                        <li class="{{ $page == $detailLaporan->currentPage() ? 'active' : '' }}">
                            @if($page == $detailLaporan->currentPage())
                                <span>{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach

                    {{-- Next Page Link --}}
                    @if($detailLaporan->hasMorePages())
                        <li><a href="{{ $detailLaporan->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a></li>
                    @else
                        <li class="disabled"><span><i class="fas fa-chevron-right"></i></span></li>
                    @endif
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection