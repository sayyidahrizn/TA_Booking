<div class="sidebar">
    <h2>Pengunjung</h2>

    <a href="{{ route('user.dashboard') }}"
       class="{{ request()->is('user/dashboard') ? 'active' : '' }}">
        Dashboard
    </a>

    <a href="{{ route('user.fasilitas.index') }}"
       class="{{ request()->is('user/fasilitas*') ? 'active' : '' }}">
        Fasilitas
    </a>

    <a href="{{ route('user.penyewaan.index') }}"
       class="{{ request()->is('user/penyewaan*') ? 'active' : '' }}">
        Penyewaan
    </a>

    <a href="{{ route('user.riwayat') }}"
       class="{{ request()->is('user/riwayat') ? 'active' : '' }}">
        Riwayat Penyewaan
    </a>

    {{-- Menu Pengembalian (sementara belum aktif) --}}
    <a href="#"
       onclick="">
        Pengembalian
    </a>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</div>