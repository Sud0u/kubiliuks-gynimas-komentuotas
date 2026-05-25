<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Administravimas' }} – Kubiliuks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <style>
        .page-bg{
            background:
                radial-gradient(900px 600px at 15% 10%, #f5f5f4 0%, rgba(245,245,244,0) 55%),
                radial-gradient(900px 600px at 85% 15%, #ecfdf5 0%, rgba(236,253,245,0) 55%),
                radial-gradient(900px 700px at 50% 95%, #fafaf9 0%, rgba(250,250,249,0) 55%),
                #f8f7f4;
        }
        .board{
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 18px 55px rgba(28,25,23,0.08);
        }
        .sidebar-surface{
            background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
        }
        .soft2{
            box-shadow: 0 8px 18px rgba(28,25,23,0.06);
        }
        .btn-emerald{
            background: linear-gradient(180deg, #059669 0%, #047857 100%);
        }
        .fade-in{
            animation: fadeIn .25s ease-out both;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .nav-icon{
            width: 18px;
            height: 18px;
        }
        .admin-mobile-overlay{
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }
    </style>
</head>

<body class="min-h-screen page-bg text-stone-900">
@php
    $path = request()->path();

    $isDashboard = str_contains($path, 'admin/dashboard');
    $isProducts = str_contains($path, 'admin/products');
    $isOrders = str_contains($path, 'admin/orders');

    $is = fn($s) => str_contains($path, $s);
@endphp

<div class="w-full lg:max-w-[1320px] lg:mx-auto px-2 sm:px-4 py-4 sm:py-6 lg:py-10">
    <div class="board rounded-[20px] sm:rounded-[30px] lg:rounded-[36px] p-2 sm:p-5 lg:p-8">

        <div class="lg:hidden mb-4">
            <div class="flex items-center justify-between gap-3 rounded-[22px] border border-black/10 bg-white px-3 py-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-2xl sidebar-surface flex items-center justify-center shrink-0">
                        <div class="w-5 h-5 rounded-xl bg-emerald-400/90"></div>
                    </div>

                    <div class="min-w-0">
                        <div class="font-extrabold text-base leading-none truncate">Kubiliuks</div>
                        <div class="text-xs text-stone-500 mt-1">Admin zona</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('admin.products.create') }}"
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-white text-sm font-semibold btn-emerald">
                        + Prekė
                    </a>

                    <button
                        type="button"
                        id="adminMobileMenuBtn"
                        class="inline-flex items-center justify-center w-11 h-11 rounded-xl border border-black/10 bg-white text-stone-900"
                        aria-label="Atidaryti meniu"
                        aria-expanded="false"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M4 6H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mt-3 grid grid-cols-3 gap-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="text-center rounded-2xl px-3 py-3 text-sm font-semibold border transition
                   {{ $isDashboard ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-stone-700 border-black/10 hover:bg-stone-50' }}">
                    Apžvalga
                </a>

                <a href="{{ route('admin.products.index') }}"
                   class="text-center rounded-2xl px-3 py-3 text-sm font-semibold border transition
                   {{ $isProducts ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-stone-700 border-black/10 hover:bg-stone-50' }}">
                    Prekės
                </a>

                <a href="{{ route('admin.orders.index') }}"
                   class="text-center rounded-2xl px-3 py-3 text-sm font-semibold border transition
                   {{ $isOrders ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-stone-700 border-black/10 hover:bg-stone-50' }}">
                    Užsakymai
                </a>
            </div>
        </div>

        <div id="adminMobileOverlay" class="admin-mobile-overlay fixed inset-0 z-40 hidden lg:hidden"></div>

        <div id="adminMobileSidebar"
             class="fixed top-0 left-0 bottom-0 z-50 w-[88%] max-w-[320px] sidebar-surface text-white p-5 transform -translate-x-full transition-transform duration-300 lg:hidden overflow-y-auto">
            <div class="flex items-center justify-between gap-3 mb-6">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-2xl bg-white/10 flex items-center justify-center shrink-0">
                        <div class="w-6 h-6 rounded-xl bg-emerald-400/90"></div>
                    </div>

                    <div class="min-w-0">
                        <div class="font-extrabold text-xl leading-none">Kubiliuks</div>
                        <div class="text-xs text-white/60 mt-1">Admin zona</div>
                    </div>
                </div>

                <button
                    type="button"
                    id="adminMobileCloseBtn"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 text-white"
                    aria-label="Uždaryti meniu"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <nav class="space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ $isDashboard ? 'bg-white text-stone-900' : 'hover:bg-white/10' }} transition">
                    <span class="w-9 h-9 rounded-xl {{ $isDashboard ? 'bg-emerald-100 text-emerald-700' : 'bg-white/10 text-white' }} flex items-center justify-center">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M4 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M8 19V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 19V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M16 19V13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M20 19V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="font-semibold">Apžvalga</span>
                </a>

                <a href="{{ route('admin.products.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ $isProducts ? 'bg-white text-stone-900' : 'hover:bg-white/10' }} transition">
                    <span class="w-9 h-9 rounded-xl {{ $isProducts ? 'bg-emerald-100 text-emerald-700' : 'bg-white/10 text-white' }} flex items-center justify-center">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M21 8.5V15.5L12 20.5L3 15.5V8.5L12 3.5L21 8.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M12 3.5V20.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M3 8.5L12 13.5L21 8.5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="font-semibold">Prekės</span>
                </a>

                <a href="{{ route('admin.orders.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ $isOrders ? 'bg-white text-stone-900' : 'hover:bg-white/10' }} transition">
                    <span class="w-9 h-9 rounded-xl {{ $isOrders ? 'bg-emerald-100 text-emerald-700' : 'bg-white/10 text-white' }} flex items-center justify-center">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M6 3H18V21L15.5 19.5L13 21L10.5 19.5L8 21L6 19.5L3 21V3H6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M8 7H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M8 11H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M8 15H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="font-semibold">Užsakymai</span>
                </a>
            </nav>

            <div class="pt-8 mt-8 border-t border-white/10 space-y-3">
                <a href="{{ route('admin.products.create') }}"
                   class="block text-center rounded-2xl bg-emerald-500 text-white font-semibold py-3 hover:bg-emerald-600 transition">
                    + Pridėti prekę
                </a>

                <a href="{{ url('/') }}"
                   class="block text-center rounded-2xl bg-white text-stone-900 font-semibold py-3 hover:bg-stone-100 transition">
                    Į parduotuvę
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-2xl bg-rose-500 text-white font-semibold py-3 hover:bg-rose-600 transition">
                        Atsijungti
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6">
            <aside class="hidden lg:flex lg:col-span-3 xl:col-span-2 sidebar-surface rounded-[30px] text-white p-6 flex-col">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/10 flex items-center justify-center">
                        <div class="w-6 h-6 rounded-xl bg-emerald-400/90"></div>
                    </div>

                    <div>
                        <div class="font-extrabold text-xl leading-none">Kubiliuks</div>
                        <div class="text-xs text-white/60 mt-1">Admin zona</div>
                    </div>
                </div>

                <nav class="mt-8 space-y-2">
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ $isDashboard ? 'bg-white text-stone-900' : 'hover:bg-white/10' }} transition">
                        <span class="w-9 h-9 rounded-xl {{ $isDashboard ? 'bg-emerald-100 text-emerald-700' : 'bg-white/10 text-white' }} flex items-center justify-center">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M4 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M8 19V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M12 19V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M16 19V13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M20 19V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="font-semibold">Apžvalga</span>
                    </a>

                    <a href="{{ route('admin.products.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ $isProducts ? 'bg-white text-stone-900' : 'hover:bg-white/10' }} transition">
                        <span class="w-9 h-9 rounded-xl {{ $isProducts ? 'bg-emerald-100 text-emerald-700' : 'bg-white/10 text-white' }} flex items-center justify-center">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M21 8.5V15.5L12 20.5L3 15.5V8.5L12 3.5L21 8.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M12 3.5V20.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M3 8.5L12 13.5L21 8.5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="font-semibold">Prekės</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ $isOrders ? 'bg-white text-stone-900' : 'hover:bg-white/10' }} transition">
                        <span class="w-9 h-9 rounded-xl {{ $isOrders ? 'bg-emerald-100 text-emerald-700' : 'bg-white/10 text-white' }} flex items-center justify-center">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M6 3H18V21L15.5 19.5L13 21L10.5 19.5L8 21L6 19.5L3 21V3H6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M8 7H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M8 11H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M8 15H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="font-semibold">Užsakymai</span>
                    </a>
                </nav>

                <div class="mt-auto pt-10">
                    <div class="rounded-[26px] bg-white/5 p-4 border border-white/10">
                        <div class="grid gap-2">
                            <a href="{{ url('/') }}"
                               class="text-center rounded-2xl bg-white text-stone-900 font-semibold py-2 hover:bg-stone-100 transition">
                                Į parduotuvę
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-2xl bg-rose-500 text-white font-semibold py-2 hover:bg-rose-600 transition">
                                    Atsijungti
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="lg:col-span-9 xl:col-span-10 min-w-0">
                <header class="hidden lg:flex justify-end mb-6">
                    <a href="{{ route('admin.products.create') }}"
                       class="inline-flex items-center gap-2 rounded-2xl px-5 py-3 text-white font-semibold btn-emerald soft2 hover:opacity-95 transition">
                        + <span>Pridėti prekę</span>
                    </a>
                </header>

                <main class="fade-in min-w-0">
                    @if (session('success'))
                        <div class="mb-5 rounded-[22px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 rounded-[22px] border border-rose-200 bg-rose-50 px-5 py-4 text-rose-900">
                            <div class="font-semibold mb-1">Yra klaidų:</div>
                            <ul class="list-disc pl-5 text-sm space-y-1">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</div>

<script>
    const adminMobileMenuBtn = document.getElementById('adminMobileMenuBtn');
    const adminMobileCloseBtn = document.getElementById('adminMobileCloseBtn');
    const adminMobileSidebar = document.getElementById('adminMobileSidebar');
    const adminMobileOverlay = document.getElementById('adminMobileOverlay');

    function openAdminMenu() {
        if (!adminMobileSidebar || !adminMobileOverlay) return;

        adminMobileSidebar.classList.remove('-translate-x-full');
        adminMobileOverlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        if (adminMobileMenuBtn) {
            adminMobileMenuBtn.setAttribute('aria-expanded', 'true');
        }
    }

    function closeAdminMenu() {
        if (!adminMobileSidebar || !adminMobileOverlay) return;

        adminMobileSidebar.classList.add('-translate-x-full');
        adminMobileOverlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        if (adminMobileMenuBtn) {
            adminMobileMenuBtn.setAttribute('aria-expanded', 'false');
        }
    }

    if (adminMobileMenuBtn) {
        adminMobileMenuBtn.addEventListener('click', openAdminMenu);
    }

    if (adminMobileCloseBtn) {
        adminMobileCloseBtn.addEventListener('click', closeAdminMenu);
    }

    if (adminMobileOverlay) {
        adminMobileOverlay.addEventListener('click', closeAdminMenu);
    }

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAdminMenu();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            closeAdminMenu();
        }
    });
</script>
</body>
</html>