<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }

        .container {
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
            gap: 60px;
        }

        .logo img {
            width: 260px;
        }

        .card {
            background: #e9edf2;
            padding: 30px 35px;
            width: 360px;
            border-radius: 6px;
            box-shadow: 0 0 0 1px #cfd6de;
        }

        .card h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #cfd6de;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn {
            width: 100%;
            background: #6c63ff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #5a52e0;
        }

        .text {
            text-align: center;
            margin-top: 12px;
            font-size: 13px;
        }

        .text a {
            color: #6c63ff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">
        <img src="{{ asset('images/LOGODESA.png') }}" alt="Logo">
    </div>

    <div class="card">
        <h2>Form Registrasi</h2>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <input type="text" name="nama" placeholder="Masukkan Nama" required>
            </div>

            <div class="form-group">
                <input type="email" name="email" placeholder="Masukkan Email" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Masukkan Password" required>
            </div>

            <div class="form-group">
                <input type="password" name="password_confirmation" placeholder="Masukkan Ulang Password" required>
            </div>

            <button class="btn" type="submit">Registrasi</button>
        </form>

        <div class="text">
            Sudah punya akun? <a href="{{ route('login') }}">Login</a>
        </div>
    </div>
</div>

</body>
</html>
