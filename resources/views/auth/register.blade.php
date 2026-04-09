<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { display: flex; height: 100vh; justify-content: center; align-items: center; gap: 60px; }
        .logo img { width: 260px; }
        .card { background: #e9edf2; padding: 30px 35px; width: 360px; border-radius: 6px; box-shadow: 0 0 0 1px #cfd6de; }
        .card h2 { text-align: center; margin-bottom: 20px; }

        .form-group { margin-bottom: 15px; position: relative; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #cfd6de;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        textarea {
            resize: none;
            height: 70px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c63ff;
        }

        .btn {
            width: 100%;
            background: #6c63ff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover { background: #5a52e0; }

        .text { text-align: center; margin-top: 12px; font-size: 13px; }

        .text a { color: #6c63ff; text-decoration: none; }

        .error {
            color: red;
            font-size: 12px;
            margin-top: 3px;
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

        {{-- ERROR GLOBAL --}}
        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <input type="text" name="nik" placeholder="Masukkan NIK" value="{{ old('nik') }}" required>
            </div>

            <div class="form-group">
                <input type="text" name="nama" placeholder="Masukkan Nama" value="{{ old('nama') }}" required>
            </div>

            <div class="form-group">
                <input type="email" name="email" placeholder="Masukkan Email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <input type="text" name="no_hp" placeholder="Masukkan No HP" value="{{ old('no_hp') }}" required>
            </div>

            <div class="form-group">
                <textarea name="alamat" placeholder="Masukkan Alamat" required>{{ old('alamat') }}</textarea>
            </div>

            <div class="form-group">
                <input type="password" name="password" id="reg_pass" placeholder="Masukkan Password" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('reg_pass', this)"></i>
            </div>

            <div class="form-group">
                <input type="password" name="password_confirmation" id="conf_pass" placeholder="Masukkan Ulang Password" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('conf_pass', this)"></i>
            </div>

            <button class="btn" type="submit">Registrasi</button>
        </form>

        <div class="text">
            Sudah punya akun? <a href="{{ route('login') }}">Login</a>
        </div>
    </div>
</div>

<script>
    function togglePass(id, el) {
        const input = document.getElementById(id);
        if (input.type === "password") {
            input.type = "text";
            el.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            input.type = "password";
            el.classList.replace("fa-eye-slash", "fa-eye");
        }
    }
</script>

</body>
</html>