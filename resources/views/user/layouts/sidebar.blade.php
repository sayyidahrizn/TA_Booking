<div class="sidebar">
    <!-- Bagian Atas: Logo & Menu Navigasi -->
    <div class="sidebar-menu">
        <div class="sidebar-logo">
            <!-- Ganti src dengan path logo Anda (contoh: asset('images/logo.png')) -->
            <img src="{{ asset('images/LOGODESA.png') }}" alt="Logo App" class="logo-img">
            <h2>Desa Kesamben</h2>
        </div>

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
        <a href="{{ route('user.pengembalian') }}"
           class="{{ request()->is('user/pengembalian') ? 'active' : '' }}">
            Pengembalian
        </a>
    </div>

    <!-- Bagian Bawah: Tombol Logout Melayang di Pojok Kiri Bawah -->
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </button>
        </form>
    </div>
</div>