<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('public/images/Logo.png') }}" type="image/svg+xml">
    <title>{{ $title ?? 'Invora' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @livewireStyles

    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Poppins', sans-serif;
        }

        .navbar-custom {
            background: #404040;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            z-index: 1050;
            display: flex;
            align-items: center;
            padding: 0 20px;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #2f2f2f;
            position: fixed;
            top: 60px;
            left: 0;
            color: #fff;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1020;
        }

        .sidebar.collapsed {
            left: -250px;
        }

        .sidebar h6 {
            text-transform: uppercase;
            font-size: 12px;
            color: #a5a5a5;
            margin: 15px 20px 5px;
        }

        .sidebar a {
            color: #dcdcdc;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #3c3c3c;
            color: #fff;
        }

        .content {
            margin-left: 260px;
            padding: 80px 25px 25px;
            transition: margin-left 0.3s;
        }

        .collapsed+.content {
            margin-left: 0;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            color: #777;
            font-size: 13px;
        }

        /* Popup Notifikasi Stok */
        #notifPopup {
            position: fixed;
            top: 70px;
            right: 20px;
            z-index: 2000;
            width: 380px;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            overflow: hidden;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        #notifPopup.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notif-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 15px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notif-body {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
        }

        .notif-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            border-left: 4px solid #6c757d;
            transition: all 0.2s;
        }

        .notif-item:hover {
            background: #e9ecef;
            transform: translateX(2px);
        }

        .notif-item.danger {
            border-left-color: #dc3545;
            background: #fff5f5;
        }

        .notif-item.critical {
            border-left-color: #fd7e14;
            background: #fff8f3;
        }

        .notif-item.warning {
            border-left-color: #ffc107;
            background: #fffbf0;
        }

        .notif-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .notif-message {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 6px;
        }

        .notif-meta {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #adb5bd;
        }

        .notif-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-danger {
            background: #dc3545;
            color: white;
        }

        .badge-critical {
            background: #fd7e14;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #000;
        }

        .notif-footer {
            background: #f8f9fa;
            padding: 10px 15px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }

        .notif-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
        }

        .notif-footer a:hover {
            text-decoration: underline;
        }

        /* Badge animasi pulse untuk critical */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .badge-pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        /* Scrollbar */
        .notif-body::-webkit-scrollbar {
            width: 6px;
        }

        .notif-body::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 3px;
        }

        .notif-body::-webkit-scrollbar-thumb:hover {
            background-color: #999;
        }

        @media (max-width: 991px) {
            .sidebar {
                left: -250px;
            }

            .sidebar.show {
                left: 0;
            }

            .content {
                margin-left: 0;
                padding-top: 80px;
            }

            #notifPopup {
                width: 320px;
                right: 10px;
            }
        }
    </style>
</head>

<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-dark navbar-custom shadow-sm">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-light d-lg-none" id="toggleSidebar"><i class="bi bi-list"></i></button>
                <a class="navbar-brand fw-bold" href="{{ route('dashboard.index') }}">Invora</a>
            </div>

            <ul class="navbar-nav flex-row align-items-center gap-3 ms-auto">
                <li class="nav-item d-flex align-items-center text-white fw-semibold border-end pe-3">
                    <i class="bi bi-person-circle me-1"></i>
                    {{ auth()->user()->name ?? 'Kasir' }}
                </li>

                <!-- Tombol Notifikasi Stok -->
                <li class="nav-item">
                    <button class="btn btn-outline-light position-relative" id="notifBtn" title="Notifikasi Stok">
                        <i class="bi bi-bell-fill"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            id="notifCount" style="display:none;">0</span>
                    </button>
                </li>

                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-light btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Popup Notifikasi Stok -->
    <div id="notifPopup">
        <div class="notif-header">
            <span><i class="bi bi-bell"></i> Notifikasi Stok</span>
            <button class="btn-close btn-close-white btn-sm" id="closeNotif"></button>
        </div>
        <div class="notif-body" id="notifList">
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted small mt-2 mb-0">Memuat notifikasi...</p>
            </div>
        </div>
        <div class="notif-footer">
            <a href="{{ route('notifications.index') }}">Lihat Semua Notifikasi <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="sidebar" id="sidebarMenu">
        <h6>Dashboard INVORA</h6>
        <a href="{{ route('dashboard.index') }}" class="{{ request()->is('dashboard*') ? 'active' : '' }}">
            <i class="bi bi-house"></i> Dashboard
        </a>
        <h6>Manajemen Produk</h6>
        <a href="{{ route('product.index') }}" class="{{ request()->is('product*') ? 'active' : '' }}">
            <i class="bi bi-box"></i> Produk
        </a>
        @if (Auth::user()->role === 'owner')
            <a href="{{ route('kategori.index') }}" class="{{ request()->is('kategori*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Kategori
            </a>
        @endif
        <h6>Transaksi</h6>
        <a href="{{ route('pos.index') }}" class="{{ request()->is('pos*') ? 'active' : '' }}">
            <i class="bi bi-cart-check"></i> Kasir
        </a>
        <a href="{{ route('history_transaksi.index') }}" class="{{ request()->is('transaksi*') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> History Transaksi
        </a>
        <a href="{{ route('barang_masuk.index') }}" class="{{ request()->is('barang_masuk*') ? 'active' : '' }}">
            <i class="bi bi-box-arrow-in-down"></i> Barang Masuk
        </a>

        @if (Auth::user()->role === 'owner')
            <h6>Manajemen Pengguna</h6>
            <a href="{{ route('account.index') }}" class="{{ request()->is('auth/account*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Akun Pengguna
            </a>
            <a href="{{ route('karyawan.index') }}" class="{{ request()->is('karyawan*') ? 'active' : '' }}">
                <i class="bi bi-person-workspace"></i> Karyawan
            </a>
        @endif

        <h6>Operasional</h6>
        @if (Auth::user()->role === 'owner')
            <a href="{{ route('kalender.index') }}" class="{{ request()->is('kalender*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i> Kalender Event
            </a>
        @endif
        <a href="{{ route('retur.index') }}" class="{{ request()->is('retur*') ? 'active' : '' }}">
            <i class="bi bi-arrow-counterclockwise"></i> Retur Barang
        </a>
        <a href="{{ route('barangrusak.index') }}" class="{{ request()->is('barang-rusak*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle"></i> Barang Rusak
        </a>
        <a href="{{ route('sales.index') }}" class="{{ request()->is('sales*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Kunjungan Sales
        </a>
        <h6>Laporan</h6>
        <a href="{{ route('notifications.index') }}" class="{{ request()->is('notifications*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> Notifikasi Stok
        </a>
        @if (Auth::user()->role === 'owner')
            <a href="{{ route('laporan_penjualan.penjualan') }}"
                class="{{ request()->is('laporan-penjualan*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data"></i> Laporan Penjualan
            </a>
        @endif
        <a href="{{ route('purchase-order.index') }}" class="{{ request()->is('purchase-order*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Purchase Order
        </a>
        @if (Auth::user()->role === 'owner')
            <a href="{{ route('laporan_stok.index') }}"
                class="{{ request()->is('laporan-stok*') ? 'active' : '' }}">
                <i class="bi bi-archive"></i> Laporan Stok
            </a>
        @endif
    </div>

    <div class="content">
        {!! $slot ?? '' !!}
        @yield('content')
        <footer>
            <hr>
            <p>&copy; {{ date('Y') }} Invora System — Toko Karomah, Mulyorejo</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebarMenu');
        const toggleBtn = document.getElementById('toggleSidebar');
        if (toggleBtn) toggleBtn.addEventListener('click', () => sidebar.classList.toggle('show'));

        // Notification Popup Toggle
        const notifBtn = document.getElementById('notifBtn');
        const notifPopup = document.getElementById('notifPopup');
        const closeNotif = document.getElementById('closeNotif');

        notifBtn.addEventListener('click', () => {
            notifPopup.classList.toggle('active');
            if (notifPopup.classList.contains('active')) {
                loadStockNotifications();
            }
        });

        closeNotif.addEventListener('click', () => notifPopup.classList.remove('active'));

        document.addEventListener('click', (e) => {
            if (!notifPopup.contains(e.target) && !notifBtn.contains(e.target)) {
                notifPopup.classList.remove('active');
            }
        });

        // Load Stock Notifications
        function loadStockNotifications() {
            fetch("{{ route('notifications.recent') }}?limit=5")
                .then(res => res.json())
                .then(data => {
                    let notifList = document.getElementById('notifList');
                    let notifCount = document.getElementById('notifCount');

                    // Update badge count
                    if (data.total_unread > 0) {
                        notifCount.innerText = data.total_unread > 99 ? '99+' : data.total_unread;
                        notifCount.style.display = 'inline-block';

                        // Add pulse animation for critical
                        if (data.notifications.some(n => n.severity === 'danger' || n.severity === 'critical')) {
                            notifCount.classList.add('badge-pulse');
                        }
                    } else {
                        notifCount.style.display = 'none';
                    }

                    // Build notification items
                    let html = '';
                    if (data.notifications.length > 0) {
                        data.notifications.forEach(notif => {
                            const severityClass = notif.severity;
                            const icon = notif.icon;

                            html += `
                                <div class="notif-item ${severityClass}">
                                    <div class="notif-title">
                                        <span>${icon}</span>
                                        <span>${notif.product_name}</span>
                                        <span class="notif-badge badge-${severityClass} ms-auto">${notif.severity}</span>
                                    </div>
                                    <div class="notif-message">${notif.message}</div>
                                    <div class="notif-meta">
                                        <span><i class="bi bi-box"></i> Stok: ${notif.current_stock}</span>
                                        <span>${notif.time_ago}</span>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = `
                            <div class="text-center py-5">
                                <i class="bi bi-check-circle text-success" style="font-size: 48px;"></i>
                                <p class="text-muted mt-3 mb-0">Tidak ada notifikasi</p>
                                <small class="text-muted">Semua stok dalam kondisi baik</small>
                            </div>
                        `;
                    }

                    notifList.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    document.getElementById('notifList').innerHTML = `
                        <div class="text-center py-4">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 32px;"></i>
                            <p class="text-muted small mt-2 mb-0">Gagal memuat notifikasi</p>
                        </div>
                    `;
                });
        }

        // Auto-refresh notification count every 30 seconds
        setInterval(() => {
            fetch("{{ route('notifications.unread-count') }}")
                .then(res => res.json())
                .then(data => {
                    let notifCount = document.getElementById('notifCount');
                    if (data.unread_count > 0) {
                        notifCount.innerText = data.unread_count > 99 ? '99+' : data.unread_count;
                        notifCount.style.display = 'inline-block';

                        if (data.critical_count > 0) {
                            notifCount.classList.add('badge-pulse');
                        }
                    } else {
                        notifCount.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error fetching notification count:', error));
        }, 30000); // 30 seconds

        // Load initial notification count on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch("{{ route('notifications.unread-count') }}")
                .then(res => res.json())
                .then(data => {
                    let notifCount = document.getElementById('notifCount');
                    if (data.unread_count > 0) {
                        notifCount.innerText = data.unread_count > 99 ? '99+' : data.unread_count;
                        notifCount.style.display = 'inline-block';

                        if (data.critical_count > 0) {
                            notifCount.classList.add('badge-pulse');
                        }
                    }
                });
        });
    </script>

    @livewireScripts
</body>

</html>
