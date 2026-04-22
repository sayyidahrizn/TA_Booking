<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fasilitas;
use App\Models\FasilitasGambar;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FasilitasController extends Controller
{
    /**
     * Halaman Depan Publik (Landing Page)
     */
    public function landingPage()
    {
        // Ambil fasilitas tersedia beserta gambarnya
        $fasilitas = Fasilitas::with('gambar')->where('status_fasilitas', 'tersedia')->get();

        // Ambil data penyewaan yang disetujui untuk kalender
        $jadwal = Penyewaan::with('fasilitas')
            ->whereIn('status_sewa', ['disetujui', 'proses']) 
            ->get();

        return view('beranda', compact('fasilitas', 'jadwal'));
    }

    /**
     * Halaman List Fasilitas untuk Admin (Dengan Pencarian & Pagination)
     */
    public function index(Request $request)
    {
        // Ambil kata kunci dari input search
        $search = $request->input('search');

        // Query data fasilitas dengan relasi gambar
        $fasilitas = Fasilitas::with('gambar')
            ->when($search, function ($query, $search) {
                return $query->where('nama_fasilitas', 'LIKE', "%{$search}%")
                             ->orWhere('deskripsi', 'LIKE', "%{$search}%");
            })
            ->latest() // Menampilkan data terbaru di atas
            ->paginate(8)
            ->withQueryString(); // Menjaga keyword search tetap ada saat pindah halaman

        return view('admin.fasilitas.index', compact('fasilitas'));
    }

    public function create()
    {
        return view('admin.fasilitas.create');
    }

    public function store(Request $request)
    {
        // Validasi dasar
        $request->validate([
            'nama_fasilitas' => 'required',
            'harga_sewa' => 'required|numeric',
            'jumlah' => 'required|integer',
            'gambar.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $fasilitas = new Fasilitas();
        $fasilitas->nama_fasilitas = $request->nama_fasilitas;
        $fasilitas->deskripsi = $request->deskripsi;
        $fasilitas->jumlah = $request->jumlah;
        $fasilitas->harga_sewa = $request->harga_sewa;
        $fasilitas->status_fasilitas = $request->status_fasilitas;
        $fasilitas->save();

        if($request->hasFile('gambar')){
            foreach($request->file('gambar') as $img){
                $path = $img->store('fasilitas', 'public');
                
                $gambar = new FasilitasGambar();
                $gambar->id_fasilitas = $fasilitas->id_fasilitas;
                $gambar->file_gambar = $path;
                $gambar->save();
            }
        }

        return redirect()->route('fasilitas.index')
            ->with('success', 'Fasilitas berhasil ditambahkan');
    }

    public function edit($id)
    {
        $fasilitas = Fasilitas::with('gambar')->findOrFail($id);
        return view('admin.fasilitas.edit', compact('fasilitas'));
    }

    public function update(Request $request, $id)
    {
        $fasilitas = Fasilitas::findOrFail($id);
        $fasilitas->nama_fasilitas = $request->nama_fasilitas;
        $fasilitas->deskripsi = $request->deskripsi;
        $fasilitas->jumlah = $request->jumlah;
        $fasilitas->harga_sewa = $request->harga_sewa;
        $fasilitas->status_fasilitas = $request->status_fasilitas;
        $fasilitas->save();

        if($request->hasFile('gambar')){
            foreach($request->file('gambar') as $img){
                $path = $img->store('fasilitas', 'public');
                
                $gambar = new FasilitasGambar();
                $gambar->id_fasilitas = $fasilitas->id_fasilitas;
                $gambar->file_gambar = $path;
                $gambar->save();
            }
        }

        return redirect()->route('fasilitas.index')
            ->with('success', 'Fasilitas berhasil diupdate');
    }

    public function destroy($id)
    {
        $fasilitas = Fasilitas::with('gambar')->findOrFail($id);
        
        // Hapus semua file gambar fisik dari storage
        foreach($fasilitas->gambar as $g){
            if (Storage::disk('public')->exists($g->file_gambar)) {
                Storage::disk('public')->delete($g->file_gambar);
            }
            $g->delete();
        }
        
        $fasilitas->delete();

        return redirect()->route('fasilitas.index')
            ->with('success', 'Fasilitas berhasil dihapus');
    }

    /**
     * Menghapus gambar satu per satu (dipakai di halaman edit)
     */
    public function hapusGambar($id)
    {
        $gambar = FasilitasGambar::findOrFail($id);
        
        if (Storage::disk('public')->exists($gambar->file_gambar)) {
            Storage::disk('public')->delete($gambar->file_gambar);
        }
        
        $gambar->delete();
        
        return back()->with('success', 'Gambar berhasil dihapus!');
    }

    /**
     * List Fasilitas untuk sisi User
     */
    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $fasilitas = Fasilitas::where('status_fasilitas', 'tersedia')
            ->when($search, function($query, $search) {
                return $query->where('nama_fasilitas', 'LIKE', "%{$search}%");
            })
            ->paginate(8)
            ->withQueryString();

        return view('user.fasilitas.index', compact('fasilitas'));
    }

    public function show($id)
    {
        $fasilitas = Fasilitas::with('gambar')->findOrFail($id);
        return view('user.fasilitas.show', compact('fasilitas'));
    }
}