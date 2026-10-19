<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Warehouse System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Modern Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 text-gray-900">

    <div id="overlay" class="fixed inset-0 bg-gray-900/50 z-20 hidden lg:hidden"></div>

    @include('components.sidebar')

    <main id="main-content" class="lg:ml-64 min-h-screen transition-all duration-300">
       <header class="sticky top-0 bg-white/80 backdrop-blur-md border-b border-gray-200 px-6 py-4 flex justify-between items-center z-10">
            <div class="flex items-center gap-4">
                <button id="menu-btn" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                </button>
            </div>

            <div class="flex item-center gap-4">
                @if(auth()->user()->role == 'gudang')
                <div class="relative">
                    <button id="msg-menu-btn" class="p-2 text-gray-500 hover:bg-gray-100 rounded-xl transition relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span
                            id="msg-badge"
                            class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                            0
                        </span>
                    </button>

                    <!-- Dropdown Pesan -->
                    <div id="msg-dropdown" class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-gray-100 py-2 hidden animate-in fade-in zoom-in-95 duration-200">
                        <div class="px-4 py-2 border-b border-gray-50 text-sm font-semibold text-gray-700">Notifikasi Gudang</div>
                        
                        <!-- Area ini yang akan kita isi via JS -->
                        <div id="msg-list" class="max-h-60 overflow-y-auto">
                            <div class="p-4 text-sm text-gray-400 text-center">Memuat...</div>
                        </div>

                        <div class="px-4 py-2 border-t border-gray-50 text-center">
                            <a href="{{ route('gudang.requests') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua Permintaan</a>
                        </div>
                    </div>
                </div>

                <div class="relative">

                    <button id="spk-menu-btn"
                        class="p-2 text-gray-500 hover:bg-gray-100 rounded-xl transition relative">

                        <svg class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>

                        <span
                            id="spk-badge"
                            class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-blue-600 text-white text-[10px] font-bold flex items-center justify-center">
                            0
                        </span>

                    </button>

                    <div id="spk-dropdown"
                        class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-gray-100 py-2 hidden">

                        <div class="px-4 py-2 border-b text-sm font-semibold">
                            SPK Baru
                        </div>

                        <div id="spk-list" class="max-h-60 overflow-y-auto">

                            <div class="p-4 text-center text-gray-400">
                                Memuat...
                            </div>

                        </div>

                        <div class="border-t px-4 py-2 text-center">
                            <a href="{{ route('gudang.spk') }}"
                                class="text-xs text-blue-600 hover:underline">
                                Lihat Semua SPK
                            </a>
                        </div>

                    </div>

                </div>
                @elseif(auth()->user()->role == 'production')
                <div class="relative">

                    <button id="spk-menu-btn"
                        class="p-2 text-gray-500 hover:bg-gray-100 rounded-xl transition relative">

                        <svg class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>

                        <span
                            id="spk-badge"
                            class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-blue-600 text-white text-[10px] font-bold flex items-center justify-center">
                            0
                        </span>

                    </button>

                    <div id="spk-dropdown"
                        class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-gray-100 py-2 hidden">

                        <div class="px-4 py-2 border-b text-sm font-semibold">
                            SPK Baru
                        </div>

                        <div id="spk-list" class="max-h-60 overflow-y-auto">

                            <div class="p-4 text-center text-gray-400">
                                Memuat...
                            </div>

                        </div>

                        <div class="border-t px-4 py-2 text-center">
                            <a href="{{ route('gudang.spk') }}"
                                class="text-xs text-blue-600 hover:underline">
                                Lihat Semua SPK
                            </a>
                        </div>

                    </div>

                </div>
                @elseif(auth()->user()->role == 'akuntansi')
                <div class="relative">

                    <button id="spk-menu-btn"
                        class="p-2 text-gray-500 hover:bg-gray-100 rounded-xl transition relative">

                        <svg class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>

                        <span
                            id="spk-badge"
                            class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-blue-600 text-white text-[10px] font-bold flex items-center justify-center">
                            0
                        </span>

                    </button>

                    <div id="spk-dropdown"
                        class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-gray-100 py-2 hidden">

                        <div class="px-4 py-2 border-b text-sm font-semibold">
                            SPK Baru
                        </div>

                        <div id="spk-list" class="max-h-60 overflow-y-auto">

                            <div class="p-4 text-center text-gray-400">
                                Memuat...
                            </div>

                        </div>

                        <div class="border-t px-4 py-2 text-center">
                            <a href="{{ route('gudang.spk') }}"
                                class="text-xs text-blue-600 hover:underline">
                                Lihat Semua SPK
                            </a>
                        </div>

                    </div>

                </div>
                @endif
                <div class="relative">
                    <button id="user-menu-btn" class="flex items-center gap-3 hover:bg-gray-100 px-3 py-1.5 rounded-xl transition">
                        <div class="w-8 h-8 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center text-blue-600 text-xs font-bold">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <span class="text-sm font-medium hidden md:block">{{ auth()->user()->name ?? 'Administrator' }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
    
                    <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 hidden animate-in fade-in zoom-in-95 duration-200">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </header>

        <div class="p-6 md:p-8">
            @yield('content')
        </div>
    </main>

    <script>
        const msgBtn = document.getElementById('msg-menu-btn');
        const msgDropdown = document.getElementById('msg-dropdown');

        const userMenuBtn = document.getElementById('user-menu-btn');
        const userDropdown = document.getElementById('user-dropdown');

        const spkBtn = document.getElementById('spk-menu-btn');
        const spkDropdown = document.getElementById('spk-dropdown');

        async function loadSpk() {

            const list = document.getElementById('spk-list');

            try {

                const response = await fetch('/api/gudang/spk');
                const data = await response.json();

                updateSpkBadge(data.length);

                list.innerHTML = '';

                if (!data.length) {

                    list.innerHTML = `
                        <div class="p-4 text-center text-gray-400 text-sm">
                            Tidak ada SPK baru
                        </div>
                    `;

                    return;
                }

                data.forEach(spk => {

                    let quotation = null;

                    if(spk.quotation){

                        quotation = spk.quotation;

                    }
                    else if(spk.warehouse){

                        quotation = spk.warehouse.quotation;

                    }

                    console.log(spk);



                    list.innerHTML += `

                    <a href="/spk"
                        class="block px-4 py-3 hover:bg-gray-50 border-b">


                        <p class="font-semibold text-sm">
                            ${spk.spk_number}
                        </p>


                        <p class="text-xs text-gray-500">
                            Quotation :
                            ${quotation?.quotation_number ?? '-'}
                        </p>


                        <p class="text-xs text-gray-500">
                            Customer :
                            ${quotation?.nama_customer ?? '-'}
                        </p>


                        <p class="text-[10px] text-gray-400 mt-1">
                            ${new Date(spk.created_at)
                            .toLocaleDateString('id-ID')}
                        </p>


                    </a>

                    `;

                });

            } catch (e) {

                list.innerHTML = `
                    <div class="p-4 text-center text-red-500 text-sm">
                        Gagal memuat SPK
                    </div>
                `;

                console.error(e);

            }

        }

        function updateSpkBadge(total) {

            const badge = document.getElementById('spk-badge');

            if (!badge) return;

            if (total > 0) {

                badge.classList.remove('hidden');
                badge.innerText = total > 99 ? '99+' : total;

            } else {

                badge.classList.add('hidden');

            }

        }

        async function loadSpkCount() {

            try {

                const response = await fetch('/api/gudang/spk-count');
                const data = await response.json();

                updateSpkBadge(data.count);

            } catch (e) {

                console.error(e);

            }

        }

        if (spkBtn) {

            spkBtn.addEventListener('click', function(e){

                e.stopPropagation();

                msgDropdown?.classList.add('hidden');
                userDropdown?.classList.add('hidden');

                spkDropdown.classList.toggle('hidden');

                if(!spkDropdown.classList.contains('hidden')){

                    loadSpk();

                }

                if (
                    spkDropdown &&
                    spkBtn &&
                    !spkBtn.contains(e.target) &&
                    !spkDropdown.contains(e.target)
                ) {
                    spkDropdown.classList.add('hidden');
                }

            });

        }

        const menuBtn = document.getElementById('menu-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        // ==========================
        // Sidebar Mobile
        // ==========================
        if (menuBtn) {
            menuBtn.addEventListener('click', () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }

        // ==========================
        // Load Notification
        // ==========================
        async function loadNotifications() {
            const msgList = document.getElementById('msg-list');

            try {
                const response = await fetch('/api/gudang/requests');
                const data = await response.json();

                updateBadge(data.length);

                msgList.innerHTML = '';

                if (!data.length) {
                    msgList.innerHTML = `
                        <div class="p-4 text-sm text-gray-400 text-center">
                            Tidak ada permintaan baru
                        </div>
                    `;
                    return;
                }

                data.forEach(req => {

                    msgList.innerHTML += `
                        <a href="{{ route('gudang.requests') }}"
                        class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100">

                            <p class="font-medium text-sm">
                                Quotation : ${req.quotation.quotation_number}
                            </p>

                            <p class="text-xs text-gray-500">
                                Customer : ${req.quotation.nama_customer}
                            </p>

                            <p class="text-[10px] text-gray-400 mt-1">
                                Baru saja
                            </p>

                        </a>
                    `;
                });

            } catch (e) {

                msgList.innerHTML = `
                    <div class="p-4 text-center text-red-500 text-sm">
                        Gagal memuat data.
                    </div>
                `;

                console.error(e);
            }
        }

        document.addEventListener('visibilitychange', () => {

            if (!document.hidden) {
                loadNotificationCount();
            }

        });

        async function loadNotificationCount() {

            try {

                const response = await fetch('/api/gudang/request-count');

                const data = await response.json();

                updateBadge(data.count);

            } catch (e) {
                console.error(e);
            }
        }

        // ==========================
        // Message Dropdown
        // ==========================
        if (msgBtn) {
            msgBtn.addEventListener('click', function (e) {

                e.stopPropagation();

                // tutup dropdown user
                userDropdown?.classList.add('hidden');

                msgDropdown.classList.toggle('hidden');

                if (!msgDropdown.classList.contains('hidden')) {
                    loadNotifications();
                }
            });
        }

        // ==========================
        // User Dropdown
        // ==========================
        if (userMenuBtn) {
            userMenuBtn.addEventListener('click', function (e) {

                e.stopPropagation();

                // tutup dropdown pesan
                msgDropdown?.classList.add('hidden');

                userDropdown.classList.toggle('hidden');
            });
        }

        // ==========================
        // Klik di luar dropdown
        // ==========================
        document.addEventListener('click', function (e) {

            if (
                msgDropdown &&
                msgBtn &&
                !msgBtn.contains(e.target) &&
                !msgDropdown.contains(e.target)
            ) {
                msgDropdown.classList.add('hidden');
            }

            if (
                userDropdown &&
                userMenuBtn &&
                !userMenuBtn.contains(e.target) &&
                !userDropdown.contains(e.target)
            ) {
                userDropdown.classList.add('hidden');
            }

        });

        // ==========================
        // Format Rupiah
        // ==========================
        function formatRupiah(input) {
            let value = input.value.replace(/[^0-9]/g, '');

            if (value) {
                input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            } else {
                input.value = '';
            }

            if (input.id == "harga_per_box") {
                calculateTotalOrder();
            }

            if (input.id == "sisa_pembayaran") {
                terbilang(value, 'terbilang');
            }
        }

        function updateBadge(total) {

            const badge = document.getElementById('msg-badge');

            if (!badge) return;

            if (total > 0) {

                badge.classList.remove('hidden');

                // Jika lebih dari 99 tampilkan 99+
                badge.innerText = total > 99 ? '99+' : total;

            } else {

                badge.classList.add('hidden');

            }

        }

        function terbilang(bilangan, targetId) {

            bilangan = String(bilangan).replace(/\./g, '');

            if (bilangan == "" || isNaN(bilangan)) {
                document.getElementById(targetId).value = "";
                return;
            }

            const angka = [
                "", "Satu", "Dua", "Tiga", "Empat", "Lima",
                "Enam", "Tujuh", "Delapan", "Sembilan",
                "Sepuluh", "Sebelas"
            ];

            function baca(n) {

                if (n < 12) return " " + angka[n];
                if (n < 20) return baca(n - 10) + " Belas";
                if (n < 100) return baca(Math.floor(n / 10)) + " Puluh" + baca(n % 10);
                if (n < 200) return " Seratus" + baca(n - 100);
                if (n < 1000) return baca(Math.floor(n / 100)) + " Ratus" + baca(n % 100);
                if (n < 2000) return " Seribu" + baca(n - 1000);
                if (n < 1000000) return baca(Math.floor(n / 1000)) + " Ribu" + baca(n % 1000);
                if (n < 1000000000) return baca(Math.floor(n / 1000000)) + " Juta" + baca(n % 1000000);
                if (n < 1000000000000) return baca(Math.floor(n / 1000000000)) + " Milyar" + baca(n % 1000000000);

                return baca(Math.floor(n / 1000000000000)) + " Triliun" + baca(n % 1000000000000);
            }

            document.getElementById(targetId).value =
                baca(parseInt(bilangan)).trim() + " Rupiah";
        }

        function calculateTotalOrder() {

            const jumlahBoxInput = document.getElementById('jumlah_box');
            const hargaPerBoxInput = document.getElementById('harga_per_box');
            const totalOrderInput = document.getElementById('total_order');

            if (!jumlahBoxInput || !hargaPerBoxInput || !totalOrderInput) return;

            const jumlahBox =
                parseInt(jumlahBoxInput.value.replace(/\./g, '')) || 0;

            const hargaPerBox =
                parseInt(hargaPerBoxInput.value.replace(/\./g, '')) || 0;

            totalOrderInput.value = (jumlahBox * hargaPerBox).toLocaleString('id-ID');
        }

        loadNotificationCount();
        loadSpkCount();
    </script>
</body>
</html>