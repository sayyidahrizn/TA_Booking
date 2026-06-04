<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use App\Models\Fasilitas;
use App\Models\User;
use App\Models\Pengembalian;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PenyewaanDisetujuiMail;
use App\Mail\PenyewaanDitolakMail;


class PenyewaanController extends Controller
{
    /**
     * VERIFIKASI PEMBAYARAN MANUAL
     */
    public function verifikasiPembayaran(Request $request, $id_pembayaran)
    {
        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:1'
        ]);

        DB::beginTransaction();

        try {

            $pembayaran = Pembayaran::with('penyewaan')->findOrFail($id_pembayaran);

            $kodeBooking = $pembayaran->penyewaan->kode_booking;

            // TOTAL SEMUA FASILITAS DALAM 1 BOOKING
            $totalTagihan = Penyewaan::where('kode_booking', $kodeBooking)
                ->sum('total_harga');

            // TOTAL SUDAH DIBAYAR
            $totalSudahBayar = Pembayaran::whereHas('penyewaan', function ($q) use ($kodeBooking) {
                    $q->where('kode_booking', $kodeBooking);
                })
                ->whereIn('status_pembayaran', ['berhasil', 'diverifikasi'])
                ->sum('jumlah_bayar');

            $inputBayar = $request->jumlah_bayar;

            // TOTAL SETELAH PEMBAYARAN SEKARANG
            $totalSetelahBayar = $totalSudahBayar + $inputBayar;

            // CEK DP ATAU PELUNASAN
            $jenisPembayaran = ($totalSetelahBayar < $totalTagihan)
                ? 'dp'
                : 'pelunasan';

            // UPDATE PEMBAYARAN
            $pembayaran->update([
                'status_pembayaran' => 'diverifikasi',
                'tanggal_bayar' => now(),
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar' => $inputBayar,
                'jenis_pembayaran' => $jenisPembayaran
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Pembayaran tunai berhasil diverifikasi.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Gagal verifikasi: ' . $e->getMessage()
            );
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

            // Jika minus jadikan 0
            if ($sisaTagihan < 0) {
                $sisaTagihan = 0;
            }

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
        $now = now();
        $bulan = $now->month;
        $tahun = $now->year;

        // --- BASE QUERY (Filter Bulan & Tahun Berjalan) ---
        $baseQuery = Penyewaan::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun);

        /*
        |--------------------------------------------------------------------------
        | STATISTIK UTAMA
        |--------------------------------------------------------------------------
        */

        // Menghitung pendapatan hanya dari transaksi yang valid/disetujui
        $totalPendapatan = (clone $baseQuery)
            ->where(function ($query) {
                $query->whereHas('pembayaran', function ($q) {
                    $q->whereIn('status_pembayaran', ['berhasil', 'diverifikasi']);
                })->orWhere('status_sewa', 'disetujui');
            })
            ->sum('total_harga');

        $totalFasilitas = Fasilitas::count();

        $totalPenyewaan = (clone $baseQuery)
            ->distinct('kode_booking')
            ->count('kode_booking');

        $totalKembali = (clone $baseQuery)
            ->where(function ($query) {
                $query->where('status_sewa', 'selesai')
                    ->orWhereHas('pengembalian', function ($q) {
                        $q->where('status_validasi', 'disetujui');
                    });
            })
            ->distinct('kode_booking')
            ->count('kode_booking');
        $periode = $now->translatedFormat('F Y'); 
        $tanggalHariIni = $now->translatedFormat('d F Y'); 

        /*
        |--------------------------------------------------------------------------
        | STATUS MONITORING (COUNT)
        |--------------------------------------------------------------------------
        */

        $pending              = (clone $baseQuery)->where('status_sewa', 'proses')->distinct('kode_booking')->count();
        $menungguPengembalian = (clone $baseQuery)->where('status_sewa', 'menunggu_pengembalian')->distinct('kode_booking')->count();
        $menungguDenda        = (clone $baseQuery)->where('status_sewa', 'menunggu_pembayaran_denda')->distinct('kode_booking')->count();
        
        // Status untuk Grafik Donut
        $disetujuiSelesai     = (clone $baseQuery)->whereIn('status_sewa', ['disetujui', 'selesai'])->distinct('kode_booking')->count();
        $dibatalkan           = (clone $baseQuery)->whereIn('status_sewa', ['batal', 'dibatalkan_user'])->distinct('kode_booking')->count();

        // Validasi Pengembalian (Berdasarkan relasi tabel pengembalian)
        $validasiPengembalian = Pengembalian::whereMonth('tanggal_pengembalian', $bulan)
            ->whereYear('tanggal_pengembalian', $tahun)
            ->where('status_validasi', 'pending')
            ->with('penyewaan')
            ->get()
            ->groupBy(fn($item) => $item->penyewaan->kode_booking)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | DATA GRAFIK PENDAPATAN HARIAN
        |--------------------------------------------------------------------------
        */

        $pendapatanHarian = (clone $baseQuery)
            ->selectRaw('SUM(total_harga) as total, DAY(created_at) as hari')
            ->where(function ($query) {
                $query->whereHas('pembayaran', function ($q) {
                    $q->whereIn('status_pembayaran', ['berhasil', 'diverifikasi']);
                })->orWhere('status_sewa', 'disetujui');
            })
            ->groupBy('hari')
            ->orderBy('hari')
            ->pluck('total', 'hari')
            ->toArray();

        $labelHari  = range(1, $now->daysInMonth);
        $dataGrafik = array_map(fn($hari) => $pendapatanHarian[$hari] ?? 0, $labelHari);

        /*
        |--------------------------------------------------------------------------
        | LIST PENYEWAAN TERBARU (5 DATA TERAKHIR)
        |--------------------------------------------------------------------------
        */

        $penyewaan = (clone $baseQuery)
            ->with(['user', 'fasilitas', 'pengembalian', 'pembayaran'])
            ->latest()
            ->get()
            ->groupBy('kode_booking')
            ->take(5)
            ->map(function ($group) {
                $totalTagihan = $group->sum('total_harga');
                
                // Hitung total bayar dari koleksi pembayaran yang valid
                $totalBayar = $group->pluck('pembayaran')
                    ->flatten()
                    ->whereIn('status_pembayaran', ['berhasil', 'diverifikasi'])
                    ->sum('jumlah_bayar');

                // Tambahkan atribut custom ke dalam koleksi
                $group->total_tagihan = $totalTagihan;
                $group->total_bayar   = $totalBayar;
                $group->status_bayar  = ($totalBayar >= $totalTagihan) ? 'lunas' : 'pending';

                return $group;
            });

        return view('admin.dashboard', compact(
            'totalPendapatan', 'totalFasilitas', 'totalKembali', 'totalPenyewaan',
            'pending', 'menungguPengembalian', 'validasiPengembalian', 'menungguDenda',
            'dataGrafik', 'labelHari', 'penyewaan', 'disetujuiSelesai', 'dibatalkan',  'periode', 'tanggalHariIni'
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
        DB::beginTransaction();

        try {
            // FIX 1: Ditambahkan with('user') agar data user terbawa ke file email blade
            $data = Penyewaan::with('user')->where('kode_booking', $kode)->get();

            if ($data->isEmpty()) {
                return back()->with('error', 'Data booking tidak ditemukan.');
            }

            $userEmail = null;
            $itemUntukEmail = null;

            foreach ($data as $item) {
                if ($item->status_sewa == 'disetujui') {
                    continue;
                }

                // CEK DAN KURANGI STOK FASILITAS
                $fasilitas = Fasilitas::lockForUpdate()->find($item->id_fasilitas);
                if (!$fasilitas) {
                    throw new \Exception('Fasilitas tidak ditemukan.');
                }

                if ($fasilitas->jumlah < $item->jumlah_sewa) {
                    throw new \Exception('Stok "' . $fasilitas->nama_fasilitas . '" tidak mencukupi.');
                }

                $fasilitas->decrement('jumlah', $item->jumlah_sewa);
                $fasilitas->refresh();

                if ($fasilitas->jumlah <= 0) {
                    $fasilitas->update(['status_fasilitas' => 'tidak tersedia']);
                }

                // FORCE SAVE STATUS (Dipaksa simpan ke database)
                $item->status_sewa = 'disetujui';
                $item->save(); 

                // Ambil data user untuk dikirimi email nanti
                if ($item->user && $item->user->email) {
                    $userEmail = $item->user->email;
                    $itemUntukEmail = $item;
                }

                // LOGIKA UPDATE PEMBAYARAN PENDING
                $pembayaran = Pembayaran::where('id_penyewaan', $item->id_penyewaan)->first();
                if ($pembayaran) {
                    $pembayaran->update([
                        'jenis_pembayaran'  => 'pelunasan',
                        'status_pembayaran' => 'pending'
                    ]);
                } else {
                    Pembayaran::create([
                        'id_penyewaan'      => $item->id_penyewaan,
                        'kode_pembayaran'   => 'PAY-' . time() . '-' . $item->id_penyewaan,
                        'jenis_pembayaran'  => 'pelunasan',
                        'metode_pembayaran' => 'midtrans',
                        'jumlah_bayar'      => 0,
                        'status_pembayaran' => 'pending',
                    ]);
                }
            }

            // FIX 2: PROSES EMAIL DIKELUARKAN DARI LOOP (Hanya kirim 1 email per kode booking)
            if ($userEmail && $itemUntukEmail) {
                Mail::to($userEmail)->send(new PenyewaanDisetujuiMail($itemUntukEmail));
            }

            DB::commit();
            return back()->with('success', 'Booking ' . $kode . ' berhasil disetujui.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Menampilkan pesan eror asli di dashboard jika sistem gagal menyimpan
            return back()->with('error', 'Gagal menyetujui booking: ' . $e->getMessage());
        }
    }

    /**
     * TOLAK BOOKING
     */
    public function tolakGroup(Request $request, $kode)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            // FIX 1: Ditambahkan with('user') agar data user terbawa ke file email blade
            $data = Penyewaan::with('user')->where('kode_booking', $kode)->get();

            if ($data->isEmpty()) {
                return back()->with('error', 'Data booking tidak ditemukan.');
            }

            $alasan = $request->alasan_penolakan;
            $userEmail = null;
            $itemUntukEmail = null;

            foreach ($data as $item) {
                if ($item->status_sewa === 'batal') {
                    continue;
                }

                // Kembalikan Stok Fasilitas
                $fasilitas = Fasilitas::find($item->id_fasilitas);
                if ($fasilitas) {
                    $fasilitas->increment('jumlah', $item->jumlah_sewa);
                    $fasilitas->refresh();

                    if ($fasilitas->jumlah > 0) {
                        $fasilitas->update(['status_fasilitas' => 'tersedia']);
                    }
                }

                // Force Ubah status sewa & simpan ke database
                $item->status_sewa = 'batal';
                $item->save();

                // Batalkan transaksi pembayaran terkait
                Pembayaran::where('id_penyewaan', $item->id_penyewaan)->update([
                    'status_pembayaran' => 'gagal'
                ]);

                if ($item->user && $item->user->email) {
                    $userEmail = $item->user->email;
                    $itemUntukEmail = $item;
                }
            }

            // FIX 2: PROSES EMAIL DIKELUARKAN DARI LOOP (Hanya kirim 1 email per kode booking)
            if ($userEmail && $itemUntukEmail) {
                Mail::to($userEmail)->send(new PenyewaanDitolakMail($itemUntukEmail, $alasan));
            }

            DB::commit();
            return back()->with('success', 'Booking ' . $kode . ' telah ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak booking: ' . $e->getMessage());
        }
    }

    /**
     * HAPUS DATA
     */
    public function destroy($id)
    {
        Penyewaan::findOrFail($id)->delete();
        return back()->with('success', 'Data penyewaan berhasil dihapus.');
    }

    public function cetakPembayaran($kode_booking)
    {
        $data = Penyewaan::with([
            'user',
            'fasilitas',
            'pembayaran'
        ])
        ->where('kode_booking', $kode_booking)
        ->get();

        if ($data->isEmpty()) {
            abort(404);
        }

        // TOTAL SEMUA FASILITAS
        $totalFasilitas = $data->sum('total_harga');

        // AMBIL SEMUA PEMBAYARAN VALID
        $pembayaran = Pembayaran::whereHas('penyewaan', function ($q) use ($kode_booking) {
                $q->where('kode_booking', $kode_booking);
            })
            ->whereIn('status_pembayaran', ['berhasil', 'diverifikasi'])
            ->orderBy('tanggal_bayar')
            ->get();

        // TOTAL DIBAYAR
        $totalDibayar = $pembayaran->sum('jumlah_bayar');

        // SISA TAGIHAN
        $sisaTagihan = $totalFasilitas - $totalDibayar;

        if ($sisaTagihan < 0) {
            $sisaTagihan = 0;
        }

        return view('admin.pembayaran.cetak', compact(
            'data',
            'pembayaran',
            'totalFasilitas',
            'totalDibayar',
            'sisaTagihan'
        ));
    }
}