@extends('admin.layout')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('content')

<!-- Kita tambahkan Font Awesome di layout utama, jika belum ada bisa di-load di sini -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
.form-card{
    max-width:550px;
    margin:0 auto;
    background:#ffffff;
    border:2px solid #cbd5e1;
    border-radius:8px;
    padding:25px;
}
.form-card h2{
    text-align:center;
    margin-bottom:25px;
    color:#1f2937;
}
.form-group{
    margin-bottom:18px;
    position: relative;
}
.form-group label{
    display:block;
    margin-bottom:6px;
    font-weight:600;
    color:#1f2937;
}
.form-group input, .form-group select, .form-group textarea{
    width:100%;
    padding:10px;
    border:2px solid #cbd5e1;
    border-radius:4px;
    font-size:14px;
    background: white;
    box-sizing: border-box;
}
.form-group textarea {
    resize: none;
    height: 80px;
}
.error-text{
    color:red;
    font-size:13px;
    margin-top:5px;
}
.password-wrapper{
    position:relative;
}
.password-wrapper input{
    padding-right:40px;
}
.toggle-password{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    font-size:16px;
    color:#6b7280;
}
.btn-submit{
    background:#2563eb;
    color:white;
    padding:10px 18px;
    border:none;
    border-radius:4px;
    cursor:pointer;
    font-weight: 600;
}
.btn-submit:hover{
    background:#1d4ed8;
}
.btn-back{
    margin-left:10px;
    text-decoration:none;
    color:#374151;
}
</style>

<div class="form-card">
    <h2>Formulir Tambah User</h2>

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        {{-- NIK --}}
        <div class="form-group">
            <label>NIK (Nomor Induk Kependudukan)</label>
            <input 
                type="text" 
                name="nik" 
                value="{{ old('nik') }}" 
                placeholder="Masukkan 16 digit NIK"
                maxlength="16"
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                required
            >
            @error('nik') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Nama (disesuaikan dari 'name' menjadi 'nama') --}}
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" placeholder="Masukkan nama lengkap" value="{{ old('nama') }}" required>
            @error('nama') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Email --}}
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Masukkan alamat email" value="{{ old('email') }}" required>
            @error('email') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- No HP (Tambahan agar sama dengan register) --}}
        <div class="form-group">
            <label>No. HP</label>
            <input type="text" name="no_hp" placeholder="Masukkan nomor HP aktif" value="{{ old('no_hp') }}" required>
            @error('no_hp') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Alamat (Tambahan agar sama dengan register) --}}
        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" placeholder="Masukkan alamat lengkap" required>{{ old('alamat') }}</textarea>
            @error('alamat') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Role User (Khusus Dashboard Admin) --}}
        <div class="form-group">
            <label>Role User</label>
            <select name="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="kaur" {{ old('role') == 'kaur' ? 'selected' : '' }}>Admin (Kaur)</option>
                <option value="penyewa" {{ old('role') == 'penyewa' ? 'selected' : '' }}>Penyewa</option>
            </select>
            @error('role') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Password --}}
        <div class="form-group">
            <label>Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="reg_pass" placeholder="Masukkan password" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('reg_pass', this)"></i>
            </div>
            @error('password') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Konfirmasi Password (Tambahan agar sama dengan register) --}}
        <div class="form-group">
            <label>Konfirmasi Password</label>
            <div class="password-wrapper">
                <input type="password" name="password_confirmation" id="conf_pass" placeholder="Ulangi password" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('conf_pass', this)"></i>
            </div>
            @error('password_confirmation') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div style="margin-top: 25px;">
            <button type="submit" class="btn-submit">Simpan User</button>
            <a href="{{ route('users.index') }}" class="btn-back">Kembali</a>
        </div>
    </form>
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

@endsection