@extends('user.layouts.app')

@section('content')
<div class="container" style="max-width: 900px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
    
    {{-- Notifikasi Error & Validasi --}}
    @if($errors->any() || session('error'))
        <div style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; font-size: 14px;">
            <strong>Terjadi Kesalahan:</strong>
            <ul style="margin: 5px 0 0; padding-left: 20px;">
                @foreach ($errors->all() as $error) 
                    <li>{{ $error }}</li> 
                @endforeach
                @if(session('error')) 
                    <li>{{ session('error') }}</li> 
                @endif
            </ul>
        </div>
    @endif

    <div style="margin-bottom: 20px;">
        <a href="{{ route('user.penyewaan.index') }}" style="text-decoration: none; color: #4f46e5; font-weight: bold;">&larr; Kembali ke Daftar</a>
    </div>

    <h2 style="text-align: center; margin-bottom: 25px; color: #1f2937;">Form Booking Fasilitas</h2>

    <form action="{{ route('user.penyewaan.store') }}" method="POST" id="mainForm">
        @csrf
        
        {{-- Data Identitas Penyewa (OTOMATIS & READONLY) --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; border-bottom: 2px solid #f3f4f6; padding-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nama Lengkap Penyewa</label>
                <input type="text" name="nama_penyewa" value="{{ auth()->user()->name }}" readonly required 
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; background-color: #f3f4f6; color: #6b7280; cursor: not-allowed;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">NIK</label>
                <input type="text" name="nik" value="{{ auth()->user()->nik }}" readonly required 
                       style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; background-color: #f3f4f6; color: #6b7280; cursor: not-allowed;">
            </div>
        </div>

        {{-- Input Selector Fasilitas --}}
        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 25px; border: 1px dashed #4f46e5;">
            <h4 style="margin: 0 0 15px; color: #4f46e5;">Pilih Fasilitas & Periode</h4>
            <div style="display: grid; grid-template-columns: 2fr 1.5fr 1.5fr auto; gap: 15px; align-items: end;">
                <div>
                    <label style="font-size: 13px; font-weight: 600;">Fasilitas</label>
                    <select id="temp_id" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db;">
                        <option value="">-- Pilih --</option>
                        @foreach($fasilitas as $f)
                            <option value="{{ $f->id_fasilitas }}" data-nama="{{ $f->nama_fasilitas }}" data-harga="{{ $f->harga_sewa }}">
                                {{ $f->nama_fasilitas }} - Rp {{ number_format($f->harga_sewa, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 600;">Tgl Mulai</label>
                    <input type="date" id="temp_mulai" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db;">
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 600;">Tgl Selesai</label>
                    <input type="date" id="temp_selesai" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db;">
                </div>
                <button type="button" onclick="addItem()" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">+</button>
            </div>
        </div>

        {{-- Tabel Keranjang --}}
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 600;">Daftar Sewa (Keranjang):</label>
            <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #e5e7eb;">
                <thead>
                    <tr style="background: #f3f4f6; text-align: left;">
                        <th style="padding: 12px; border: 1px solid #e5e7eb;">Fasilitas</th>
                        <th style="padding: 12px; border: 1px solid #e5e7eb;">Periode & Durasi</th>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">Subtotal</th>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="cartItems"></tbody>
                <tfoot>
                    <tr id="totalRow" style="display: none; background: #f9fafb; font-weight: bold;">
                        <td colspan="2" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">Total Pembayaran:</td>
                        <td id="grandTotalDisplay" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #4f46e5; font-size: 1.1em;">Rp 0</td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb;"></td>
                    </tr>
                </tfoot>
            </table>
            <div id="emptyCartNote" style="text-align: center; padding: 20px; color: #9ca3af; border: 1px solid #e5e7eb; border-top: none;">
                Belum ada fasilitas yang ditambahkan.
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Keterangan Acara</label>
            <textarea name="keterangan" rows="3" placeholder="Tujuan penggunaan..." style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px;">{{ old('keterangan') }}</textarea>
        </div>

        <button type="submit" id="submitAll" disabled style="width: 100%; padding: 14px; background: #4f46e5; color: white; border: none; border-radius: 6px; cursor: not-allowed; font-size: 16px; font-weight: bold; opacity: 0.5; transition: 0.3s;">
            Kirim Pengajuan Sewa (<span id="countDisplay">0 item</span>)
        </button>
    </form>
</div>

<script>
    let items = [];

    function formatRupiah(number) {
        return "Rp " + new Intl.NumberFormat('id-ID').format(number);
    }

    function addItem() {
        const select = document.getElementById('temp_id');
        const id = select.value;
        const nama = select.options[select.selectedIndex]?.dataset.nama;
        const hargaPerHari = parseFloat(select.options[select.selectedIndex]?.dataset.harga) || 0;
        const mulai = document.getElementById('temp_mulai').value;
        const selesai = document.getElementById('temp_selesai').value;

        if (!id || !mulai || !selesai) {
            alert("Silakan lengkapi Fasilitas dan Tanggal Sewa.");
            return;
        }

        const dateMulai = new Date(mulai);
        const dateSelesai = new Date(selesai);

        if (dateMulai > dateSelesai) {
            alert("Tanggal mulai tidak boleh lebih besar dari tanggal selesai.");
            return;
        }

        const diffTime = Math.abs(dateSelesai - dateMulai);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
        const subtotal = diffDays * hargaPerHari;

        items.push({ id, nama, hargaPerHari, mulai, selesai, diffDays, subtotal });
        renderTable();

        select.value = "";
        document.getElementById('temp_mulai').value = "";
        document.getElementById('temp_selesai').value = "";
    }

    function removeItem(index) {
        items.splice(index, 1);
        renderTable();
    }

    function renderTable() {
        const tbody = document.getElementById('cartItems');
        const emptyNote = document.getElementById('emptyCartNote');
        const totalRow = document.getElementById('totalRow');
        const grandTotalDisplay = document.getElementById('grandTotalDisplay');
        const btn = document.getElementById('submitAll');
        const countDisplay = document.getElementById('countDisplay');

        tbody.innerHTML = "";
        let grandTotal = 0;

        items.forEach((item, index) => {
            grandTotal += item.subtotal;
            tbody.innerHTML += `
                <tr>
                    <td style="padding: 12px; border: 1px solid #e5e7eb;">
                        <strong>${item.nama}</strong><br>
                        <small style="color: #6b7280;">${formatRupiah(item.hargaPerHari)} / hari</small>
                        <input type="hidden" name="items[${index}][id_fasilitas]" value="${item.id}">
                    </td>
                    <td style="padding: 12px; border: 1px solid #e5e7eb;">
                        <span style="font-size: 0.9em;">${item.mulai} s/d ${item.selesai}</span><br>
                        <span style="background: #e0e7ff; color: #4338ca; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold;">${item.diffDays} Hari</span>
                        <input type="hidden" name="items[${index}][tgl_mulai]" value="${item.mulai}">
                        <input type="hidden" name="items[${index}][tgl_selesai]" value="${item.selesai}">
                    </td>
                    <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; font-weight: 600;">
                        ${formatRupiah(item.subtotal)}
                    </td>
                    <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                        <button type="button" onclick="removeItem(${index})" style="background: #fee2e2; color: #ef4444; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">Hapus</button>
                    </td>
                </tr>`;
        });

        if (items.length > 0) {
            emptyNote.style.display = "none";
            totalRow.style.display = "table-row";
            grandTotalDisplay.innerText = formatRupiah(grandTotal);
        } else {
            emptyNote.style.display = "block";
            totalRow.style.display = "none";
        }

        btn.disabled = items.length === 0;
        btn.style.opacity = items.length ? "1" : "0.5";
        btn.style.cursor = items.length ? "pointer" : "not-allowed";
        countDisplay.innerText = items.length + " item";
    }

    document.getElementById('mainForm').onsubmit = function() {
        const btn = document.getElementById('submitAll');
        btn.disabled = true;
        btn.innerHTML = "Sedang Mengirim...";
    };
</script>
@endsection