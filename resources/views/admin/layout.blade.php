<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin')</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 220px;
            background: #1e293b;
            color: #fff;
            padding: 20px;
        }
        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        .sidebar a:hover {
            background: #334155;
        }
        .content {
            flex: 1;
            padding: 20px;
            background: #f1f5f9;
        }
        .header {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Admin Desa</h2>
        <a href="/admin/dashboard">Dashboard</a>
        <a href="/fasilitas">Fasilitas</a>
        <a href="#">Booking</a>
        <a href="#">Pembayaran</a>
        <a href="/">Logout</a>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <div class="header">
            <h3>@yield('page-title')</h3>
        </div>

        @yield('content')
    </div>
</div>

</body>
</html>
