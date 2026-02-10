<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pengunjung</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
        }

        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: #1e3a8a;
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
            background-color: #1e40af;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        table th {
            background-color: #1e3a8a;
            color: white;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Pengunjung</h2>
    <a href="/user/dashboard">Dashboard</a>
    <a href="#">Jadwal Fasilitas</a>
    <a href="#">Ajukan Penyewaan</a>
    <a href="/">Logout</a>
</div>

<div class="content">
    <h1>Dashboard Pengunjung</h1>

    <div class="card">
        <h3>Informasi</h3>
        <p>Selamat datang di sistem penyewaan fasilitas Desa Kesamben.</p>
    </div>

    <div class="card">
        <h3>Jadwal Penyewaan</h3>
        <table>
            <tr>
                <th>Fasilitas</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Gedung Serbaguna</td>
                <td>12 Maret 2026</td>
                <td>08.00 - 16.00</td>
                <td>Disewa</td>
            </tr>
            <tr>
                <td>Lapangan Desa</td>
                <td>13 Maret 2026</td>
                <td>07.00 - 12.00</td>
                <td>Tersedia</td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
