@extends('admin.layout')

@section('title', 'Data User')
@section('page-title', 'Data User')

@section('content')

<style>
/* ================= NOTIFIKASI ================= */
.alert-notif {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 600;
    color: white;
    display: flex;
    align-items: center;
    transition: opacity 0.5s ease;
}
.alert-success { background: #10b981; }
.alert-error { background: #ef4444; }

/* ================= BUTTON ================= */
.btn{
    padding:6px 12px;
    border-radius:4px;
    text-decoration:none;
    font-size:13px;
    display:inline-block;
    border: none;
    cursor: pointer;
}
.btn-add{
    background:#2563eb;
    color:white;
}

/* ================= ICON BUTTON ================= */
.icon-btn{
    border:none;
    background:none;
    cursor:pointer;
    font-size:16px;
    padding:5px;
}
.icon-edit{ color:#14b8a6; }
.icon-delete{ color:#ef4444; }
.icon-btn:hover{ transform:scale(1.15); }

/* ================= ROLE BADGE ================= */
.badge-admin{
    background:#ef4444;
    color:white;
    padding:4px 8px;
    border-radius:4px;
    font-size:12px;
}
.badge-user{
    background:#3b82f6;
    color:white;
    padding:4px 8px;
    border-radius:4px;
    font-size:12px;
}

/* ================= TABLE ================= */
.table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}
.table th, .table td{
    border:1px solid #d1d5db;
    padding:10px;
}
.table th{ background:#e5e7eb; }
.text-left{ text-align:left; }
.text-center{ text-align:center; }
.text-muted{ color: #6b7280; font-family: monospace; } /* Styling tambahan untuk NIK */
.aksi{ display:flex; justify-content:center; gap:10px; }

/* ================= PAGINATION ================= */
.pagination-container {
    margin-top: 25px;
    display: flex;
    justify-content: center;
}
.pagination-container nav ul {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 5px;
}
.pagination-container nav ul li a, 
.pagination-container nav ul li span {
    display: block;
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    text-decoration: none;
    color: #2563eb;
    border-radius: 4px;
    background: white;
    font-size: 14px;
}
.pagination-container nav ul li.active span {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
}
.pagination-container nav ul li.disabled span {
    color: #9ca3af;
    background: #f3f4f6;
    cursor: not-allowed;
}
.pagination-container .flex.justify-between.flex-1,
.pagination-container .hidden.sm\:flex-1 {
    display: none !important;
}

/* ================= FITUR TAMBAHAN (SEARCH & FILTER) ================= */
.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 15px;
    flex-wrap: wrap;
}
.search-filter-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
}
.input-control {
    padding: 7px 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
    outline: none;
}
.btn-report {
    background: #059669;
    color: white;
}
.btn-search {
    background: #4b5563;
    color: white;
}
</style>

{{-- NOTIFIKASI --}}
@if(session('success'))
<div class="alert-notif alert-success" id="notif-user">
    ✅ {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert-notif alert-error" id="notif-user">
    ❌ {{ session('error') }}
</div>
@endif

<div class="header-actions">
    <div class="left-btns">
        <a href="{{ route('users.create') }}" class="btn btn-add">
            + Tambah User
        </a>
        <a href="{{ route('users.laporan') }}" class="btn btn-report">
            <i class="fa-solid fa-file-pdf"></i> Laporan
        </a>
    </div>

    <form action="{{ route('users.index') }}" method="GET" class="search-filter-wrapper">
        <select name="role" class="input-control">
            <option value="">Semua Role</option>
            <option value="kaur" {{ request('role') == 'kaur' ? 'selected' : '' }}>Admin</option>
            <option value="penyewa" {{ request('role') == 'penyewa' ? 'selected' : '' }}>Penyewa</option>
        </select>
        
        <input type="text" name="search" class="input-control" placeholder="Cari Nama atau NIK..." value="{{ request('search') }}">
        
        <button type="submit" class="btn btn-search">
            <i class="fa-solid fa-magnifying-glass"></i> Cari
        </button>

        @if(request('search') || request('role'))
            <a href="{{ route('users.index') }}" class="btn" style="background:#9ca3af; color:white;">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">No</th>
                <th class="text-left">Nama</th>
                <th class="text-left">NIK</th> {{-- Kolom NIK ditambahkan --}}
                <th class="text-left">Email</th>
                <th class="text-center">Role</th>
                <th class="text-center">Dibuat</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            <tr>
                <td class="text-center">
                    {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                </td>
                <td class="text-left"><strong>{{ $u->name }}</strong></td>
                <td class="text-left text-muted">
                    {{ $u->nik ?? '-' }} {{-- Menampilkan NIK, atau strip jika kosong --}}
                </td>
                <td class="text-left">{{ $u->email }}</td>
                <td class="text-center">
                    @if($u->role == 'kaur')
                        <span class="badge-admin">Admin</span>
                    @else
                        <span class="badge-user">Penyewa</span>
                    @endif
                </td>
                <td class="text-center">{{ $u->created_at->format('d M Y') }}</td>
                <td class="text-center">
                    <div class="aksi">
                        <a href="{{ route('users.edit', $u->id) }}" class="icon-btn icon-edit" title="Edit">
                            <i class="fa-solid fa-pen"></i>
                        </a>

                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini?')" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn icon-delete" title="Hapus">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Data user belum tersedia</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Tombol Navigasi Halaman --}}
<div class="pagination-container">
    {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
</div>

<script>
// Notifikasi hilang otomatis
setTimeout(function(){
    const notif = document.getElementById('notif-user');
    if(notif){
        notif.style.opacity='0';
        setTimeout(()=>{
            notif.remove();
        },500);
    }
},3000);
</script>

@endsection