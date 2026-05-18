@extends('user.layouts.app')

{{-- Bagian ini akan tampil di Topbar sebelah kiri, sejajar dengan profil --}}
@section('page_title_content')
    <h1 style="margin: 0; font-size: 30px; font-weight: 700; color: #1a202c;">
        Riwayat Penyewaan
    </h1>
@endsection

@section('content')

<div class="container"
    style="max-width: 1400px; margin: 40px auto; background: white; padding: 0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e5e7eb;">

    <div style="padding: 25px 30px 10px;">
        <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #111827;">
            Riwayat Lengkap Penyewaan
        </h2>

        <p style="margin-top: 6px; color: #6b7280; font-size: 14px;">
            Menampilkan seluruh riwayat penyewaan, pembayaran, pengembalian, dan denda.
        </p>
    </div>

    <div style="overflow-x: auto; padding: 20px 30px 30px;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0; background: white; min-width: 1200px;">

            <thead>
                <tr style="background: #1e3a8a;">

                    <th style="
                        padding: 15px 20px;
                        color: #ffffff;
                        font-weight: 600;
                        font-size: 13px;
                        text-transform: uppercase;
                        text-align: center;
                        border-radius: 8px 0 0 8px;
                    ">
                        No
                    </th>

                    <th style="
                        padding: 15px 20px;
                        color: #ffffff;
                        font-weight: 600;
                        font-size: 13px;
                        text-transform: uppercase;
                    ">
                        Fasilitas
                    </th>

                    <th style="
                        padding: 15px 20px;
                        color: #ffffff;
                        font-weight: 600;
                        font-size: 13px;
                        text-transform: uppercase;
                        text-align: center;
                    ">
                        Tanggal Pinjam
                    </th>

                    <th style="
                        padding: 15px 20px;
                        color: #ffffff;
                        font-weight: 600;
                        font-size: 13px;
                        text-transform: uppercase;
                        text-align: right;
                    ">
                        Total Harga
                    </th>

                    <th style="
                        padding: 15px 20px;
                        color: #ffffff;
                        font-weight: 600;
                        font-size: 13px;
                        text-transform: uppercase;
                        text-align: center;
                    ">
                        Status Sewa
                    </th>

                    <th style="
                        padding: 15px 20px;
                        color: #ffffff;
                        font-weight: 600;
                        font-size: 13px;
                        text-transform: uppercase;
                        text-align: center;
                    ">
                        Pembayaran
                    </th>

                    <th style="
                        padding: 15px 20px;
                        color: #ffffff;
                        font-weight: 600;
                        font-size: 13px;
                        text-transform: uppercase;
                        text-align: center;
                    ">
                        Pengembalian
                    </th>

                    <th style="
                        padding: 15px 20px;
                        color: #ffffff;
                        font-weight: 600;
                        font-size: 13px;
                        text-transform: uppercase;
                        text-align: center;
                    ">
                        Denda
                    </th>

                    <th style="
                        padding: 15px 20px;
                        color: #ffffff;
                        font-weight: 600;
                        font-size: 13px;
                        text-transform: uppercase;
                        text-align: center;
                        border-radius: 0 8px 8px 0;
                    ">
                        Status Akhir
                    </th>

                </tr>
            </thead>

            <tbody>

                @forelse($data as $group)

                @php
                    $first = $group->first();

                    $totalHargaGrup = $group->sum('total_harga');

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS PENYEWAAN
                    |--------------------------------------------------------------------------
                    */
                    $statusSewa = $first->status_sewa ?? '-';

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS PEMBAYARAN
                    |--------------------------------------------------------------------------
                    */
                    $pembayaran = $group->flatMap->pembayaran->first();

                    $statusPembayaran = 'Belum Dibayar';

                    if ($pembayaran) {
                        if (in_array($pembayaran->status_pembayaran, ['berhasil', 'diverifikasi'])) {
                            $statusPembayaran = 'Sudah Dibayar';
                        } elseif ($pembayaran->status_pembayaran == 'pending') {
                            $statusPembayaran = 'Menunggu';
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS PENGEMBALIAN
                    |--------------------------------------------------------------------------
                    */
                    $statusPengembalian = '-';

                    if ($first->pengembalian && $first->pengembalian->count() > 0) {
                        $statusPengembalian = $first->pengembalian->first()->status_validasi ?? 'menunggu';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS DENDA
                    |--------------------------------------------------------------------------
                    */
                    $allDenda = $group->flatMap->denda;

                    $totalDenda = $allDenda->sum('jumlah_denda');

                    $statusDenda = 'Tidak Ada';

                    if ($totalDenda > 0) {
                        $statusDenda = $allDenda->contains('status_pembayaran', 'lunas')
                            ? 'Lunas'
                            : 'Belum Bayar';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS AKHIR
                    |--------------------------------------------------------------------------
                    */
                    if ($statusSewa == 'batal') {

                        $statusAkhir = 'DIBATALKAN';

                    } elseif ($statusDenda == 'lunas') {

                        $statusAkhir = 'SELESAI';

                    } elseif ($statusPengembalian == 'disetujui') {

                        $statusAkhir = 'SELESAI';

                    } elseif ($statusSewa == 'selesai') {

                        $statusAkhir = 'SELESAI';

                    } else {

                        $statusAkhir = strtoupper($statusSewa);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | WARNA STATUS AKHIR
                    |--------------------------------------------------------------------------
                    */
                    if ($statusAkhir == 'SELESAI') {

                        $bgColor = '#dcfce7';
                        $textColor = '#166534';
                        $dotColor = '#22c55e';

                    } elseif ($statusAkhir == 'DIBATALKAN') {

                        $bgColor = '#fee2e2';
                        $textColor = '#991b1b';
                        $dotColor = '#ef4444';

                    } else {

                        $bgColor = '#fef3c7';
                        $textColor = '#92400e';
                        $dotColor = '#f59e0b';
                    }
                @endphp

                <tr style="
                    transition: background 0.2s;
                "
                onmouseover="this.style.backgroundColor='#f9fafb'"
                onmouseout="this.style.backgroundColor='transparent'">

                    {{-- NOMOR --}}
                    <td style="
                        padding: 20px;
                        border-bottom: 1px solid #f3f4f6;
                        text-align: center;
                        font-weight: 600;
                        color: #64748b;
                    ">
                        {{ $loop->iteration }}
                    </td>

                    {{-- FASILITAS --}}
                    <td style="
                        padding: 20px;
                        border-bottom: 1px solid #f3f4f6;
                    ">
                        <div style="
                            display: flex;
                            flex-wrap: wrap;
                            gap: 6px;
                        ">
                            @foreach($group as $item)
                                <span style="
                                    font-weight: 700;
                                    color: #111827;
                                    font-size: 13px;
                                    background: #f3f4f6;
                                    padding: 4px 10px;
                                    border-radius: 6px;
                                ">
                                    {{ $item->fasilitas->nama_fasilitas }}
                                </span>
                            @endforeach
                        </div>
                    </td>

                    {{-- TANGGAL --}}
                    <td style="
                        padding: 20px;
                        border-bottom: 1px solid #f3f4f6;
                        text-align: center;
                    ">
                        <div style="
                            font-size: 13px;
                            color: #4b5563;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 8px;
                        ">
                            <span style="color: #9ca3af;">📅</span>

                            {{ \Carbon\Carbon::parse($first->tgl_mulai)->translatedFormat('d M Y') }}
                        </div>
                    </td>

                    {{-- TOTAL --}}
                    <td style="
                        padding: 20px;
                        border-bottom: 1px solid #f3f4f6;
                        text-align: right;
                    ">
                        <div style="
                            font-weight: 700;
                            color: #111827;
                            font-size: 14px;
                        ">
                            Rp {{ number_format($totalHargaGrup, 0, ',', '.') }}
                        </div>
                    </td>

                    {{-- STATUS SEWA --}}
                    <td style="
                        padding: 20px;
                        border-bottom: 1px solid #f3f4f6;
                        text-align: center;
                    ">
                        <span style="
                            background: #dbeafe;
                            color: #1e40af;
                            padding: 6px 12px;
                            border-radius: 999px;
                            font-size: 11px;
                            font-weight: 700;
                            text-transform: uppercase;
                        ">
                            {{ $statusSewa }}
                        </span>
                    </td>

                    {{-- PEMBAYARAN --}}
                    <td style="
                        padding: 20px;
                        border-bottom: 1px solid #f3f4f6;
                        text-align: center;
                    ">
                        <span style="
                            background: #ede9fe;
                            color: #6d28d9;
                            padding: 6px 12px;
                            border-radius: 999px;
                            font-size: 11px;
                            font-weight: 700;
                            text-transform: uppercase;
                        ">
                            {{ $statusPembayaran }}
                        </span>
                    </td>

                    {{-- PENGEMBALIAN --}}
                    <td style="
                        padding: 20px;
                        border-bottom: 1px solid #f3f4f6;
                        text-align: center;
                    ">
                        <span style="
                            background: #fce7f3;
                            color: #be185d;
                            padding: 6px 12px;
                            border-radius: 999px;
                            font-size: 11px;
                            font-weight: 700;
                            text-transform: uppercase;
                        ">
                            {{ $statusPengembalian }}
                        </span>
                    </td>

                    {{-- DENDA --}}
                    <td style="
                        padding: 20px;
                        border-bottom: 1px solid #f3f4f6;
                        text-align: center;
                    ">
                        <span style="
                            background: #fff7ed;
                            color: #c2410c;
                            padding: 6px 12px;
                            border-radius: 999px;
                            font-size: 11px;
                            font-weight: 700;
                            text-transform: uppercase;
                        ">
                            @if($totalDenda > 0)
                                Rp {{ number_format($totalDenda, 0, ',', '.') }} <br>
                                <small>{{ $statusDenda }}</small>
                            @else
                                {{ $statusDenda }}
                            @endif
                        </span>
                    </td>

                    {{-- STATUS AKHIR --}}
                    <td style="
                        padding: 20px;
                        border-bottom: 1px solid #f3f4f6;
                        text-align: center;
                    ">
                        <span style="
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 6px 14px;
                            border-radius: 9999px;
                            font-size: 11px;
                            font-weight: 800;
                            text-transform: uppercase;
                            background: {{ $bgColor }};
                            color: {{ $textColor }};
                            border: 1px solid rgba(0,0,0,0.05);
                        ">

                            <span style="
                                width: 6px;
                                height: 6px;
                                border-radius: 50%;
                                background: {{ $dotColor }};
                            "></span>

                            {{ $statusAkhir }}

                        </span>
                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="9" style="
                        padding: 50px;
                        text-align: center;
                        color: #9ca3af;
                        font-style: italic;
                    ">
                        Tidak ada riwayat penyewaan yang ditemukan.
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>
    </div>
</div>

@endsection