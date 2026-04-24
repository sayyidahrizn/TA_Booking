<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
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

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: #111827;
            color: #ffffff;
            position: fixed;
            left: 0;
            top: 0;
        }

        .sidebar h2 {
            height: 60px;
            line-height: 60px;
            margin: 0;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.3);
        }

        .sidebar a {
            display: block;
            height: 45px;
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
            margin-left: 220px;
            width: calc(100% - 220px);
            padding: 25px;
        }

        /* ================= HEADER ================= */
        .header {
            min-height: 60px;
            background-color: #ffffff;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #111827;
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
            background: #f1f5f9;
        }

        .profile-info {
            text-align: right;
        }

        .profile-name {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }

        .profile-role {
            font-size: 11px;
            color: #64748b;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #2563eb;
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

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Admin Desa</h2>

        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('fasilitas.index') }}">Kelola Fasilitas</a>
        <a href="{{ route('users.index') }}">Kelola User</a>
        <a href="{{ route('admin.penyewaan.index') }}">Kelola Booking</a>
        <a href="{{ route('admin.pembayaran.index') }}">Kelola Pembayaran</a>
        <a href="{{ route('admin.pengembalian') }}">Kelola Pengembalian</a>
        <a href="/">Logout</a>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <!-- HEADER -->
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

        <!-- ISI -->
        @yield('content')

    </div>

</div>

</body>
</html>