<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: #111827;
            position: fixed;
            color: white;
        }

        .sidebar h2 {
            text-align: center;
            padding: 20px 10px;
            border-bottom: 1px solid rgba(255,255,255,0.3);
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #1f2937;
        }

        .content {
            margin-left: 220px;
            padding: 25px;
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin</h2>
    <a href="/admin/dashboard">Dashboard</a>
    <a href="{{ route('fasilitas.index') }}">Kelola Fasilitas</a>
    <a href="#">Kelola Jadwal</a>
    <a href="#">Data Penyewaan</a>
    <a href="/">Logout</a>
</div>

<div class="content">
    <h1>Dashboard Admin</h1>

    <div class="card">
        <h3>Informasi Sistem</h3>
        <p>Halaman ini digunakan untuk mengelola fasilitas dan jadwal penyewaan desa.</p>
    </div>

    <div class="card">
        <h3>Ringkasan</h3>
        <ul>
            <li>Total Fasilitas : 3</li>
            <li>Penyewaan Aktif : 2</li>
            <li>Penyewaan Menunggu Verifikasi : 1</li>
        </ul>
    </div>
</div>

</body>
</html>
