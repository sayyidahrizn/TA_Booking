<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fasilitas;
use App\Models\FasilitasGambar;
use Illuminate\Support\Facades\Storage;

class FasilitasController extends Controller
{

    public function index()
    {
        // ✅ PAGINATION 8 DATA
        $fasilitas = Fasilitas::with('gambar')->paginate(8);

        return view('admin.fasilitas.index', compact('fasilitas'));
    }

    public function create()
    {
        return view('admin.fasilitas.create');
    }

    public function store(Request $request)
    {
        $fasilitas = new Fasilitas();
        $fasilitas->nama_fasilitas = $request->nama_fasilitas;
        $fasilitas->deskripsi = $request->deskripsi;
        $fasilitas->harga_sewa = $request->harga_sewa;
        $fasilitas->status_fasilitas = $request->status_fasilitas;
        $fasilitas->save();

        if($request->hasFile('gambar')){
            foreach($request->file('gambar') as $img){

                $path = $img->store('fasilitas','public');

                $gambar = new FasilitasGambar();
                $gambar->id_fasilitas = $fasilitas->id_fasilitas;
                $gambar->file_gambar = $path;
                $gambar->save();
            }
        }

        return redirect()->route('fasilitas.index')
        ->with('success','Fasilitas berhasil ditambahkan');
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
        $fasilitas->harga_sewa = $request->harga_sewa;
        $fasilitas->status_fasilitas = $request->status_fasilitas;
        $fasilitas->save();

        if($request->hasFile('gambar')){
            foreach($request->file('gambar') as $img){

                $path = $img->store('fasilitas','public');

                $gambar = new FasilitasGambar();
                $gambar->id_fasilitas = $fasilitas->id_fasilitas;
                $gambar->file_gambar = $path;
                $gambar->save();
            }
        }

        return redirect()->route('fasilitas.index')
        ->with('success','Fasilitas berhasil diupdate');
    }

    public function destroy($id)
    {
        $fasilitas = Fasilitas::with('gambar')->findOrFail($id);

        foreach($fasilitas->gambar as $g){
            Storage::disk('public')->delete($g->file_gambar);
            $g->delete();
        }

        $fasilitas->delete();

        return redirect()->route('fasilitas.index')
        ->with('success','Fasilitas berhasil dihapus');
    }

    public function hapusGambar($id)
    {
        $gambar = FasilitasGambar::findOrFail($id);

        if (Storage::disk('public')->exists($gambar->file_gambar)) {
            Storage::disk('public')->delete($gambar->file_gambar);
        }

        $gambar->delete();

        return back()->with('success', 'Gambar berhasil dihapus!');
    }

    public function indexUser()
    {
        $fasilitas = Fasilitas::where('status_fasilitas', 'tersedia')->paginate(8);

        return view('user.fasilitas.index', compact('fasilitas'));
    }

    public function show($id)
    {
        $fasilitas = Fasilitas::findOrFail($id);
        return view('user.fasilitas.show', compact('fasilitas'));
    }
}