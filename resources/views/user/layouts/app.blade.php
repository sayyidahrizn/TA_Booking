<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
        }

        /* SIDEBAR */
        .sidebar {
            width: 220px;
            height: 100vh;
            background-color: #1e3a8a;
            position: fixed;
            color: white;
            overflow: hidden; /* 🔥 biar tidak melebar */
        }

        .sidebar h2 {
            text-align: center;
            padding: 20px 10px;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            margin: 0;
        }

        /* MENU */
        .sidebar a,
        .sidebar button {
            display: block;
            width: 100%;
            padding: 12px 20px;

            color: white;
            text-decoration: none;

            background: none;
            border: none;

            font-size: 16px; /* 🔥 SAMA SEMUA */
            font-family: inherit;

            text-align: left;
            cursor: pointer;

            box-sizing: border-box;
            transition: 0.2s;
        }

        .sidebar a:hover,
        .sidebar button:hover {
            background-color: #1e40af;
        }

        /* ACTIVE MENU */
        .sidebar .active {
            background-color: #1e40af;
            font-weight: bold;
        }

        /* CONTENT */
        .content {
            margin-left: 220px;
            padding: 25px;
        }

        /* CARD */
        .card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        table th {
            background-color: #1e3a8a;
            color: white;
        }

        /* BADGE */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .bg-pending { background-color: #f59e0b; }
        .bg-success { background-color: #10b981; }
        .bg-danger { background-color: #ef4444; }
    </style>
</head>
<body>

    {{-- Sidebar --}}
    @include('user.layouts.sidebar')

    {{-- Content --}}
    <div class="content">
        @yield('content')
    </div>

</body>
</html>