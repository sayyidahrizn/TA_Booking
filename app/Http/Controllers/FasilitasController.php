<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use App\Models\Fasilitas;
use App\Models\FasilitasGambar;
use Illuminate\Http\Request;

class FasilitasController extends Controller
{
    public function index()
    {
        $fasilitas = Fasilitas::with('gambar')->get();
        return view('admin.fasilitas.index', compact('fasilitas'));
    }

    public function create()
    {
        return view('admin.fasilitas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_fasilitas'   => 'required',
            'harga_sewa'       => 'required|numeric',
            'status_fasilitas' => 'required',
            'gambar.*'         => 'image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // simpan fasilitas
        $fasilitas = Fasilitas::create([
            'nama_fasilitas'   => $request->nama_fasilitas,
            'deskripsi'        => $request->deskripsi,
            'harga_sewa'       => $request->harga_sewa,
            'status_fasilitas' => $request->status_fasilitas
        ]);

        // simpan gambar (jika ada)
        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $img) {
                $path = $img->store('fasilitas', 'public');

                FasilitasGambar::create([
                    'id_fasilitas' => $fasilitas->id_fasilitas,
                    'file_gambar'  => $path
                ]);
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

        $fasilitas->update($request->only([
            'nama_fasilitas',
            'deskripsi',
            'harga_sewa',
            'status_fasilitas'
        ]));

        // tambah gambar baru (opsional)
        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $img) {
                $path = $img->store('fasilitas', 'public');

                FasilitasGambar::create([
                    'id_fasilitas' => $fasilitas->id_fasilitas,
                    'file_gambar'  => $path
                ]);
            }
        }

        return redirect()->route('fasilitas.index')
                         ->with('success', 'Fasilitas berhasil diupdate');
    }

    public function destroy($id)
    {
        $fasilitas = Fasilitas::with('gambar')->findOrFail($id);

        // hapus file gambar dari storage
        foreach ($fasilitas->gambar as $g) {
            Storage::disk('public')->delete($g->file_gambar);
        }

        // hapus data fasilitas (gambar ikut terhapus karena cascade)
        $fasilitas->delete();

        return redirect()->route('fasilitas.index')
                        ->with('success', 'Fasilitas berhasil dihapus');
    }


    public function hapusGambar($id)
    {
        $gambar = FasilitasGambar::findOrFail($id);

        // hapus file gambar dari storage
        Storage::disk('public')->delete($gambar->file_gambar);

        // hapus data gambar dari database
        $gambar->delete();

        return back()->with('success', 'Gambar berhasil dihapus');
    }
}
