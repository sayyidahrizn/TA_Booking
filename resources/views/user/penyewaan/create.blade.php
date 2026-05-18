@extends('user.layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .booking-grid { display: grid; grid-template-columns: 1.5fr 0.5fr 1.5fr 1.5fr auto; gap: 10px; align-items: end; }
    .identity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; border-bottom: 2px solid #f3f4f6; padding-bottom: 20px; }
    table thead th { background-color: #1e3a8a; color: white; padding: 15px; text-align: center; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .loader { border: 2px solid #f3f3f3; border-top: 2px solid #ffffff; border-radius: 50%; width: 14px; height: 14px; animation: spin 1s linear infinite; display: inline-block; margin-right: 8px; vertical-align: middle; }
    @media (max-width: 768px) { .booking-grid, .identity-grid { grid-template-columns: 1fr !important; } .container { margin: 20px auto !important; padding: 15px !important; } table thead { display: none; } table, table tbody, table tr, table td { display: block; width: 100%; } table tr { margin-bottom: 15px; border: 1px solid #e5e7eb; padding: 10px; border-radius: 8px; } table td { text-align: right !important; padding-left: 50% !important; position: relative; border: none !important; } table td::before { content: attr(data-label); position: absolute; left: 10px; width: 45%; font-weight: bold; text-align: left; } }
</style>

<div class="container" style="max-width: 1000px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
    @if($errors->any() || session('error'))
        <div style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; font-size: 14px;">
            <strong>Terjadi Kesalahan:</strong>
            <ul style="margin: 5px 0 0; padding-left: 20px;">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                @if(session('error')) <li>{{ session('error') }}</li> @endif
            </ul>
        </div>
    @endif

    <div style="margin-bottom: 20px;">
        <a href="{{ route('user.penyewaan.index') }}" style="text-decoration: none; color: #4f46e5; font-weight: bold;">&larr; Kembali ke Daftar</a>
    </div>

    <h2 style="text-align: center; margin-bottom: 25px; color: #1f2937;">Form Booking Fasilitas</h2>

    <form action="{{ route('user.penyewaan.store') }}" method="POST" id="mainForm">
        @csrf
        <div class="identity-grid">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nama Lengkap Penyewa</label>
                <input type="text" name="nama_penyewa" value="{{ auth()->user()->name }}" readonly style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; background-color: #f3f4f6; color: #6b7280;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">NIK</label>
                <input type="text" name="nik" value="{{ auth()->user()->nik }}" readonly style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; background-color: #f3f4f6; color: #6b7280;">
            </div>
        </div>

        <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 25px; border: 1px dashed #4f46e5;">
            <h4 style="margin: 0 0 15px; color: #4f46e5;">Pilih Fasilitas, Jumlah & Periode Waktu</h4>
            <div class="booking-grid">
                <div>
                    <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 5px;">Fasilitas</label>
                    <select id="temp_id" onchange="updateStockLabel()" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; height: 42px;">
                        <option value="">-- Pilih --</option>
                        @foreach($fasilitas as $f)
                            <option value="{{ $f->id_fasilitas }}" data-nama="{{ $f->nama_fasilitas }}" data-harga="{{ $f->harga_sewa }}" data-stok="{{ $f->jumlah }}">
                                {{ $f->nama_fasilitas }} (Rp {{ number_format($f->harga_sewa, 0, ',', '.') }} | Sedia: {{ $f->jumlah }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 5px;">Unit</label>
                    <input type="number" id="temp_qty" min="1" value="1" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; height: 42px;">
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 5px;">Mulai</label>
                    <div style="display: flex; gap: 5px;">
                        <input type="text" id="temp_mulai" placeholder="Pilih Tanggal" readonly style="width: 65%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; height: 42px; background: white;">
                        <input type="text" id="temp_jam_mulai" placeholder="00:00" style="width: 35%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; height: 42px; background: white;">
                    </div>
                </div>
                <div>
                    <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 5px;">Selesai</label>
                    <div style="display: flex; gap: 5px;">
                        <input type="text" id="temp_selesai" placeholder="Pilih Tanggal" readonly style="width: 65%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; height: 42px; background: white;">
                        <input type="text" id="temp_jam_selesai" placeholder="00:00" style="width: 35%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; height: 42px; background: white;">
                    </div>
                </div>
                <button type="button" onclick="addItem()" style="height: 42px; padding: 0 20px; background: #4f46e5; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 20px;">+</button>
            </div>
            <div id="stok_info" style="margin-top: 10px; font-size: 12px; color: #4338ca; font-weight: bold;"></div>
        </div>

        <div style="margin-bottom: 25px;">
            <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #e5e7eb;">
                <thead>
                    <tr><th style="border: 1px solid #e5e7eb;">Fasilitas & Qty</th><th style="border: 1px solid #e5e7eb;">Periode Sewa</th><th style="border: 1px solid #e5e7eb;">Subtotal</th><th style="border: 1px solid #e5e7eb;">Aksi</th></tr>
                </thead>
                <tbody id="cartItems"></tbody>
                <tfoot>
                    <tr id="totalRow" style="display: none; background: #f9fafb; font-weight: bold;">
                        <td colspan="2" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right;">Total Pembayaran:</td>
                        <td id="grandTotalDisplay" style="padding: 12px; border: 1px solid #e5e7eb; text-align: right; color: #4338ca; font-size: 1.1em;">Rp 0</td>
                        <td style="border: 1px solid #e5e7eb;"></td>
                    </tr>
                </tfoot>
            </table>
            <div id="emptyCartNote" style="text-align: center; padding: 20px; color: #9ca3af; border: 1px solid #e5e7eb; border-top: none;">Belum ada fasilitas yang ditambahkan.</div>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Keterangan Acara</label>
            <textarea name="keterangan" rows="3" placeholder="Tujuan penggunaan..." style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px;">{{ old('keterangan') }}</textarea>
        </div>
        {{-- Tambahkan di atas tombol Kirim Pengajuan Sewa --}}
        <div style="margin-bottom: 25px; padding: 20px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
            <label style="display: block; margin-bottom: 12px; font-weight: 700; color: #166534;">Metode Pembayaran</label>
            <div style="display: flex; gap: 20px;">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="radio" name="metode_pembayaran" value="midtrans" checked style="width: 18px; height: 18px;">
                    <span style="font-weight: 600;">Transfer Otomatis (Midtrans)</span>
                </label>
                <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="radio" name="metode_pembayaran" value="tunai" style="width: 18px; height: 18px;">
                    <span style="font-weight: 600;">Tunai (Bayar di Kantor)</span>
                </label>
            </div>
            <p id="info_pembayaran" style="margin-top: 10px; font-size: 13px; color: #166534;">
                * Bayar praktis dari rumah menggunakan VA, E-Wallet, atau Kartu Kredit.
            </p>
        </div>

        <button type="submit" id="submitAll" disabled style="width: 100%; padding: 14px; background: #4f46e5; color: white; border: none; border-radius: 6px; cursor: not-allowed; font-size: 16px; font-weight: bold; opacity: 0.5;">
            Kirim Pengajuan Sewa (<span id="countDisplay">0 item</span>)
        </button>
    </form>
</div>

<script>
    let items = [];

    // data booking dari Laravel
    const disabledDatesRaw = @json($existingBookings);

    let startPicker, endPicker;

    // =========================
    // FORMAT RUPIAH
    // =========================
    function formatRupiah(number) {
        return "Rp " + new Intl.NumberFormat('id-ID').format(number);
    }

    // =========================
    // INIT DATE PICKER
    // =========================
    function initPickers(disabledDates = []) {

        // destroy picker lama
        if (startPicker) startPicker.destroy();
        if (endPicker) endPicker.destroy();

        // picker mulai
        startPicker = flatpickr("#temp_mulai", {

            minDate: "today",

            dateFormat: "Y-m-d",

            disable: disabledDates,

            disableMobile: true,

            onChange: function(selectedDates, dateStr) {

                // tanggal selesai minimal tanggal mulai
                endPicker.set('minDate', dateStr);

                // apply disabled dates ke picker selesai
                endPicker.set('disable', disabledDates);
            }
        });

        // picker selesai
        endPicker = flatpickr("#temp_selesai", {

            minDate: "today",

            dateFormat: "Y-m-d",

            disable: disabledDates,

            disableMobile: true
        });
    }

    // =========================
    // DOM READY
    // =========================
    document.addEventListener("DOMContentLoaded", function () {

        // time picker
        const timeConfig = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            disableMobile: true
        };

        flatpickr("#temp_jam_mulai", timeConfig);
        flatpickr("#temp_jam_selesai", timeConfig);

        // init kosong
        initPickers([]);

        console.log(disabledDatesRaw);

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                iconColor: '#4f46e5'
            });
        @endif
    });

    // =========================
    // UPDATE STOCK & DISABLED DATE
    // =========================
    function updateStockLabel() {

        const select =
            document.getElementById('temp_id');

        const selected =
            select.options[select.selectedIndex];

        // reset tanggal
        document.getElementById('temp_mulai').value = "";
        document.getElementById('temp_selesai').value = "";

        // reset jika belum pilih
        if (!selected.value) {

            document.getElementById(
                'stok_info'
            ).innerText = "";

            initPickers([]);

            return;
        }

        const idFasilitas = selected.value;

        const stok =
            selected.getAttribute('data-stok');

        const harga =
            selected.getAttribute('data-harga');

        // filter booking sesuai fasilitas
        const disabledRanges =
            disabledDatesRaw
                .filter(item =>
                    item.id_fasilitas == idFasilitas
                )
                .map(item => ({
                    from: item.from,
                    to: item.to
                }));

        console.log(disabledRanges);

        // kirim ke flatpickr
        initPickers(disabledRanges);

        // info stok
        document.getElementById(
            'stok_info'
        ).innerText =
            `Harga: ${formatRupiah(harga)} / hari | Stok: ${stok} unit`;
    }

    // =========================
    // TAMBAH ITEM
    // =========================
    function addItem() {

        const select =
            document.getElementById('temp_id');

        const id = select.value;

        const selected =
            select.options[select.selectedIndex];

        if (!id) {

            return Swal.fire({
                icon: 'warning',
                title: 'Pilih Fasilitas',
                text: 'Silakan pilih fasilitas terlebih dahulu.'
            });
        }

        const nama =
            selected.getAttribute('data-nama');

        const harga =
            parseFloat(
                selected.getAttribute('data-harga')
            );

        const stok =
            parseInt(
                selected.getAttribute('data-stok')
            );

        const qty =
            parseInt(
                document.getElementById('temp_qty').value
            );

        const tglMulai =
            document.getElementById('temp_mulai').value;

        const jamMulai =
            document.getElementById('temp_jam_mulai').value;

        const tglSelesai =
            document.getElementById('temp_selesai').value;

        const jamSelesai =
            document.getElementById('temp_jam_selesai').value;

        // validasi
        if (!tglMulai || !jamMulai || !tglSelesai || !jamSelesai) {

            return Swal.fire({
                icon: 'info',
                title: 'Waktu Belum Lengkap',
                text: 'Mohon isi tanggal dan jam sewa.'
            });
        }

        if (qty > stok) {

            return Swal.fire({
                icon: 'error',
                title: 'Stok Terbatas',
                text: 'Jumlah unit melebihi stok yang tersedia.'
            });
        }

        // cek waktu
        const start =
            new Date(`${tglMulai}T${jamMulai}`);

        const end =
            new Date(`${tglSelesai}T${jamSelesai}`);

        if (start >= end) {

            return Swal.fire({
                icon: 'error',
                title: 'Kesalahan Waktu',
                text: 'Waktu mulai harus sebelum waktu selesai.'
            });
        }

        // cek overlap booking
        const isOverlap =
            disabledDatesRaw.some(b => {

                if (b.id_fasilitas == id) {

                    const bookedStart =
                        new Date(b.from);

                    const bookedEnd =
                        new Date(b.to);

                    const selectedStart =
                        new Date(tglMulai);

                    const selectedEnd =
                        new Date(tglSelesai);

                    return (
                        selectedStart <= bookedEnd &&
                        selectedEnd >= bookedStart
                    );
                }

                return false;
            });

        // jika bentrok
        if (isOverlap) {

            return Swal.fire({
                icon: 'error',
                title: 'Tanggal Sudah Dibooking',
                text: 'Silakan pilih tanggal lain.'
            });
        }

        // hitung hari
        const diffTime =
            Math.abs(end - start);

        const diffDays =
            Math.ceil(
                diffTime / (1000 * 60 * 60 * 24)
            );

        // simpan item
        items.push({

            id,
            nama,
            qty,
            harga,

            mulai:
                `${tglMulai} ${jamMulai}`,

            selesai:
                `${tglSelesai} ${jamSelesai}`,

            diffDays,

            subtotal:
                diffDays * harga * qty
        });

        // render
        renderTable();

        // reset form
        select.value = "";

        document.getElementById(
            'temp_qty'
        ).value = 1;

        document.getElementById(
            'temp_mulai'
        ).value = "";

        document.getElementById(
            'temp_selesai'
        ).value = "";

        document.getElementById(
            'temp_jam_mulai'
        ).value = "";

        document.getElementById(
            'temp_jam_selesai'
        ).value = "";

        document.getElementById(
            'stok_info'
        ).innerText = "";

        initPickers([]);
    }

    // =========================
    // RENDER TABLE
    // =========================
    function renderTable() {

        const tbody =
            document.getElementById('cartItems');

        tbody.innerHTML = "";

        let total = 0;

        items.forEach((item, index) => {

            total += item.subtotal;

            tbody.innerHTML += `

            <tr>

                <td
                    data-label="Fasilitas & Qty"
                    style="padding:12px;border:1px solid #e5e7eb;text-align:center;"
                >

                    <strong style="font-size:1.1em;">
                        ${item.nama}
                    </strong>

                    <br>

                    <span>
                        ${item.qty} Unit
                    </span>

                    <br>

                    <small style="color:#6b7280;">
                        (${formatRupiah(item.harga)} / unit / hari)
                    </small>

                    <input
                        type="hidden"
                        name="items[${index}][id_fasilitas]"
                        value="${item.id}"
                    >

                    <input
                        type="hidden"
                        name="items[${index}][jumlah_sewa]"
                        value="${item.qty}"
                    >

                </td>

                <td
                    data-label="Periode"
                    style="padding:12px;border:1px solid #e5e7eb;text-align:center;font-size:0.9em;"
                >

                    <div style="margin-bottom:5px;">
                        <span style="color:#6b7280;font-weight:bold;">
                            Dari:
                        </span>

                        ${item.mulai}
                    </div>

                    <div style="margin-bottom:5px;">
                        <span style="color:#6b7280;font-weight:bold;">
                            Sampai:
                        </span>

                        ${item.selesai}
                    </div>

                    <span
                        style="background:#e0e7ff;color:#4338ca;padding:2px 8px;border-radius:4px;font-size:0.85em;font-weight:bold;display:inline-block;"
                    >
                        ${item.diffDays} Hari
                    </span>

                    <input
                        type="hidden"
                        name="items[${index}][tgl_mulai]"
                        value="${item.mulai}"
                    >

                    <input
                        type="hidden"
                        name="items[${index}][tgl_selesai]"
                        value="${item.selesai}"
                    >

                </td>

                <td
                    data-label="Subtotal"
                    style="padding:12px;border:1px solid #e5e7eb;text-align:right;font-weight:bold;"
                >
                    ${formatRupiah(item.subtotal)}
                </td>

                <td
                    data-label="Aksi"
                    style="padding:12px;border:1px solid #e5e7eb;text-align:center;"
                >

                    <button
                        type="button"
                        onclick="items.splice(${index},1);renderTable();"
                        style="color:#ef4444;border:1px solid #fecaca;background:#fef2f2;padding:5px 12px;border-radius:4px;cursor:pointer;"
                    >
                        Hapus
                    </button>

                </td>

            </tr>
            `;
        });

        // total
        document.getElementById(
            'totalRow'
        ).style.display =
            items.length
                ? "table-row"
                : "none";

        document.getElementById(
            'emptyCartNote'
        ).style.display =
            items.length
                ? "none"
                : "block";

        document.getElementById(
            'grandTotalDisplay'
        ).innerText =
            formatRupiah(total);

        const btn =
            document.getElementById('submitAll');

        btn.disabled = !items.length;

        btn.style.opacity =
            items.length ? "1" : "0.5";

        btn.style.cursor =
            items.length
                ? "pointer"
                : "not-allowed";

        document.getElementById(
            'countDisplay'
        ).innerText =
            items.length + " item";
    }

    // =========================
    // SUBMIT
    // =========================
    document.getElementById('mainForm').onsubmit = function(e) {

        if (items.length === 0) {

            e.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Tambahkan minimal satu fasilitas.'
            });

            return false;
        }

        const btn =
            document.getElementById('submitAll');

        btn.disabled = true;

        btn.innerHTML =
            `<span class="loader"></span> Sedang Mengirim...`;
    };
</script>
<script>
            // Script tambahan untuk ganti info teks saat radio button diklik
            document.querySelectorAll('input[name="metode_pembayaran"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const info = document.getElementById('info_pembayaran');
                    if(this.value === 'tunai') {
                        info.innerHTML = "<strong>INFO:</strong> Silakan segera datang ke kantor desa dengan membawa identitas diri untuk melakukan pembayaran setelah pengajuan disetujui admin.";
                    } else {
                        info.innerHTML = "* Bayar praktis dari rumah menggunakan VA, E-Wallet, atau Kartu Kredit.";
                    }
                });
            });
        </script>
@endsection