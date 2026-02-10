<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda - Sistem Penyewaan Fasilitas Desa Kesamben</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
        }

        /* HEADER */
        .header {
            background-color: #1e3a8a;
            color: white;
            text-align: center;
            padding: 25px;
        }

        /* NAVBAR */
        .navbar {
            background-color: #ffffff;
            padding: 12px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .navbar a {
            margin: 0 15px;
            text-decoration: none;
            color: #1e3a8a;
            font-weight: bold;
        }

        .login-btn {
            background-color: #f97316;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
        }

        /* CONTENT */
        .content {
            padding: 40px;
            text-align: center;
        }

        .fasilitas {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .card {
            background-color: white;
            padding: 20px;
            width: 250px;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }

        /* JADWAL */
        .jadwal {
            margin-top: 50px;
        }

        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: white;
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

        .status-booked {
            color: red;
            font-weight: bold;
        }

        .status-available {
            color: green;
            font-weight: bold;
        }

        .btn-booking {
            display: inline-block;
            margin-top: 30px;
            background-color: #1e3a8a;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
        }

        /* FOOTER */
        .footer {
            background-color: #1e3a8a;
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <h1>Sistem Penyewaan Fasilitas Desa Kesamben</h1>
        <p>Pelayanan pemesanan fasilitas desa secara online</p>
    </div>

    <!-- NAVBAR -->
    <div class="navbar">
        <a href="#">Beranda</a>
        <a href="#">Fasilitas</a>
        <a href="#">Jadwal</a>
        <a href="#">Tentang</a>
        <a href="{{ route('login') }}" class="login-btn">Login</a>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <h2>Daftar Fasilitas Desa</h2>
        <div class="fasilitas">
            <div class="card">
                <h3>Gedung Serbaguna</h3>
                <p>Untuk pertemuan dan acara masyarakat.</p>
            </div>

            <div class="card">
                <h3>Lapangan Desa</h3>
                <p>Fasilitas olahraga dan kegiatan desa.</p>
            </div>

            <div class="card">
                <h3>Balai Desa</h3>
                <p>Untuk rapat resmi dan kegiatan pemerintahan.</p>
            </div>
        </div>

        <!-- JADWAL -->
        <div class="jadwal">
            <h2>Jadwal Penyewaan Fasilitas</h2>
            <p>Informasi ketersediaan fasilitas desa</p>

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
                    <td class="status-booked">Disewa</td>
                </tr>
                <tr>
                    <td>Lapangan Desa</td>
                    <td>13 Maret 2026</td>
                    <td>07.00 - 12.00</td>
                    <td class="status-available">Tersedia</td>
                </tr>
                <tr>
                    <td>Balai Desa</td>
                    <td>14 Maret 2026</td>
                    <td>09.00 - 15.00</td>
                    <td class="status-booked">Disewa</td>
                </tr>
            </table>

            <a href="#" class="btn-booking">Ajukan Penyewaan</a>
        </div>

    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>&copy; {{ date('Y') }} Desa Kesamben</p>
    </div>

</body>
</html>
