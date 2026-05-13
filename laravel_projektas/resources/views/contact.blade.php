@extends('layouts.app')

@section('title', 'Kontaktai – Kubiliuks')

@section('content')
@php
    $sellerName = 'Žaneta Selemonavičė';
    $brandName = 'Kubiliuks';
    $activityText = 'Veikla vykdoma pagal individualią veiklą';
    $activityNumber = '1014392';

    $phoneDisplay = '+370 684 50267';
    $phoneTel = '+37068450267';

    $email = 'info@kubiliuks.lt';

    $addressText = 'Pamatlindžių g. 5-2, Pamatlindžių k., Kelmės r. sav., Lietuva';

    $lat = '55.744584';
    $lng = '23.058221';

    $mailSubject = rawurlencode('Užklausa');

    $googleMapsLink = 'https://www.google.com/maps?q=' . $lat . ',' . $lng;
    $googleMapsEmbed = 'https://maps.google.com/maps?width=100%25&height=600&hl=lt&q=' . $lat . ',' . $lng . '&t=k&z=18&ie=UTF8&iwloc=B&output=embed';
@endphp

<section class="bg-stone-50">
    <div class="max-w-6xl mx-auto px-4 lg:px-0 py-12 lg:py-16">
        <div class="text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-stone-900">Kontaktai</h1>
            <div class="mt-4 h-1 w-28 bg-emerald-600 mx-auto rounded-full"></div>
        </div>

        <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="tel:{{ $phoneTel }}"
               class="group bg-white rounded-3xl border border-stone-200 shadow-sm hover:shadow-lg transition overflow-hidden">
                <div class="p-6">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700">
                        <svg viewBox="0 0 24 24" fill="none" class="w-7 h-7" aria-hidden="true">
                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2c-3.2-.3-6.3-1.4-9-3.2a20.8 20.8 0 0 1-6.3-6.3C2.7 9.7 1.6 6.6 1.3 3.4A2 2 0 0 1 3.3 1.2h3a2 2 0 0 1 2 1.7c.2 1.3.5 2.6 1 3.8a2 2 0 0 1-.5 2.1L7.5 10a16.7 16.7 0 0 0 6.3 6.3l1.2-1.3a2 2 0 0 1 2.1-.5c1.2.5 2.5.8 3.8 1A2 2 0 0 1 22 16.9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div class="mt-4 text-sm font-semibold text-stone-900">Telefonas</div>
                    <div class="mt-1 text-stone-600">{{ $phoneDisplay }}</div>
                </div>
            </a>

            <a href="mailto:{{ $email }}?subject={{ $mailSubject }}"
               class="group bg-white rounded-3xl border border-stone-200 shadow-sm hover:shadow-lg transition overflow-hidden">
                <div class="p-6">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700">
                        <svg viewBox="0 0 24 24" fill="none" class="w-7 h-7" aria-hidden="true">
                            <path d="M4 6h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="m22 8-10 7L2 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div class="mt-4 text-sm font-semibold text-stone-900">El. paštas</div>
                    <div class="mt-1 text-stone-600 break-all">{{ $email }}</div>
                </div>
            </a>

            <a href="{{ $googleMapsLink }}"
               target="_blank"
               rel="noopener noreferrer"
               class="group bg-white rounded-3xl border border-stone-200 shadow-sm hover:shadow-lg transition overflow-hidden">
                <div class="p-6">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700">
                        <svg viewBox="0 0 24 24" fill="none" class="w-7 h-7" aria-hidden="true">
                            <path d="M12 22s7-4.6 7-12a7 7 0 1 0-14 0c0 7.4 7 12 7 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M12 13.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </div>

                    <div class="mt-4 text-sm font-semibold text-stone-900">Adresas</div>
                    <div class="mt-1 text-stone-600">{{ $addressText }}</div>
                </div>
            </a>
        </div>

        <div class="mt-10 bg-white rounded-3xl border border-stone-200 shadow-sm p-6 sm:p-8">
            <h2 class="text-2xl font-extrabold tracking-tight text-stone-900">Pardavėjo rekvizitai</h2>

            <div class="mt-6 max-w-3xl space-y-4 text-[15px] sm:text-base leading-8 text-stone-700">
                <div>
                    <span class="font-semibold text-stone-900">Prekės ženklas:</span>
                    {{ $brandName }}
                </div>

                <div>
                    <span class="font-semibold text-stone-900">Pardavėja:</span>
                    {{ $sellerName }}
                </div>

                <div>
                    <span class="font-semibold text-stone-900">Veikla:</span>
                    {{ $activityText }}
                </div>

                <div>
                    <span class="font-semibold text-stone-900">Individualios veiklos pažymos Nr.:</span>
                    {{ $activityNumber }}
                </div>

                <div>
                    <span class="font-semibold text-stone-900">Telefonas:</span>
                    <a href="tel:{{ $phoneTel }}" class="text-emerald-700 hover:text-emerald-800">
                        {{ $phoneDisplay }}
                    </a>
                </div>

                <div>
                    <span class="font-semibold text-stone-900">El. paštas:</span>
                    <a href="mailto:{{ $email }}" class="text-emerald-700 hover:text-emerald-800 break-all">
                        {{ $email }}
                    </a>
                </div>

                <div>
                    <span class="font-semibold text-stone-900">Adresas:</span>
                    {{ $addressText }}
                </div>
            </div>

            <a href="{{ route('terms') }}"
               class="mt-6 inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700">
                Atidaryti taisykles
            </a>
        </div>

        <div class="mt-10 bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="relative w-full" style="padding-top: 52%;">
                <iframe
                    src="{{ $googleMapsEmbed }}"
                    class="absolute inset-0 w-full h-full"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Kubiliuks – žemėlapis"
                ></iframe>
            </div>
        </div>
    </div>
</section>
@endsection