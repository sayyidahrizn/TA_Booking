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

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: #1e3a8a;
            position: fixed;
            top: 0;
            left: 0;
            color: white;
            overflow: hidden;
            z-index: 100;
        }

        .sidebar h2 {
            text-align: center;
            padding: 20px 10px;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            margin: 0;
        }

        .sidebar a,
        .sidebar button {
            display: block;
            width: 100%;
            padding: 14px 20px;
            color: white;
            text-decoration: none;
            background: none;
            border: none;
            font-size: 16px;
            font-family: inherit;
            text-align: left;
            cursor: pointer;
            box-sizing: border-box;
            transition: 0.2s;
        }

        .sidebar a:hover,
        .sidebar button:hover {
            background-color: #1e40af;
        }

        .sidebar .active {
            background-color: #1e40af;
            font-weight: bold;
        }

        /* ===== MAIN WRAPPER ===== */
        .main-wrapper {
            margin-left: 220px;
            min-height: 100vh;
            padding-top: 10px; /* Memberikan sedikit ruang di paling atas */
        }

        /* ===== TOPBAR (DIUBAH AGAR SESUAI GAMBAR) ===== */
        .topbar {
            height: 70px;
            background: rgb(215, 214, 229);
            display: flex;
            justify-content: space-between; 
            align-items: center;
            padding: 0 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            
            /* Penyesuaian Jarak agar Melayang */
            margin: 20px;            /* Memberikan jarak atas, bawah, kiri, kanan */
            border-radius: 12px;     /* Membuat sudut melengkung seperti di gambar */
            position: sticky;
            top: 20px;               /* Tetap melayang saat di-scroll */
            z-index: 99;
        }

        /* Container untuk judul di sebelah kiri */
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

        /* Navigasi Profil di Kanan */
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
            padding: 0 25px 25px 25px; /* Padding samping dan bawah */
        }

        /* ===== TABLE & CARD (FITUR TETAP) ===== */
        .card {
            background: rgb(165, 193, 238);
            padding: 20px;
            border-radius: 8px; /* Disamakan sedikit lebih bulat */
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
            {{-- Bagian Judul Halaman (Kiri) --}}
            <div class="page-title-area">
                <h3>@yield('page_title_content')</h3>
            </div>

            {{-- Bagian Profil (Kanan) --}}
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