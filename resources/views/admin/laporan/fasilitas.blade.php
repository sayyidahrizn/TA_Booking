@extends('admin.layout')

@section('title', 'Laporan Fasilitas')
@section('page-title', 'Laporan Fasilitas')

@section('content')
<div class="report-container" style="padding: 1.5rem;">

    <div class="filter-card" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); margin-bottom: 1.5rem;">
        <form action="{{ route('admin.laporan.fasilitas') }}" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="font-weight: 600; font-size: 0.875rem; color: #475569;">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" style="padding: 0.5rem 1rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
            </div>
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="font-weight: 600; font-size: 0.875rem; color: #475569;">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" style="padding: 0.5rem 1rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
            </div>
            
            <div class="form-actions" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="submit" name="action" value="filter" style="background: #2563eb; color: white; padding: 0.5rem 1.25rem; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; transition: 0.2s;">
                    Filter
                </button>
                <a href="{{ route('admin.laporan.fasilitas') }}" style="background: #64748b; color: white; padding: 0.5rem 1.25rem; border: none; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 0.9rem; text-align: center; transition: 0.2s;">
                    Reset
                </a>

                <button type="submit" name="action" value="pdf" style="background: #dc2626; color: white; padding: 0.5rem 1.25rem; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; transition: 0.2s;">
                    Cetak PDF
                </button>
                <button type="submit" name="action" value="excel" style="background: #16a34a; color: white; padding: 0.5rem 1.25rem; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; transition: 0.2s;">
                    Export Excel
                </button>
            </div>
        </form>
    </div>

    <div class="table-card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
        <div style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0 0 0.25rem 0;">Fasilitas Paling Sering Dipinjam</h2>
            <p style="color: #64748b; margin: 0; font-size: 0.95rem;">
                Periode: 
                <span style="font-weight: 600; color: #334155;">
                    @if($startDate && $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                    @else
                        Semua Waktu
                    @endif
                </span>
            </p>
        </div>

        <div style="overflow-x: auto;">
            <table class="table-modern" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                <thead>
                    <tr style="background-color: #0f172a; color: white;">
                        <th style="padding: 1rem; border-top-left-radius: 8px; border-bottom-left-radius: 8px; width: 80px; text-align: center;">No</th>
                        <th style="padding: 1rem;">Nama Fasilitas</th>
                        <th style="padding: 1rem; border-top-right-radius: 8px; border-bottom-right-radius: 8px; width: 250px; text-align: center;">Total Dipinjam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataFasilitas as $i => $item)
                        <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 1rem; text-align: center; font-weight: 500; color: #64748b;">{{ $dataFasilitas->firstItem() + $i }}</td>
                            <td style="padding: 1rem; font-weight: 600; color: #334155;">
                                {{ $item->nama_fasilitas }}
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <span style="background-color: #dbeafe; color: #1e40af; padding: 0.35rem 0.75rem; border-radius: 9999px; font-weight: 700; font-size: 0.875rem;">
                                    {{ $item->total_peminjaman }} kali
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding: 3rem; text-align: center; color: #94a3b8; font-style: italic;">
                                Tidak ada data fasilitas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 2rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem;">
            
            <div style="color: #64748b; font-size: 0.85rem;">
                Showing {{ $dataFasilitas->firstItem() ?? 0 }} to {{ $dataFasilitas->lastItem() ?? 0 }} of {{ $dataFasilitas->total() }} results
            </div>

            @if($dataFasilitas->hasPages())
                <div class="custom-pagination-box" style="display: flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; background: #fff;">
                    
                    {{-- Tombol Sebelumnya --}}
                    @if($dataFasilitas->onFirstPage())
                        <span class="page-node disabled">&lsaquo;</span>
                    @else
                        <a href="{{ $dataFasilitas->previousPageUrl() }}" class="page-node">&lsaquo;</a>
                    @endif

                    {{-- Urutan Angka Halaman --}}
                    @foreach($dataFasilitas->getUrlRange(1, $dataFasilitas->lastPage()) as $page => $url)
                        @if($page == $dataFasilitas->currentPage())
                            <span class="page-node active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-node">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Tombol Selanjutnya --}}
                    @if($dataFasilitas->hasMorePages())
                        <a href="{{ $dataFasilitas->nextPageUrl() }}" class="page-node">&rsaquo;</a>
                    @else
                        <span class="page-node disabled">&rsaquo;</span>
                    @endif

                </div>
            @endif
        </div>

    </div>
</div>

{{-- Style Pengendali Struktur Pagination Kotak Menengah --}}
<style>
    .custom-pagination-box .page-node {
        display: inline-block;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #334155;
        text-decoration: none;
        border-right: 1px solid #e2e8f0;
        background: #ffffff;
        transition: all 0.2s;
    }
    .custom-pagination-box .page-node:last-child {
        border-right: none;
    }
    .custom-pagination-box a.page-node:hover {
        background-color: #f1f5f9;
        color: #2563eb;
    }
    .custom-pagination-box .page-node.active {
        background-color: #0f172a; /* Menyamakan warna background gelap card menu kamu */
        color: #ffffff;
        font-weight: 600;
    }
    .custom-pagination-box .page-node.disabled {
        color: #cbd5e1;
        background-color: #f8fafc;
        cursor: not-allowed;
    }
</style>
@endsection