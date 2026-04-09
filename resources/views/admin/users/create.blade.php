@extends('admin.layout')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('content')

<style>
.form-card{
    max-width:500px;
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
}
.form-group label{
    display:block;
    margin-bottom:6px;
    font-weight:600;
    color:#1f2937;
}
.form-group input, .form-group select{
    width:100%;
    padding:10px;
    border:2px solid #cbd5e1;
    border-radius:4px;
    font-size:14px;
    background: white;
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
    right:10px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    font-size:18px;
    color:#6b7280;
}
.btn-submit{
    background:#2563eb;
    color:white;
    padding:10px 18px;
    border:none;
    border-radius:4px;
    cursor:pointer;
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
    <h2>Form Tambah User</h2>

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

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

        <div class="form-group">
            <label>Nama</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            @error('name') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
            @error('email') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Role User</label>
            <select name="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="kaur" {{ old('role') == 'kaur' ? 'selected' : '' }}>Admin (Kaur)</option>
                <option value="penyewa" {{ old('role') == 'penyewa' ? 'selected' : '' }}>Penyewa</option>
            </select>
            @error('role') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" required>
                <span class="toggle-password" onclick="togglePassword()">👁</span>
            </div>
            @error('password') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-submit">Simpan</button>
        <a href="{{ route('users.index') }}" class="btn-back">Kembali</a>
    </form>
</div>

<script>
function togglePassword(){
    const password = document.getElementById("password");
    password.type = password.type === "password" ? "text" : "password";
}
</script>

@endsection