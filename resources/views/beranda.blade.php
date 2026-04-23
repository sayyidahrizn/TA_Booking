<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Beranda - Sistem Penyewaan Fasilitas Desa Kesamben</title>

    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

    <style>
        /* SMOOTH SCROLL */
        html { scroll-behavior: smooth; }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background-color: #f8fafc; color: #334155; line-height: 1.4; }
        a { text-decoration: none; color: inherit; }

        /* NAVBAR - KEMBALI KE DESAIN AWAL YANG RAPI */
        .navbar { 
            width: 100%; 
            background: #ffffff; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
        }
        
        .nav-container { 
            max-width: 1100px; 
            margin: auto; 
            padding: 12px 15px; 
            display: flex; 
            align-items: center;
            justify-content: space-between;
        }

        .logo-wrapper { 
            display: flex; 
            align-items: center; 
            gap: 10px;
        }
        .logo-wrapper img { height: 35px; width: auto; }
        .logo-text { font-size: 16px; font-weight: bold; color: #1e3a8a; }

        .nav-menu { 
            display: flex; 
            gap: 25px; 
            list-style: none; 
            font-size: 13px; 
        }
        .nav-menu a {
            font-weight: 600;
            color: #1e3a8a;
            transition: 0.3s;
        }
        .nav-menu a:hover { color: #f97316; }

        .login-btn { 
            padding: 6px 15px; 
            background: #f97316; 
            color: #fff !important; 
            border-radius: 4px; 
            font-weight: bold; 
            font-size: 12px;
        }

        /* HERO SECTION */
        .hero { 
            padding: 80px 15px; 
            background: #1e3a8a; 
            color: white; 
        }
        .hero-container {
            max-width: 1100px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
        }
        .hero-text { flex: 1; text-align: left; }
        .hero-text h1 { font-size: 36px; margin-bottom: 20px; line-height: 1.1; font-weight: 800; }
        .hero-text p { font-size: 15px; margin-bottom: 30px; opacity: 0.9; }
        
        .hero-btns { display: flex; gap: 12px; }
        .btn-primary { background: #f97316; color: white; padding: 12px 24px; border-radius: 6px; font-weight: bold; font-size: 14px; }
        .btn-outline { border: 2px solid white; color: white; padding: 12px 24px; border-radius: 6px; font-weight: bold; font-size: 14px; }

        .hero-image { flex: 1; display: flex; justify-content: flex-end; }
        .photo-single { 
            width: 100%; 
            max-width: 480px; 
            height: 300px; 
            object-fit: cover; 
            border-radius: 20px; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
            border: 4px solid rgba(255,255,255,0.1);
        }

        /* FASILITAS */
        .content-section { padding: 40px 15px; max-width: 1000px; margin: auto; text-align: center; }
        .section-title h2 { font-size: 18px; color: #1e3a8a; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 1px; }

        .fasilitas-container { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); 
            gap: 12px; 
        }
        .card { 
            background: white; 
            padding: 6px; 
            border-radius: 6px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
            border: 1px solid #e2e8f0; 
            text-align: left;
            transition: 0.3s;
        }
        .card img { width: 100%; height: 85px; object-fit: cover; border-radius: 4px; margin-bottom: 5px; }
        .card h3 { font-size: 11px; color: #1e3a8a; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card .price { font-size: 11px; font-weight: bold; color: #f97316; }

        /* ============================================================ */
        /* FIX KALENDER: NGEBLOCK TOTAL, BERSIH, & MULTIPLE TEXT        */
        /* ============================================================ */
        .calendar-wrapper { 
            background: white; 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
            max-width: 850px;
            margin: auto;
        }

        .fc .fc-daygrid-day-frame {
            min-height: 100px !important;
            position: relative;
        }

        /* Warna Block Penuh Oranye */
        .fc-daygrid-bg-harness {
            background-color: #915dda !important;
            z-index: 1;
        }

        /* SEMBUNYIKAN SEMUA TEKS BAWAAN (BAYANGAN DI POJOK) */
        .fc-daygrid-event-harness, .fc-daygrid-event, .fc-event, .fc-event-main {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

        /* NOMOR TANGGAL TETAP DI ATAS */
        .fc .fc-daygrid-day-top {
            position: relative;
            z-index: 10; 
            justify-content: flex-end;
        }
        .fc .fc-daygrid-day-number {
            font-weight: bold;
            padding: 8px !important;
            color: #334155;
            text-decoration: none !important;
        }
        .is-booked .fc-daygrid-day-number {
            color: #ffffff !important;
        }

        /* KONTAINER TEKS CUSTOM DI TENGAH */
        .custom-center-text-container {
            position: absolute;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 95%;
            z-index: 5;
            display: flex;
            flex-direction: column;
            gap: 2px;
            pointer-events: none;
        }

        .item-text {
            color: #ffffff !important;
            font-size: 10px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            line-height: 1.1;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        .footer { background: #1e3a8a; color: white; padding: 15px; text-align: center; font-size: 11px; margin-top: 30px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <div class="logo-wrapper">
                <img src="{{ asset('images/LOGODESA.png') }}" alt="Logo Desa">
                <span class="logo-text">Desa Kesamben</span>
            </div>
            
            <ul class="nav-menu">
                <li><a href="#">Beranda</a></li>
                <li><a href="#fasilitas">Fasilitas</a></li>
                <li><a href="#jadwal">Jadwal</a></li>
            </ul>

            <div class="login-wrapper">
                <a href="{{ route('login') }}" class="login-btn">Masuk</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-container">
            <div class="hero-text">
                <h1>Sistem Penyewaan Fasilitas Desa Kesamben</h1>
                <p>Nikmati kemudahan dalam memantau jadwal dan melakukan penyewaan fasilitas desa secara online dan transparan.</p>
                <div class="hero-btns">
                    <a href="{{ route('login') }}" class="btn-primary">Sewa Sekarang</a>
                    <a href="#fasilitas" class="btn-outline">Lihat Fasilitas</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="{{ asset('images/ksb.jpeg') }}" class="photo-single" alt="Gedung Desa">
            </div>
        </div>
    </section>

    <section class="content-section" id="fasilitas">
        <div class="section-title"><h2>Fasilitas</h2></div>
        <div class="fasilitas-container">
            @foreach($fasilitas as $item)
            <div class="card">
                @if($item->gambar && $item->gambar->count() > 0 && $item->gambar->first())
                    <img src="{{ asset('storage/' . $item->gambar->first()->file_gambar) }}" alt="{{ $item->nama_fasilitas }}">
                @else
                    <img src="https://via.placeholder.com/150x85?text=Fasilitas" alt="No Image">
                @endif
                <h3>{{ $item->nama_fasilitas }}</h3>
                <div class="price">Rp {{ number_format($item->harga_sewa, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </section>

    <section class="content-section" id="jadwal" style="background-color: #f1f5f9;">
        <div class="section-title"><h2>Jadwal Kegiatan</h2></div>
        <div class="calendar-wrapper">
            <div id='calendar'></div>
        </div>
    </section>

    <footer class="footer">
        <p>&copy; {{ date('Y') }} Pemerintah Desa Kesamben.</p>
    </footer>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                height: 'auto',
                headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                events: [
                    @foreach($jadwal as $j)
                    {
                        title: '{{ $j->nama_kegiatan ?? ($j->fasilitas->nama_fasilitas ?? "DIPESAN") }}',
                        start: '{{ $j->tgl_mulai }}',
                        end: '{{ \Carbon\Carbon::parse($j->tgl_selesai)->addDay()->format("Y-m-d") }}',
                        display: 'background'
                    },
                    @endforeach
                ],
                eventDidMount: function(info) {
                    if (info.event.display === 'background') {
                        let cell = info.el.closest('.fc-daygrid-day');
                        if (cell) {
                            cell.classList.add('is-booked');
                            let frame = cell.querySelector('.fc-daygrid-day-frame');
                            let container = frame.querySelector('.custom-center-text-container');
                            if (!container) {
                                container = document.createElement('div');
                                container.className = 'custom-center-text-container';
                                frame.appendChild(container);
                            }
                            let existingItems = Array.from(container.querySelectorAll('.item-text')).map(el => el.innerText);
                            if (!existingItems.includes(info.event.title)) {
                                let textDiv = document.createElement('div');
                                textDiv.className = 'item-text';
                                textDiv.innerText = info.event.title;
                                container.appendChild(textDiv);
                            }
                        }
                    }
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>