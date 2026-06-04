<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        /* --- Reset & Base --- */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f1f5f9;
            font-size: 14px;
            color: #334155;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* --- Sidebar --- */
        .sidebar {
            width: 240px;
            height: 100vh;
            background-color: #111827;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 100;
        }

        .sidebar-menu-wrapper {
            width: 100%;
            overflow-y: auto;
        }

        /* --- Logo Section --- */
        .sidebar-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 25px 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 10px;
        }

        .logo-desa-img {
            width: 65px;
            height: auto;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .logo-desa-text {
            font-size: 16px;
            font-weight: bold;
            color: white;
            text-align: center;
            letter-spacing: 0.5px;
        }

        /* --- Navigation Links --- */
        .sidebar a, .menu-link {
            display: flex;
            align-items: center;
            width: 100%;
            height: 50px;
            padding: 0 20px;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sidebar a i, .menu-link i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
            font-size: 16px;
        }

        .sidebar a span, .menu-link span {
            transition: 0.3s ease;
        }

        .sidebar a:hover, .menu-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .sidebar a:hover span, .menu-link:hover span {
            transform: translateX(5px);
        }

        .sidebar a.active {
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white !important;
            font-weight: 600;
            border-left: 4px solid #38bdf8;
        }

        /* --- Dropdown Menu --- */
        .menu-dropdown-wrapper {
            width: 100%;
        }

        .menu-dropdown-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: 0.3s;
        }

        .menu-dropdown-header.active{
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            border-left: 4px solid #38bdf8;
        }

        .menu-dropdown-header.active .menu-link{
            color: white !important;
            font-weight: bold;
        }

        .menu-dropdown-header.active .menu-link span{
            transform: translateX(3px);
        }

        .dropdown-toggle {
            width: 45px;
            height: 50px;
            border: none;
            background: transparent;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: 0.3s;
        }

        .dropdown-toggle i {
            transition: transform 0.3s;
        }

        .dropdown-toggle.active i {
            transform: rotate(180deg);
        }

        .dropdown-container {
            display: none;
            background-color: #1f2937;
            padding-left: 10px;
        }

        .dropdown-container a {
            height: 40px;
            font-size: 13px;
            border-left: 2px solid transparent;
        }

        .dropdown-container a.active {
            color: #38bdf8;
            background: transparent;
            border-left: 2px solid #38bdf8;
        }

        /* --- Sidebar Footer --- */
        .sidebar-footer {
            width: 100%;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background-color: #1f2937;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 15px 20px;
            background: none;
            border: none;
            color: #f87171;
            font-size: 14px;
            font-family: inherit;
            cursor: pointer;
            transition: 0.3s;
        }

        .logout-btn i {
            margin-right: 10px;
        }

        .logout-btn:hover {
            background-color: #ef4444;
            color: white;
        }

        /* --- Main Content Area --- */
        .content {
            margin-left: 240px;
            width: calc(100% - 240px);
            padding: 25px;
        }

        /* --- Header Section --- */
        .header {
            min-height: 65px;
            background-color: #404058;
            padding: 0 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 20px;
            z-index: 99;
        }

        .header h3 {
            margin: 0;
            font-size: 18px;
            color: white;
        }

        /* --- Profile Widget --- */
        .profile-top {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            padding: 5px 15px;
            border-radius: 50px;
            transition: 0.3s;
        }

        .profile-top:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .profile-info {
            text-align: right;
        }

        .profile-name {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: white;
        }

        .profile-role {
            font-size: 11px;
            color: #cbd5e1;
        }

        .profile-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #38bdf8;
            object-fit: cover;
        }

        /* --- General Card --- */
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="sidebar">
        <div class="sidebar-menu-wrapper">
            <div class="sidebar-logo">
                <img src="{{ asset('images/LOGODESA.png') }}" alt="Logo Desa" class="logo-desa-img">
                <span class="logo-desa-text">Desa Kesamben</span>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <span></i> Dashboard</span>
            </a>

            <a href="{{ route('fasilitas.index') }}" class="{{ request()->is('fasilitas*') ? 'active' : '' }}">
                <span></i> Kelola Fasilitas</span>
            </a>

            <a href="{{ route('users.index') }}" class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                <span></i> Kelola User</span>
            </a>

            <a href="{{ route('admin.penyewaan.index') }}" class="{{ request()->is('admin/penyewaan*') ? 'active' : '' }}">
                <span></i> Kelola Penyewaan</span>
            </a>

            <a href="{{ route('admin.pembayaran.index') }}" class="{{ request()->is('admin/pembayaran*') ? 'active' : '' }}">
                <span></i> Kelola Pembayaran</span>
            </a>

            <a href="{{ route('admin.pengembalian') }}" class="{{ request()->is('admin/pengembalian*') ? 'active' : '' }}">
                <span></i> Kelola Pengembalian</span>
            </a>

            <div class="menu-dropdown-wrapper">
                <div class="menu-dropdown-header {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
                    <a href="{{ route('admin.laporan') }}" class="menu-link">
                        <span></i> Laporan</span>
                    </a>
                    <button type="button" class="dropdown-toggle {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}" id="laporanBtn">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </div>

                <div class="dropdown-container" id="laporanDropdown" 
                     style="{{ request()->routeIs('admin.laporan.*') ? 'display:block;' : '' }}">
                    <a href="{{ route('admin.laporan.sewa') }}" class="{{ request()->routeIs('admin.laporan.sewa') ? 'active' : '' }}">
                        Pemasukan Penyewaan
                    </a>
                    <a href="{{ route('admin.laporan.denda') }}" class="{{ request()->routeIs('admin.laporan.denda') ? 'active' : '' }}">
                        Pemasukan Denda
                    </a>
                    <a href="{{ route('admin.laporan.fasilitas') }}" class="{{ request()->routeIs('admin.laporan.fasilitas') ? 'active' : '' }}">
                        Fasilitas
                    </a>
                </div>
            </div>
        </div>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="content">
        <div class="header">
            <h3>@yield('page-title')</h3>
            <a href="{{ route('admin.profile') }}" class="profile-top">
                <div class="profile-info">
                    <span class="profile-name">{{ auth()->user()->name }}</span>
                    <span class="profile-role">Administrator</span>
                </div>
                <img src="{{ auth()->user()->photo ? asset('storage/' . auth()->user()->photo) . '?v=' . time() : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=2563eb&color=fff' }}" 
                     alt="Avatar" class="profile-avatar">
            </a>
        </div>

        @yield('content')
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const laporanBtn = document.getElementById('laporanBtn');
        const laporanDropdown = document.getElementById('laporanDropdown');

        laporanBtn.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            
            if (laporanDropdown.style.display === 'block') {
                laporanDropdown.style.display = 'none';
            } else {
                laporanDropdown.style.display = 'block';
            }
        });
    });
</script>

</body>
</html>