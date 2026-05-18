<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Pengunjung')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
        }

        /* ===== SIDEBAR (DIPERBAIKI DENGAN FLEXBOX) ===== */
        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: #1e3a8a;
            position: fixed;
            top: 0;
            left: 0;
            color: white;
            z-index: 100;
            
            /* Menggunakan Flexbox untuk memisahkan menu atas & footer logout */
            display: flex;
            flex-direction: column;
            justify-content: space-between; 
            overflow: hidden;
        }

        /* Container Menu Navigasi Atas */
        .sidebar-menu {
            width: 100%;
            overflow-y: auto; /* Scroll internal jika menu sangat banyak */
        }

        /* Gaya Area Logo */
        .sidebar-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 10px;
        }

        .sidebar-logo .logo-img {
            width: 55px;
            height: auto;
            margin-bottom: 10px;
            object-fit: contain;
            border-radius: 8px;
            background: transparent;
        }

        .sidebar h2 {
            text-align: center;
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* Gaya Default Link & Button Navigasi */
        .sidebar a,
        .sidebar button {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 14px 20px;
            color: rgba(255, 255, 255, 0.75); /* Teks sedikit redup saat tidak aktif */
            text-decoration: none;
            background: none;
            border: none;
            font-size: 15px;
            font-family: inherit;
            text-align: left;
            cursor: pointer;
            box-sizing: border-box;
            transition: all 0.2s ease-in-out;
            border-left: 4px solid transparent; /* Cadangan border untuk efek aktif */
        }

        /* Sinkronisasi Jarak Icon FontAwesome */
        .sidebar a i, 
        .sidebar button i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        /* ===== 1. EFEK HOVER (Saat Kursor Menempel) ===== */
        .sidebar a:hover,
        .sidebar button:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.1); /* Background putih transparan tipis */
            padding-left: 24px; /* Efek bergeser smooth ke kanan */
        }

        /* ===== 2. EFEK ACTIVE (Halaman Yang Sedang Dibuka) ===== */
        .sidebar .active,
        .sidebar .active:hover {
            background-color: #1e40af !important;
            color: #ffffff !important;
            font-weight: bold;
            border-left: 4px solid #38bdf8; /* Garis vertikal indikator warna biru muda */
            padding-left: 20px; /* Dikunci agar tidak bergeser lagi saat di-hover */
        }

        /* Container Footer Sidebar (Logout) */
        .sidebar-footer {
            width: 100%;
            border-top: 1px solid rgba(255,255,255,0.15);
            background-color: #1a337e; /* Warna dasar sedikit lebih gelap */
        }

        /* Hover khusus tombol logout jadi warna merah */
        .sidebar-footer button:hover {
            background-color: #ef4444; 
            color: white;
            padding-left: 20px; /* Tetap stabil di tempat */
        }

        /* ===== MAIN WRAPPER ===== */
        .main-wrapper {
            margin-left: 220px;
            min-height: 100vh;
            padding-top: 10px;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            height: 70px;
            background: rgb(215, 214, 229);
            display: flex;
            justify-content: space-between; 
            align-items: center;
            padding: 0 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin: 20px;            
            border-radius: 12px;     
            position: sticky;
            top: 20px;               
            z-index: 99;
        }

        .page-title-area {
            display: flex;
            flex-direction: column;
        }

        .page-title-area h3 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 50px;
            transition: 0.3s;
        }

        .user-nav:hover {
            background: #74d0ec;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }

        .user-status {
            font-size: 12px;
            color: #6b7280;
        }

        .nav-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 2px solid #2563eb;
            object-fit: cover;
        }

        /* ===== CONTENT ===== */
        .content {
            padding: 0 25px 25px 25px; 
        }

        /* ===== TABLE & CARD ===== */
        .card {
            background: rgb(165, 193, 238);
            padding: 20px;
            border-radius: 8px; 
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        table th {
            background-color: #1e3a8a;
            color: white;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .bg-pending { background-color: #f59e0b; }
        .bg-success { background-color: #10b981; }
        .bg-danger { background-color: #ef4444; }
    </style>
</head>
<body>

    @include('user.layouts.sidebar')

    <div class="main-wrapper">

        {{-- TOPBAR DENGAN TAMPILAN MELAYANG --}}
        <div class="topbar">
            <div class="page-title-area">
                <h3>@yield('page_title_content')</h3>
            </div>

            <a href="{{ route('user.profile') }}" class="user-nav">
                <div class="user-details">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-status">Pengunjung Aktif</div>
                </div>

                <img src="{{ auth()->user()->photo 
                    ? asset('storage/' . auth()->user()->photo) . '?v=' . time()
                    : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=2563eb&color=fff' }}"
                    class="nav-avatar"
                    alt="Avatar">
            </a>
        </div>

        {{-- Content Area --}}
        <div class="content">
            @yield('content')
        </div>

    </div>

</body>
</html>