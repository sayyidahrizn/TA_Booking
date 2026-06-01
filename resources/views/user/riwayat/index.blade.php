@extends('user.layouts.app')

@section('page_title_content')
    <h1 class="page-main-title">Riwayat Penyewaan</h1>
@endsection

@section('content')
<style>
    .history-container {
        max-width: 1400px;
        margin: 40px auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .history-header {
        padding: 25px 30px;
        border-bottom: 1px solid #f3f4f6;
    }
    .history-header h2 { margin: 0; font-size: 20px; font-weight: 700; color: #111827; }
    .history-header p { margin: 6px 0 0; color: #6b7280; font-size: 14px; }

    .table-responsive { overflow-x: auto; padding: 20px 30px 30px; }
    
    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 1200px;
    }
    .custom-table thead tr { background: #1e3a8a; }
    .custom-table th {
        padding: 15px 20px;
        color: #ffffff;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        text-align: center;
    }
    .custom-table th:first-child { border-radius: 8px 0 0 8px; }
    .custom-table th:last-child { border-radius: 0 8px 8px 0; }

    .custom-table td {
        padding: 18px 15px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 13px;
        color: #4b5563;
    }

    /* Badges & Status */
    .badge-status {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .facility-tag {
        background: #f3f4f6;
        color: #111827;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
    }
    .status-dot { width: 6px; height: 6px; border-radius: 50%; }

    .row-hover:hover { background-color: #f9fafb; transition: 0.2s; }
</style>

<div class="history-container">
    <div class="history-header">
        <h2>Riwayat Lengkap Penyewaan</h2>
        <p>Kelola dan pantau seluruh riwayat transaksi, pengembalian, dan denda Anda.</p>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th style="text-align: left;">Fasilitas</th>
                    <th>Tgl Pinjam</th>
                    <th style="text-align: right;">Total Harga</th>
                    <th>Status Sewa</th>
                    <th>Pembayaran</th>
                    <th>Pengembalian</th>
                    <th>Denda</th>
                    <th>Status Akhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $group)
                    @php
                        $first = $group->first();
                        $totalHargaGrup = $group->sum('total_harga');

                        // Logika Pembayaran
                        $allPembayaran = $group->flatMap->pembayaran;

                        if ($allPembayaran->count() > 0) {

                            $hasBerhasil = $allPembayaran->contains(function ($item) {
                                return strtolower($item->status_pembayaran) == 'berhasil';
                            });

                            $hasPelunasan = $allPembayaran->contains(function ($item) {
                                return strtolower($item->jenis_pembayaran) == 'pelunasan'
                                    && strtolower($item->status_pembayaran) == 'berhasil';
                            });

                            $hasDp = $allPembayaran->contains(function ($item) {
                                return strtolower($item->jenis_pembayaran) == 'dp'
                                    && strtolower($item->status_pembayaran) == 'berhasil';
                            });

                            if ($hasPelunasan) {

                                $jenisPembayaran = 'LUNAS';

                            } elseif ($hasDp) {

                                $jenisPembayaran = 'DP';

                            } elseif (!$hasBerhasil) {

                                $jenisPembayaran = 'BELUM DIBAYAR';

                            } else {

                                $jenisPembayaran = '-';
                            }

                        } else {

                            $jenisPembayaran = 'BELUM DIBAYAR';
                        };

                        // Logika Pengembalian
                        $statusPengembalian = ($first->pengembalian && $first->pengembalian->count() > 0) 
                            ? ($first->pengembalian->first()->status_validasi ?? 'menunggu') 
                            : '-';

                        // Logika Denda
                        $allDenda = $group->flatMap->denda;
                        $totalDenda = $allDenda->sum('total_denda');
                        $statusDenda = $totalDenda > 0 
                            ? ($allDenda->every(fn($d) => strtolower($d->status_denda) == 'lunas') ? 'Lunas' : 'Belum Bayar')
                            : 'Tidak Ada';

                        // Logika Status Sewa & Akhir
                        $semuaStatusSewa = $group->pluck('status_sewa')->filter()->unique();
                        
                        // Penentuan Status Terpadu
                        if ($statusDenda == 'Lunas' || $statusPengembalian == 'disetujui') {
                            $statusSewa = 'selesai';
                        } else {
                            $statusSewa = $semuaStatusSewa->contains('menunggu_pembayaran_denda') ? 'menunggu_pembayaran_denda' :
                                         ($semuaStatusSewa->contains('dipinjam') ? 'dipinjam' :
                                         ($semuaStatusSewa->contains('disetujui') ? 'disetujui' :
                                         ($semuaStatusSewa->contains('batal') ? 'batal' : ($semuaStatusSewa->first() ?? '-'))));
                        }

                        $statusAkhir = ($statusSewa == 'batal') ? 'DIBATALKAN' : (($statusSewa == 'selesai') ? 'SELESAI' : strtoupper($statusSewa));

                        // Warna Berdasarkan Status Akhir
                        $statusMeta = [
                            'SELESAI' => ['bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#22c55e'],
                            'DIBATALKAN' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'dot' => '#ef4444'],
                        ][$statusAkhir] ?? ['bg' => '#fef3c7', 'text' => '#92400e', 'dot' => '#f59e0b'];
                    @endphp

                    <tr class="row-hover">
                        <td style="text-align: center; font-weight: 600; color: #64748b;">{{ $loop->iteration }}</td>
                        
                        <td style="text-align: left;">
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; max-width: 250px;">
                                @foreach($group as $item)
                                    <span class="facility-tag">{{ $item->fasilitas->nama_fasilitas }}</span>
                                @endforeach
                            </div>
                        </td>

                        <td style="text-align: center;">
                            <span style="color: #9ca3af; margin-right: 4px;">📅</span>
                            {{ \Carbon\Carbon::parse($first->tgl_mulai)->translatedFormat('d M Y') }}
                        </td>

                        <td style="text-align: right; font-weight: 700; color: #111827;">
                            Rp {{ number_format($totalHargaGrup, 0, ',', '.') }}
                        </td>

                        <td style="text-align: center;">
                            <span class="badge-status" style="background: #dbeafe; color: #1e40af;">
                                {{ str_replace('_', ' ', $statusSewa) }}
                            </span>
                        </td>

                        <td style="text-align: center;">
                            <span class="badge-status" style="background: #ede9fe; color: #6d28d9;">
                                {{ $jenisPembayaran }}
                            </span>
                        </td>

                        <td style="text-align: center;">
                            <span class="badge-status" style="background: #fce7f3; color: #be185d;">
                                {{ $statusPengembalian }}
                            </span>
                        </td>

                        <td style="text-align: center;">
                            @if($totalDenda > 0)
                                <div style="font-size: 13px; font-weight: 700;">
                                    <span style="color: #c2410c;">Rp {{ number_format($totalDenda, 0, ',', '.') }}</span>
                                    <small style="display: block; color: {{ strtolower($statusDenda) == 'lunas' ? '#15803d' : '#dc2626' }};">
                                        {{ $statusDenda }}
                                    </small>
                                </div>
                            @else
                                <span style="color: #9ca3af; font-size: 12px;">-</span>
                            @endif
                        </td>

                        <td style="text-align: center;">
                            <span class="badge-status" style="background: {{ $statusMeta['bg'] }}; color: {{ $statusMeta['text'] }}; border: 1px solid rgba(0,0,0,0.05);">
                                <span class="status-dot" style="background: {{ $statusMeta['dot'] }};"></span>
                                {{ $statusAkhir }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding: 50px; text-align: center; color: #9ca3af; font-style: italic;">
                            Belum ada riwayat penyewaan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection