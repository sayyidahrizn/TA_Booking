@extends('user.layouts.app')

@section('content')
<div class="container">
    <h2>Pembayaran</h2>

    <hr>

    <p><b>Fasilitas:</b> {{ $penyewaan->fasilitas->nama_fasilitas }}</p>
    <p><b>Tanggal:</b> {{ $penyewaan->tgl_mulai }} - {{ $penyewaan->tgl_selesai }}</p>
    <p><b>Total:</b> Rp {{ number_format($penyewaan->total_harga, 0, ',', '.') }}</p>

    <hr>

    {{-- ✅ FIXED: Menggunakan id_penyewaan sesuai Primary Key di Model --}}
    <form action="{{ route('user.pembayaran.store', $penyewaan->id_penyewaan) }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf

        <div>
            <label>Metode Pembayaran</label><br>
            <select name="metode" required class="form-control">
                <option value="">-- Pilih --</option>
                <option value="transfer">Transfer</option>
                <option value="cash">Cash</option>
            </select>
        </div>

        <br>

        <div>
            <label>Bukti Pembayaran</label><br>
            <input type="file" name="bukti_pembayaran" required class="form-control">
            <small class="text-muted">Format: JPG, PNG, JPEG (Maks. 2MB)</small>
        </div>

        <br>

        <button type="submit" class="btn btn-success">Bayar Sekarang</button>
        <a href="{{ route('user.penyewaan.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection