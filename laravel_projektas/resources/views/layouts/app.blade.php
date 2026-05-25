<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Kubiliuks')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="verify-paysera" content="b7b565984b531fbd3c9d00e127f46f1a">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
</head>
<body class="bg-white text-stone-900 overflow-x-hidden">

<div class="min-h-screen flex flex-col">
    @include('partials.store_header')

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="site-footer border-t border-black/10 bg-stone-950 text-white">
        <div class="max-w-6xl mx-auto px-4 lg:px-0 py-12">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-1">
                    <div class="text-[22px] font-extrabold tracking-tight">Kubiliuks</div>
                    <p class="mt-4 max-w-md text-sm leading-6 text-stone-300">
                        Kokybiški medienos gaminiai namams, poilsiui ir kiemui.
                        Patogus užsakymas internetu ir aiškus bendravimas su klientu.
                    </p>
                </div>

                <div>
                    <div class="text-sm font-bold uppercase tracking-[0.18em] text-stone-400">Nuorodos</div>

                    <ul class="mt-4 space-y-3 text-sm">
                        <li>
                            <a href="{{ route('home') }}" class="text-stone-200 transition hover:text-white">
                                Pradžia
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('prekes') }}" class="text-stone-200 transition hover:text-white">
                                Prekės
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('kontaktai') }}" class="text-stone-200 transition hover:text-white">
                                Kontaktai
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('terms') }}" class="text-stone-200 transition hover:text-white">
                                Taisyklės
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('build.tub') }}" class="text-stone-200 transition hover:text-white">
                                Susikurk kubilą
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <div class="text-sm font-bold uppercase tracking-[0.18em] text-stone-400">Kontaktai</div>

                    <div class="mt-4 space-y-3 text-sm text-stone-200">
                        <div class="break-all sm:break-normal">
                            <span class="text-stone-400">El. paštas:</span>
                            <a href="mailto:info@kubiliuks.lt" class="ml-2 transition hover:text-white">
                                info@kubiliuks.lt
                            </a>
                        </div>

                        <div>
                            <span class="text-stone-400">Telefonas:</span>
                            <a href="tel:+37068450267" class="ml-2 transition hover:text-white">
                                +370 684 50267
                            </a>
                        </div>

                        <div>
                            <span class="text-stone-400">Adresas:</span>
                            <span class="ml-2">Pamatlindžių g. 5-2, Pamatlindžių k., Kelmės r. sav., Lietuva</span>
                        </div>

                        <div>
                            <span class="text-stone-400">IV Nr.:</span>
                            <span class="ml-2">1014392</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-white/10 pt-6 text-sm text-stone-400 sm:flex-row sm:items-center sm:justify-between">
                <small>© {{ date('Y') }} Kubiliuks. Visos teisės saugomos.</small>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                    <a href="{{ route('privacy') }}" class="transition hover:text-white">
                        Privatumo politika
                    </a>

                    <span class="hidden sm:inline text-stone-600">•</span>

                    <a href="{{ route('cookies') }}" class="transition hover:text-white">
                        Slapukų politika
                    </a>

                    <span class="hidden sm:inline text-stone-600">•</span>

                    <a href="{{ route('terms') }}" class="transition hover:text-white">
                        Taisyklės
                    </a>
                </div>
            </div>
        </div>
    </footer>
</div>

@include('partials.cookie-consent')
@include('partials.store_header_scripts')
@yield('scripts')

</body>
</html>