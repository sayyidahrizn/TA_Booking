<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>@yield('title', 'Dashboard Admin')</title>

    <style>
        /* ================= RESET ================= */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
            font-size: 14px; /* FONT GLOBAL (KUNCI) */
        }

        /* ================= LAYOUT ================= */
        .container {
            display: flex;
            min-height: 100vh;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: 220px;              /* KUNCI LEBAR */
            height: 100vh;
            background-color: #111827;
            color: #ffffff;
            position: fixed;
            left: 0;
            top: 0;
        }

        .sidebar h2 {
            height: 60px;              /* KUNCI TINGGI */
            line-height: 60px;
            margin: 0;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.3);
        }

        .sidebar a {
            display: block;
            height: 45px;              /* KUNCI TINGGI MENU */
            line-height: 45px;
            padding: 0 20px;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
        }

        .sidebar a:hover {
            background-color: #1f2937;
        }

        /* ================= CONTENT ================= */
        .content {
            margin-left: 220px;        /* SESUAI SIDEBAR */
            width: calc(100% - 220px);
            padding: 25px;
        }

        /* ================= HEADER ================= */
        .header {
            height: 55px;              /* KUNCI TINGGI */
            line-height: 55px;
            background-color: #ffffff;
            padding: 0 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .header h3 {
            margin: 0;
            font-size: 16px;           /* KUNCI UKURAN JUDUL */
            font-weight: bold;
        }

        /* ================= CARD ================= */
        .card {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            font-size: 14px;
        }

        .card h3 {
            margin-top: 0;
            font-size: 15px;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- ========== SIDEBAR ========== -->
    <div class="sidebar">
        <h2>Admin Desa</h2>
        <a href="/admin/dashboard">Dashboard</a>
        <a href="{{ route('fasilitas.index') }}">Kelola Fasilitas</a>
        <a href="{{ route('users.index') }}">Kelola User</a>
        <a href="/admin/penyewaan">Kelola Booking</a>
        <a href="#">Kelola Pembayaran</a>
        <a href="#">Kelola Pengembalian</a>
        <a href="/">Logout</a>
    </div>

    <!-- ========== CONTENT ========== -->
    <div class="content">

        <!-- HEADER -->
        <div class="header">
            <h3>@yield('page-title')</h3>
        </div>

        <!-- ISI HALAMAN -->
        @yield('content')

    </div>

</div>

</body>
</html>
