@php
    $homeUrl = route('home');
    $productsUrl = route('prekes');
    $contactUrl = route('kontaktai');

    $buildTubUrl = \Illuminate\Support\Facades\Route::has('build.tub')
        ? route('build.tub')
        : url('/susikurk-savo-kubila');

    $cartUrl = \Illuminate\Support\Facades\Route::has('cart')
        ? route('cart')
        : url('/cart');

    $currentUrl = request()->url();
@endphp

<header class="relative z-50 border-b border-black/10 bg-white/95 backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10">
        <div class="h-[72px] flex items-center justify-between gap-3">

            <div class="flex items-center min-w-0">
                <a href="{{ $homeUrl }}" class="inline-flex items-center gap-3 min-w-0">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-emerald-100 bg-emerald-50 shadow-sm">
                        <span class="relative block h-5 w-5">
                            <span class="absolute inset-y-0 left-0 w-[6px] rounded-full bg-emerald-600"></span>
                            <span class="absolute left-[8px] top-0 h-full w-[6px] rounded-full bg-stone-900"></span>
                            <span class="absolute right-0 top-[2px] h-[16px] w-[6px] rounded-full bg-emerald-400"></span>
                        </span>
                    </span>

                    <span class="truncate text-[18px] sm:text-[20px] md:text-[22px] font-extrabold tracking-tight text-stone-900">
                        <span class="text-emerald-700">Kubi</span><span>liuks</span>
                    </span>
                </a>
            </div>

            <nav class="hidden md:flex items-center justify-center gap-8 flex-1 min-w-0">
                <a href="{{ $homeUrl }}" class="nav-link {{ $currentUrl === $homeUrl ? 'nav-link-active' : '' }}">Pradžia</a>
                <a href="{{ $productsUrl }}" class="nav-link {{ $currentUrl === $productsUrl ? 'nav-link-active' : '' }}">Prekės</a>
                <a href="{{ $contactUrl }}" class="nav-link {{ $currentUrl === $contactUrl ? 'nav-link-active' : '' }}">Kontaktai</a>
                <a href="{{ $buildTubUrl }}" class="nav-link whitespace-nowrap {{ $currentUrl === $buildTubUrl ? 'nav-link-active' : '' }}">
                    Susikurk kubilą
                </a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a href="{{ $cartUrl }}" class="relative nav-icon-btn" aria-label="Krepšelis">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <circle cx="8" cy="21" r="1"></circle>
                        <circle cx="19" cy="21" r="1"></circle>
                        <path d="M2 2h3l2.4 12.4a2 2 0 0 0 2 1.6h9.2a2 2 0 0 0 2-1.6L23 6H6" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>

                    <span id="cartCountBadge" class="cart-badge hidden"></span>
                </a>

                <div class="relative hidden md:block" id="accountMenuWrap">
                    <button
                        type="button"
                        id="accountMenuBtn"
                        class="nav-icon-btn"
                        aria-label="Paskyra"
                        aria-expanded="false"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>

                    <div
                        id="accountMenu"
                        class="hidden absolute right-0 mt-2 w-[300px] overflow-hidden rounded-3xl border border-black/10 bg-white shadow-xl"
                    >
                        @auth
                            <div class="border-b border-stone-200 px-5 py-4">
                                <div class="text-[12px] font-bold uppercase tracking-[0.18em] text-stone-400">
                                    Mano paskyra
                                </div>
                                <div class="mt-2 text-[15px] font-semibold text-stone-900 truncate">
                                    {{ auth()->user()->name }}
                                </div>
                                <div class="text-[13px] text-stone-500 truncate">
                                    {{ auth()->user()->email }}
                                </div>
                            </div>

                            <div class="p-2">
                                <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                                    @if(\Illuminate\Support\Facades\Route::has('orders.index'))
                                        <a href="{{ route('orders.index') }}"
                                           class="flex items-center gap-3 px-4 py-4 text-stone-900 hover:bg-stone-50 transition">
                                            <svg class="w-6 h-6 text-stone-700 shrink-0" viewBox="0 0 24 24" fill="none">
                                                <path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span class="text-[16px] font-medium">Užsakymai</span>
                                        </a>
                                    @endif

                                    @if(\Illuminate\Support\Facades\Route::has('profile.edit'))
                                        <a href="{{ route('profile.edit') }}"
                                           class="flex items-center gap-3 border-t border-stone-200 px-4 py-4 text-stone-900 hover:bg-stone-50 transition">
                                            <svg class="w-6 h-6 text-stone-700 shrink-0" viewBox="0 0 24 24" fill="none">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8"></circle>
                                            </svg>
                                            <span class="text-[16px] font-medium">Profilis</span>
                                        </a>
                                    @endif

                                    @if(auth()->user()->is_admin && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
                                        <a href="{{ route('admin.dashboard') }}"
                                           class="flex items-center gap-3 border-t border-stone-200 px-4 py-4 text-stone-900 hover:bg-stone-50 transition">
                                            <svg class="w-6 h-6 text-stone-700 shrink-0" viewBox="0 0 24 24" fill="none">
                                                <path d="M4 19V5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <path d="M8 19V11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <path d="M12 19V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <path d="M16 19V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <path d="M20 19V9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                            <span class="text-[16px] font-medium">Admin panelė</span>
                                        </a>
                                    @endif

                                    <form method="POST" action="{{ route('logout') }}" class="border-t border-stone-200">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="w-full flex items-center gap-3 px-4 py-4 text-left text-stone-900 hover:bg-stone-50 transition"
                                        >
                                            <svg class="w-6 h-6 text-stone-700 shrink-0" viewBox="0 0 24 24" fill="none">
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span class="text-[16px] font-medium">Atsijungti</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="p-3">
                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('login') }}"
                                       class="flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-[14px] font-semibold text-white hover:bg-emerald-700 transition">
                                        Prisijungti
                                    </a>

                                    <a href="{{ route('register') }}"
                                       class="flex items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-[14px] font-medium text-stone-800 hover:bg-stone-50 transition">
                                        Registruotis
                                    </a>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>

                <button
                    type="button"
                    id="mobileMenuBtn"
                    class="md:hidden nav-icon-btn"
                    aria-label="Meniu"
                    aria-expanded="false"
                    aria-controls="mobileNav"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobileMenuBackdrop" class="hidden md:hidden fixed inset-0 z-40 bg-black/40"></div>

    <div
        id="mobileNav"
        class="hidden md:hidden fixed inset-x-0 top-[72px] z-50 max-h-[calc(100dvh-72px)] overflow-y-auto border-t border-black/10 bg-white"
    >
        <div class="px-4 py-4">
            <div class="rounded-3xl border border-stone-200 bg-stone-50 p-3">
                <div class="mb-3 flex items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-emerald-100 bg-emerald-50 shadow-sm">
                        <span class="relative block h-5 w-5">
                            <span class="absolute inset-y-0 left-0 w-[6px] rounded-full bg-emerald-600"></span>
                            <span class="absolute left-[8px] top-0 h-full w-[6px] rounded-full bg-stone-900"></span>
                            <span class="absolute right-0 top-[2px] h-[16px] w-[6px] rounded-full bg-emerald-400"></span>
                        </span>
                    </span>

                    <span class="truncate text-[20px] font-extrabold tracking-tight text-stone-900">
                        <span class="text-emerald-700">Kubi</span><span>liuks</span>
                    </span>
                </div>

                <nav class="flex flex-col gap-1.5">
                    <a href="{{ $homeUrl }}"
                       class="rounded-2xl px-4 py-3 text-[15px] font-semibold text-stone-900 hover:bg-white {{ $currentUrl === $homeUrl ? 'bg-white shadow-sm' : '' }}">
                        Pradžia
                    </a>

                    <a href="{{ $productsUrl }}"
                       class="rounded-2xl px-4 py-3 text-[15px] font-semibold text-stone-900 hover:bg-white {{ $currentUrl === $productsUrl ? 'bg-white shadow-sm' : '' }}">
                        Prekės
                    </a>

                    <a href="{{ $contactUrl }}"
                       class="rounded-2xl px-4 py-3 text-[15px] font-semibold text-stone-900 hover:bg-white {{ $currentUrl === $contactUrl ? 'bg-white shadow-sm' : '' }}">
                        Kontaktai
                    </a>

                    <a href="{{ $buildTubUrl }}"
                       class="rounded-2xl px-4 py-3 text-[15px] font-semibold text-stone-900 hover:bg-white {{ $currentUrl === $buildTubUrl ? 'bg-white shadow-sm' : '' }}">
                        Susikurk kubilą
                    </a>

                    <a href="{{ $cartUrl }}"
                       class="flex items-center justify-between rounded-2xl px-4 py-3 text-[15px] font-semibold text-stone-900 hover:bg-white">
                        <span>Krepšelis</span>
                        <span data-cart-count class="hidden min-w-[24px] rounded-full bg-emerald-600 px-2 py-0.5 text-center text-[12px] font-bold text-white"></span>
                    </a>
                </nav>
            </div>

            @auth
                <div class="mt-4 rounded-3xl border border-stone-200 bg-white p-3">
                    <div class="px-2 pb-2 text-[12px] font-bold uppercase tracking-[0.18em] text-stone-400">
                        Mano paskyra
                    </div>

                    <div class="flex flex-col gap-1.5">
                        @if(\Illuminate\Support\Facades\Route::has('orders.index'))
                            <a href="{{ route('orders.index') }}"
                               class="rounded-2xl px-4 py-3 text-[15px] font-semibold text-stone-900 hover:bg-stone-50">
                                Užsakymai
                            </a>
                        @endif

                        @if(\Illuminate\Support\Facades\Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}"
                               class="rounded-2xl px-4 py-3 text-[15px] font-semibold text-stone-900 hover:bg-stone-50">
                                Profilis
                            </a>
                        @endif

                        @if(auth()->user()->is_admin && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
                            <a href="{{ route('admin.dashboard') }}"
                               class="rounded-2xl px-4 py-3 text-[15px] font-semibold text-stone-900 hover:bg-stone-50">
                                Admin panelė
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full rounded-2xl px-4 py-3 text-left text-[15px] font-semibold text-red-600 hover:bg-red-50"
                            >
                                Atsijungti
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="mt-4 rounded-3xl border border-stone-200 bg-white p-3">
                    <div class="px-2 pb-2 text-[12px] font-bold uppercase tracking-[0.18em] text-stone-400">
                        Paskyra
                    </div>

                    <div class="flex flex-col gap-2">
                        <a href="{{ route('login') }}"
                           class="flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-[15px] font-semibold text-white hover:bg-emerald-700 transition">
                            Prisijungti
                        </a>

                        <a href="{{ route('register') }}"
                           class="flex items-center justify-center rounded-xl border border-stone-300 px-4 py-3 text-[15px] font-medium text-stone-800 hover:bg-stone-50 transition">
                            Registruotis
                        </a>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</header>