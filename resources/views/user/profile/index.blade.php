@extends('user.layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
<style>
    .content-padding {
        padding: 10px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 30px;
        max-width: 1100px;
        margin: 0 auto;
    }

    .card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        padding: 30px;
    }

    .profile-card-static {
        text-align: center;
    }

    .main-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin-bottom: 20px;
        border: 4px solid #f1f5f9;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 13px;
        color: #475569;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 8px;
        font-size: 14px;
        background: #f8fafc;
        transition: 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(59,130,246,0.1);
    }

    .btn-submit {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-submit:hover {
        background: #2563eb;
        transform: translateY(-2px);
    }

    .error-text {
        color: red;
        font-size: 12px;
        margin-bottom: 10px;
        display: block;
    }

    @media (max-width: 768px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-padding">
    <div class="profile-grid">

        <div class="card profile-card-static">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=3b82f6&color=fff&size=150"
                 class="main-avatar"
                 alt="Avatar User">

            <h3 style="margin: 0;">{{ $user->name }}</h3>
            <p style="color: #64748b; font-size: 13px;">{{ $user->email }}</p>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #f1f5f9;">

            <div style="text-align: left; font-size: 12px; color: #64748b; line-height: 1.8;">
                <p><i class="fas fa-clock"></i> Bergabung: {{ $user->created_at ? $user->created_at->format('Y') : '-' }}</p>
                <p><i class="fas fa-user"></i> Status: Pengguna Aktif</p>
            </div>
        </div>

        <div class="card">
            <h4 style="margin-top: 0; margin-bottom: 25px;">
                <i class="fas fa-user-edit" style="color:#3b82f6;"></i> Pengaturan Akun
            </h4>

            @if(session('success'))
                <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('user.profile.update') }}" method="POST">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                        @error('name')
                            <small class="error-text">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                        @error('email')
                            <small class="error-text">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div style="background: #fff5f5; padding: 20px; border-radius: 12px; margin-top: 20px;">
                    <h5 style="margin: 0 0 15px 0; color: #e11d48; font-size: 14px;">Ganti Kata Sandi</h5>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Isi jika ingin diubah">
                            @error('password')
                                <small class="error-text">{{ $message }}</small>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password">
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
@endsection
