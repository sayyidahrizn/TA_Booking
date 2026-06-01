@extends('user.layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
<style>
    /* --- PALET WARNA BARU (Modern & Estetik) --- */
    :root {
        --primary: #4f46e5;       /* Indigo modern untuk tombol/aksen utama */
        --primary-hover: #4338ca; /* Indigo gelap saat hover */
        --bg-main: #f8fafc;       /* Abu-abu super lembut untuk background */
        --text-dark: #1e293b;     /* Slate gelap untuk teks utama */
        --text-muted: #64748b;    /* Slate medium untuk info/subteks */
        --border-color: #e2e8f0;  /* Garis pembatas yang tipis & clean */
    }

    .content-padding { padding: 20px; }
    .profile-grid { display: grid; grid-template-columns: 320px 1fr; gap: 30px; max-width: 1140px; margin: 0 auto; }
    
    /* Card Styling */
    .profile-card { background: #ffffff; border-radius: 16px; border: 1px solid var(--border-color); padding: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); }
    .profile-card-static { text-align: center; }
    
    /* Avatar */
    .avatar-wrapper { position: relative; display: inline-block; margin-bottom: 20px; }
    .main-avatar { width: 128px; height: 128px; border-radius: 50%; border: 4px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.08); object-fit: cover; }
    .upload-btn { position: absolute; bottom: 5px; right: 5px; background: var(--primary); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid #fff; transition: 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.15); }
    .upload-btn:hover { background: var(--primary-hover); transform: scale(1.05); }

    /* Form & Typography */
    .profile-title { color: var(--text-dark); font-weight: 700; margin: 0 0 5px 0; font-size: 1.25rem; }
    .profile-email { color: var(--text-muted); font-size: 14px; margin: 0; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; color: var(--text-dark); }
    .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 4px; font-size: 14px; background: #ffffff; color: var(--text-dark); transition: 0.2s ease; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
    
    /* Buttons */
    .btn-submit { background: var(--primary); color: white; border: none; padding: 12px 28px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: 0.2s ease; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2); }
    .btn-submit:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3); }
    
    /* Section Boxes */
    .password-section { background: #f8fafc; border: 1px solid var(--border-color); padding: 25px; border-radius: 12px; margin-top: 25px; }
    .error-text { color: #ef4444; font-size: 12px; margin-top: 4px; display: block; }
    
    /* Responsive */
    @media (max-width: 768px) { 
        .profile-grid { grid-template-columns: 1fr; gap: 20px; }
        .form-grid-2 { grid-template-columns: 1fr !important; }
    }
</style>

<div class="content-padding">
    <div class="profile-grid">
        <!-- KIRI: Card Info Static -->
        <div class="profile-card profile-card-static">
            <div class="avatar-wrapper">
                <img id="avatar-preview-user" 
                     src="{{ $user->photo ? asset('storage/' . $user->photo) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=4f46e5&color=fff&size=150' }}"
                     class="main-avatar"
                     alt="Avatar User">
                
                <label for="photo-user" class="upload-btn">
                    <i class="fas fa-camera" style="font-size: 13px;"></i>
                </label>
            </div>

            <h3 class="profile-title">{{ $user->name }}</h3>
            <p class="profile-email">{{ $user->email }}</p>

            <hr style="margin: 25px 0; border: 0; border-top: 1px solid var(--border-color);">

            <div style="text-align: left; font-size: 13px; color: var(--text-muted); line-height: 2;">
                <p style="margin: 4px 0;"><i class="fas fa-clock" style="width: 20px; color: var(--primary);"></i> Bergabung: {{ $user->created_at ? $user->created_at->format('Y') : '-' }}</p>
                <p style="margin: 4px 0;"><i class="fas fa-user-check" style="width: 20px; color: #10b981;"></i> Status: <span style="color: #10b981; font-weight: 600;">Pengguna Aktif</span></p>
            </div>
        </div>

        <!-- KANAN: Form Pengaturan -->
        <div class="profile-card">
            <h4 style="margin-top: 0; margin-bottom: 25px; color: var(--text-dark); font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-user-edit" style="color: var(--primary);"></i> Pengaturan Akun
            </h4>

            @if(session('success'))
                <div style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <input type="file" id="photo-user" name="photo" style="display: none;" accept="image/*" onchange="previewImage(this, 'avatar-preview-user')">

                <div class="form-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                        @error('name') <small class="error-text">{{ $message }}</small> @enderror
                    </div>

                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                        @error('email') <small class="error-text">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="form-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div>
                        <label class="form-label">No. Handphone</label>
                        <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $user->no_hp) }}" placeholder="Contoh: 08123456789">
                        @error('no_hp') <small class="error-text">{{ $message }}</small> @enderror
                    </div>
                </div>

                <!-- Bagian Password Baru yang Lebih Kalem -->
                <div class="password-section">
                    <h5 style="margin: 0 0 15px 0; color: var(--text-dark); font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-lock" style="color: var(--text-muted); font-size: 12px;"></i> Ganti Kata Sandi
                    </h5>
                    <div class="form-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label class="form-label" style="color: var(--text-muted);">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                            @error('password') <small class="error-text">{{ $message }}</small> @enderror
                        </div>
                        <div>
                            <label class="form-label" style="color: var(--text-muted);">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection