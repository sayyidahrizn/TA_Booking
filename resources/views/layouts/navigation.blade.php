<nav style="background:#fff; border-bottom:1px solid #ddd; padding:10px 20px; display:flex; justify-content:space-between; align-items:center;">

    <!-- Kiri -->
    <div>
        <a href="{{ url('/user/dashboard') }}" style="margin-right:15px;">Dashboard</a>
        <a href="{{ route('user.fasilitas.index') }}" style="margin-right:15px;">Fasilitas</a>
        <a href="{{ route('user.penyewaan.index') }}">Penyewaan</a>
    </div>

    <!-- Kanan -->
    <div>
        <span>{{ Auth::user()->name }}</span>

        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" style="margin-left:10px;">Logout</button>
        </form>
    </div>

</nav>