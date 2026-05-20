@extends('admin.layout')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .form-card {
        max-width: 550px;
        margin: 0 auto;
        background: #ffffff;
        border: 2px solid #cbd5e1;
        border-radius: 8px;
        padding: 25px;
    }
    .form-card h2 { text-align: center; margin-bottom: 25px; color: #1f2937; }
    .form-group { margin-bottom: 18px; position: relative; }
    .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #1f2937; }
    .form-group input, .form-group select, .form-group textarea { 
        width: 100%; 
        padding: 10px; 
        border: 2px solid #cbd5e1; 
        border-radius: 4px; 
        font-size: 14px; 
        background: white; 
        box-sizing: border-box;
    }
    .form-group textarea { resize: none; height: 80px; }
    .error-text { color: red; font-size: 13px; margin-top: 5px; }
    .password-wrapper { position: relative; }
    .password-wrapper input { padding-right: 40px; }
    .toggle-password { 
        position: absolute; 
        right: 12px; 
        top: 50%; 
        transform: translateY(-50%); 
        cursor: pointer; 
        font-size: 16px; 
        color: #6b7280; 
    }
    .btn-submit { background: #2563eb; color: white; padding: 10px 18px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
    .btn-submit:hover { background: #1d4ed8; }
    .btn-back { margin-left: 10px; text-decoration: none; color: #374151; }
    .info-text { font-size: 12px; color: #64748b; margin-top: 4px; }
</style>

<div class="form-card">
    <h2>Edit Data User</h2>

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- NIK --}}
        <div class="form-group">
            <label>NIK</label>
            <input 
                type="text" 
                name="nik" 
                value="{{ old('nik', $user->nik) }}" 
                maxlength="16"
                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                required
            >
            @error('nik') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Nama (disesuaikan menjadi 'nama') --}}
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="{{ old('nama', $user->name) }}" required>
            @error('nama') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Email --}}
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            @error('email') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- No HP (Tambahan) --}}
        <div class="form-group">
            <label>No. HP</label>
            <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required>
            @error('no_hp') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Alamat (Tambahan) --}}
        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" required>{{ old('alamat', $user->alamat) }}</textarea>
            @error('alamat') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Role --}}
        <div class="form-group">
            <label>Role User</label>
            <select name="role" required>
                <option value="kaur" {{ old('role', $user->role) == 'kaur' ? 'selected' : '' }}>Admin (Kaur)</option>
                <option value="penyewa" {{ old('role', $user->role) == 'penyewa' ? 'selected' : '' }}>Penyewa</option>
            </select>
            @error('role') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 25px 0;">

        {{-- Password Baru --}}
        <div class="form-group">
            <label>Password Baru (Opsional)</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="reg_pass" placeholder="Isi jika ingin ganti password">
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('reg_pass', this)"></i>
            </div>
            <p class="info-text">*Kosongkan jika tidak ingin mengubah password.</p>
            @error('password') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- Konfirmasi Password Baru --}}
        <div class="form-group">
            <label>Konfirmasi Password Baru</label>
            <div class="password-wrapper">
                <input type="password" name="password_confirmation" id="conf_pass" placeholder="Ulangi password baru">
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('conf_pass', this)"></i>
            </div>
            @error('password_confirmation') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div style="margin-top: 25px;">
            <button type="submit" class="btn-submit">Update Data</button>
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