<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use App\Models\Fasilitas;
use App\Models\User;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PenyewaanController extends Controller
{
    /**
     * VERIFIKASI PEMBAYARAN MANUAL
     */
    public function verifikasiPembayaran(Request $request, $id_pembayaran)
    {
        // Validasi input nominal
        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1'
        ]);

        DB::beginTransaction();
        try {
            $pembayaran = Pembayaran::with('penyewaan')->findOrFail($id_pembayaran);
            $totalHarga = $pembayaran->penyewaan->total_harga;
            $inputBayar = $request->jumlah_bayar;

            // 1. Update data pembayaran yang sedang diverifikasi
            $pembayaran->update([
                'status_pembayaran' => 'diverifikasi',
                'tanggal_bayar' => now(),
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar' => $inputBayar,
                'jenis_pembayaran' => ($inputBayar < $totalHarga) ? 'dp' : 'pelunasan'
            ]);

            // 2. Jika bayarnya belum lunas (DP), buatkan record pembayaran baru untuk sisanya
            if ($inputBayar < $totalHarga) {
                Pembayaran::create([
                    'id_penyewaan' => $pembayaran->id_penyewaan,
                    'kode_pembayaran' => 'PAY-' . time() . '-SISA',
                    'jenis_pembayaran' => 'pelunasan',
                    'metode_pembayaran' => 'tunai',
                    'jumlah_bayar' => 0,
                    'status_pembayaran' => 'pending',
                ]);
            }

            DB::commit();
            return back()->with('success', 'Pembayaran tunai sebesar Rp ' . number_format($inputBayar) . ' berhasil diverifikasi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal verifikasi: ' . $e->getMessage());
        }
    }

    /**
     * HALAMAN PEMBAYARAN ADMIN
     */
   public function pembayaran(Request $request)
    {
        // FILTER STATUS
        $filter = $request->status;

        $filter = $request->status;
        $search = $request->search;

        // AMBIL DATA PENYEWAAN
        $data = Penyewaan::with([
                'user',
                'fasilitas',
                'pembayaran'
            ])

            ->when($search, function($query) use ($search) {
            $query->where(function($q) use ($search) {
            // Cari berdasarkan Nama di tabel User
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                          ->orWhere('nik', 'like', "%{$search}%");
            })
            // Atau cari berdasarkan Kode Booking di tabel Penyewaan itu sendiri
                ->orWhere('kode_booking', 'like', "%{$search}%");
            });
    })
            ->orderBy('tgl_mulai', 'desc')
            ->get()
            ->groupBy('kode_booking');

        // COLLECTION HASIL
        $hasil = collect();

        // LOOPING DATA
        foreach ($data as $kode => $items) {

            // TOTAL TAGIHAN
            $totalTagihan = $items->sum('total_harga');

            // TOTAL PEMBAYARAN
            $totalBayar = 0;

            foreach ($items as $item) {

                // CEK RELASI PEMBAYARAN
                if ($item->pembayaran) {

                    // HITUNG PEMBAYARAN BERHASIL
                    $bayar = $item->pembayaran
                        ->whereIn('status_pembayaran', [
                            'berhasil',
                            'diverifikasi'
                        ])
                        ->sum('jumlah_bayar');

                    $totalBayar += $bayar;
                }
            }

            // HITUNG SISA TAGIHAN
            $sisaTagihan = $totalTagihan - $totalBayar;

            // STATUS PEMBAYARAN
            $statusPembayaran = $sisaTagihan <= 0
                ? 'lunas'
                : 'pending';

            // SIMPAN DATA CUSTOM
            $items->total_tagihan = $totalTagihan;
            $items->total_bayar = $totalBayar;
            $items->sisa_tagihan = $sisaTagihan;
            $items->status_custom = $statusPembayaran;

            // FILTER STATUS
            if ($filter) {

                if ($statusPembayaran == $filter) {
                    $hasil->put($kode, $items);
                }

            } else {

                $hasil->put($kode, $items);
            }
        }

        // PAGINATION MANUAL
        $perPage = 10;

        $currentPage = $request->get('page', 1);

        $currentItems = $hasil
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->all();

        $pembayarans = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $hasil->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        // KIRIM KE VIEW
        return view('admin.pembayaran.index', compact(
            'pembayarans',
            'filter'
        ));
    }

    /**
     * DASHBOARD ADMIN
     */
    public function dashboard()
    {
        $totalPendapatan = Penyewaan::whereHas('pembayaran', function($q) {
                $q->whereIn('status_pembayaran', ['berhasil', 'diverifikasi']);
            })
            ->orWhere('status_sewa', 'disetujui')
            ->sum('total_harga');

        $totalFasilitas = Fasilitas::count();
        $totalPenyewaan = Penyewaan::count();

        $totalKembali = Penyewaan::where('status_sewa', 'selesai')
            ->orWhereHas('pengembalian', function($q) {
                $q->where('status_validasi', 'disetujui');
            })
            ->count();

        $pending = Penyewaan::where('status_sewa', 'proses')
            ->get()
            ->groupBy('kode_booking')
            ->count();

        $menungguPengembalian = Penyewaan::where('status_sewa', 'menunggu_pengembalian')
            ->get()
            ->groupBy('kode_booking')
            ->count();

        $validasiPengembalian = Penyewaan::where('status_sewa', 'menunggu_validasi_pengembalian')
            ->get()
            ->groupBy('kode_booking')
            ->count();

        $menungguDenda = Penyewaan::where('status_sewa', 'menunggu_pembayaran_denda')
            ->get()
            ->groupBy('kode_booking')
            ->count();

        $pendapatanBulanan = Penyewaan::select(
                DB::raw('SUM(total_harga) as total'),
                DB::raw('MONTH(tgl_mulai) as bulan')
            )
            ->whereYear('tgl_mulai', date('Y'))
            ->where(function($query) {
                $query->whereHas('pembayaran', function($q) {
                    $q->whereIn('status_pembayaran', ['berhasil', 'diverifikasi']);
                })
                ->orWhere('status_sewa', 'disetujui');
            })
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $dataGrafik = [];
        $bulanSekarang = date('n');
        for ($i = 1; $i <= $bulanSekarang; $i++) {
            $dataGrafik[] = $pendapatanBulanan[$i] ?? 0;
        }


        $disetujuiSelesai = Penyewaan::whereIn('status_sewa', [
            'disetujui',
            'selesai'
        ])->count();

        $dibatalkan = Penyewaan::whereIn('status_sewa', [
            'batal',
            'dibatalkan_user'
        ])->count();


        $penyewaan = Penyewaan::with([
        'user',
        'fasilitas',
        'pengembalian',
        'pembayaran'
        ])
        ->latest()
        ->get()
        ->groupBy('kode_booking')
        ->map(function ($group) {

            $totalTagihan = $group->sum('total_harga');

            $totalBayar = 0;

            foreach ($group as $item) {

                if ($item->pembayaran) {

                    $totalBayar += $item->pembayaran
                        ->whereIn('status_pembayaran', [
                            'berhasil',
                            'diverifikasi'
                        ])
                        ->sum('jumlah_bayar');
                }
            }

            $group->total_tagihan = $totalTagihan;
            $group->total_bayar = $totalBayar;
            $group->status_bayar = $totalBayar >= $totalTagihan
                ? 'lunas'
                : 'pending';

            return $group;
        })
        ->take(5);

        return view('admin.dashboard', compact(
            'totalPendapatan',
            'totalFasilitas',
            'totalKembali',
            'totalPenyewaan',
            'pending',
            'menungguPengembalian',
            'validasiPengembalian',
            'menungguDenda',
            'dataGrafik',
            'penyewaan',
            'disetujuiSelesai',
            'dibatalkan'
        ));
    }

    /**
     * LIST PENYEWAAN
     */
    public function index(Request $request)
    {
        $search = $request->search;

        // Ambil data dengan filter search dan relasi
        $data = Penyewaan::with(['user', 'fasilitas', 'pembayaran'])
            ->whereNotNull('kode_booking')
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('nik', 'like', "%{$search}%");
                    })
                    ->orWhere('kode_booking', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get()
            ->groupBy('kode_booking');

        // Pagination Manual untuk Grouped Collection
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $currentItems = $data->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $penyewaan = new LengthAwarePaginator(
            $currentItems,
            $data->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('admin.penyewaan.index', compact('penyewaan'));
    }

    /**
     * KONFIRMASI BOOKING (INI BAGIAN PALING PENTING)
     */
    public function konfirmasiGroup($kode)
    {
        DB::transaction(function () use ($kode) {

            $data = Penyewaan::where('kode_booking', $kode)->get();

            foreach ($data as $item) {

                // 1. SETUJUI SEWA
                $item->update([
                    'status_sewa' => 'disetujui'
                ]);

                // 2. CEK PEMBAYARAN SUDAH ADA ATAU BELUM
                $pembayaran = Pembayaran::where('id_penyewaan', $item->id_penyewaan)->first();

                if ($pembayaran) {
                    /**
                     * PERBAIKAN: 
                     * Ambil metode pembayaran yang sudah dipilih user di awal (Tunai atau Midtrans).
                     * Jangan di-hardcode ke 'midtrans'.
                     */
                    $metodeUser = $pembayaran->metode_pembayaran ?? 'midtrans';

                    $pembayaran->update([
                        'jenis_pembayaran' => 'pelunasan',
                        'metode_pembayaran' => $metodeUser, // Menggunakan metode pilihan user
                        'status_pembayaran' => 'pending'
                    ]);
                } else {
                    // Jaga-jaga jika record pembayaran belum terbuat
                    Pembayaran::create([
                        'id_penyewaan' => $item->id_penyewaan,
                        'kode_pembayaran' => 'PAY-' . time(),
                        'jenis_pembayaran' => 'lunas',
                        'metode_pembayaran' => 'midtrans', // Default jika record baru
                        'jumlah_bayar' => 0,
                        'status_pembayaran' => 'pending',
                    ]);
                }
            }
        });

        return back()->with('success', 'Booking ' . $kode . ' disetujui.');
    }

    /**
     * TOLAK BOOKING
     */
    public function tolakGroup($kode)
    {
        DB::transaction(function () use ($kode) {

            $data = Penyewaan::where('kode_booking', $kode)->get();

            foreach ($data as $item) {

                if ($item->status_sewa !== 'batal') {
                    $fasilitas = Fasilitas::find($item->id_fasilitas);

                    if ($fasilitas) {
                        $fasilitas->increment('jumlah', $item->jumlah_sewa);

                        if ($fasilitas->jumlah > 0) {
                            $fasilitas->update(['status_fasilitas' => 'tersedia']);
                        }
                    }
                }

                $item->update(['status_sewa' => 'batal']);

                Pembayaran::where('id_penyewaan', $item->id_penyewaan)
                    ->update(['status_pembayaran' => 'batal']);
            }
        });

        return back()->with('success', 'Booking ' . $kode . ' telah ditolak.');
    }

    /**
     * HAPUS DATA
     */
    public function destroy($id)
    {
        Penyewaan::findOrFail($id)->delete();
        return back()->with('success', 'Data penyewaan berhasil dihapus.');
    }
}