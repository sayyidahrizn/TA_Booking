<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>@yield('title', 'Dashboard Admin')</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
            font-size: 14px;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* ================= SIDEBAR (DIPERBAIKI) ================= */
        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: #111827; /* Warna biru sesuai desain sebelumnya */
            color: #ffffff;
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

        /* Area Logo Desa */
        .sidebar-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 25px 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 10px;
        }

        .logo-desa-img {
            width: 65px;
            height: auto;
            margin-bottom: 10px;
            object-fit: contain;
        }

        .logo-desa-text {
            font-size: 17px;
            font-weight: bold;
            color: white;
            text-align: center;
        }

        /* Gaya Link Navigasi */
        .sidebar a {
            display: flex;
            align-items: center;
            width: 100%;
            height: 45px;
            padding: 0 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        /* Efek Hover */
        .sidebar a:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            padding-left: 25px;
        }

        /* Efek Active (Halaman Saat Ini) */
        .sidebar a.active {
            background-color: #1f2937;
            color: white !important;
            font-weight: bold;
            border-left: 4px solid #38bdf8;
        }

        /* Container Logout di Bawah */
        .sidebar-footer {
            width: 100%;
            border-top: 1px solid rgba(255,255,255,0.1);
            background-color: #1f2937;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 14px 20px;
            color: rgba(255, 255, 255, 0.8);
            background: none;
            border: none;
            font-size: 14px;
            font-family: inherit;
            cursor: pointer;
            transition: 0.3s;
            text-align: left;
        }

        .logout-btn i {
            margin-right: 10px;
        }

        .logout-btn:hover {
            background-color: #ef4444; /* Warna merah saat hover logout */
            color: white;
        }

        /* ================= CONTENT ================= */
        .content {
            margin-left: 220px;
            width: calc(100% - 220px);
            padding: 25px;
        }

        /* ================= HEADER ================= */
        .header {
            min-height: 65px;
            background-color: #404058;
            padding: 10px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            font-weight: bold;
            color: #ffffff;
        }

        /* ================= PROFILE TOP ================= */
        .profile-top {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 50px;
            transition: 0.3s;
        }

        .profile-top:hover {
            background: rgba(255,255,255,0.1);
        }

        .profile-info {
            text-align: right;
        }

        .profile-name {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
        }

        .profile-role {
            font-size: 11px;
            color: #cbd5e1;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #38bdf8;
            object-fit: cover;
        }

        /* ================= CARD ================= */
        .card {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            font-size: 14px;
        }

        .card h3 {
            margin-top: 0;
            font-size: 15px;
            color: #1e2937;
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

            <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('fasilitas.index') }}" class="{{ request()->is('fasilitas*') ? 'active' : '' }}">Kelola Fasilitas</a>
            <a href="{{ route('users.index') }}" class="{{ request()->is('users*') ? 'active' : '' }}">Kelola User</a>
            <a href="{{ route('admin.penyewaan.index') }}" class="{{ request()->is('admin/penyewaan*') ? 'active' : '' }}">Kelola Penyewaan</a>
            <a href="{{ route('admin.pembayaran.index') }}" class="{{ request()->is('admin/pembayaran*') ? 'active' : '' }}">Kelola Pembayaran</a>
            <a href="{{ route('admin.pengembalian') }}" class="{{ request()->is('admin/pengembalian*') ? 'active' : '' }}">Kelola Pengembalian</a>
            <a href="{{ route('admin.laporan') }}" class="{{ request()->is('admin/laporan*') ? 'active' : '' }}">Laporan</a>
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

                <img src="{{ auth()->user()->photo 
                    ? asset('storage/' . auth()->user()->photo) . '?v=' . time()
                    : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=2563eb&color=fff' }}"
                    alt="Avatar"
                    class="profile-avatar">
            </a>
        </div>

        @yield('content')

    </div>

</div>

</body>
</html>