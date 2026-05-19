@extends('layouts.app')

@section('title', 'Kubiliuks – elektroninė parduotuvė')

@section('content')
@php
    $popularProducts = $popularProducts ?? collect([]);

    $imgUrl = function ($path) {
        if (!$path) return asset('images/background.jpg');

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        if (str_starts_with($path, '/')) return asset(ltrim($path, '/'));

        return asset('storage/' . ltrim($path, '/'));
    };

    $heroPics = $popularProducts->take(3)->values();
@endphp

<style>
    .kub-home-page {
        background: linear-gradient(180deg, #f8faf9 0%, #ffffff 40%, #f8faf9 100%);
    }

    .kub-hero {
        position: relative;
        min-height: calc(100vh - 86px);
        width: 100%;
        overflow: hidden;
        display: flex;
        align-items: center;
        background:
            linear-gradient(90deg, rgba(6, 19, 13, 0.84) 0%, rgba(7, 25, 18, 0.70) 34%, rgba(8, 26, 18, 0.48) 58%, rgba(4, 15, 11, 0.68) 100%),
            url('{{ asset('images/background.jpg') }}') center center / cover no-repeat;
    }

    .kub-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(560px 340px at 18% 46%, rgba(16, 185, 129, 0.17), transparent 60%),
            radial-gradient(420px 280px at 78% 22%, rgba(255, 255, 255, 0.06), transparent 55%);
        pointer-events: none;
    }

    .kub-hero::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        height: 180px;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.28), transparent);
        pointer-events: none;
    }

    .kub-hero-inner {
        position: relative;
        z-index: 2;
        width: 100%;
    }

    .kub-hero-title {
        font-size: clamp(4rem, 8vw, 7rem);
        line-height: 0.90;
        letter-spacing: -0.06em;
        font-weight: 900;
        color: #ffffff;
    }

    .kub-hero-subtitle {
        margin-top: 10px;
        font-size: clamp(2rem, 3.5vw, 3.4rem);
        line-height: 0.98;
        letter-spacing: -0.04em;
        font-weight: 800;
        color: #6ee7b7;
        max-width: 620px;
    }

    .kub-hero-actions a {
        transition: transform 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
    }

    .kub-hero-actions a:hover {
        transform: translateY(-2px);
    }

    .kub-hero-gallery {
        position: relative;
        height: 640px;
        width: 100%;
    }

    .kub-hero-card {
        position: absolute;
        overflow: hidden;
        border-radius: 38px;
        transition: transform 0.35s ease, box-shadow 0.35s ease, filter 0.35s ease;
        box-shadow: 0 28px 70px rgba(0, 0, 0, 0.28);
        backdrop-filter: blur(2px);
    }

    .kub-hero-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 35px 90px rgba(0, 0, 0, 0.34);
        filter: brightness(1.03);
    }

    .kub-hero-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.45s ease;
    }

    .kub-hero-card:hover img {
        transform: scale(1.06);
    }

    .kub-hero-card-1 {
        left: 70px;
        top: 150px;
        width: 300px;
        height: 470px;
        transform: rotate(-8deg);
        border: 3px solid rgba(250, 204, 21, 0.70);
        z-index: 1;
    }

    .kub-hero-card-2 {
        left: 255px;
        top: 195px;
        width: 275px;
        height: 395px;
        transform: rotate(2deg);
        border: 3px solid rgba(52, 211, 153, 0.70);
        z-index: 3;
    }

    .kub-hero-card-3 {
        left: 395px;
        top: 110px;
        width: 315px;
        height: 455px;
        transform: rotate(8deg);
        border: 3px solid rgba(96, 165, 250, 0.72);
        z-index: 2;
    }

    .kub-section-title {
        font-size: clamp(2.1rem, 4vw, 3.8rem);
        line-height: 0.96;
        letter-spacing: -0.04em;
        font-weight: 900;
        color: #111827;
    }

    .kub-product-card {
        overflow: hidden;
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
        transition: transform 0.24s ease, box-shadow 0.24s ease, border-color 0.24s ease;
    }

    .kub-product-card:hover {
        transform: translateY(-6px);
        border-color: rgba(16, 185, 129, 0.20);
        box-shadow: 0 22px 60px rgba(15, 23, 42, 0.12);
    }

    .kub-product-cover {
        height: 250px;
        background-size: cover;
        background-position: center;
    }

    .kub-cta {
        position: relative;
        overflow: hidden;
        border-radius: 40px;
        background: linear-gradient(135deg, #138272 0%, #0f6b61 42%, #0b2540 100%);
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.15);
    }

    .kub-cta::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(420px 260px at 16% 24%, rgba(52, 211, 153, 0.22), transparent 60%),
            radial-gradient(420px 260px at 82% 74%, rgba(255, 255, 255, 0.08), transparent 58%);
        pointer-events: none;
    }

    .kub-cta::after {
        content: "";
        position: absolute;
        inset: 0;
        opacity: 0.07;
        background-image:
            linear-gradient(to right, rgba(255,255,255,0.25) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(255,255,255,0.25) 1px, transparent 1px);
        background-size: 54px 54px;
        pointer-events: none;
    }

    @media (max-width: 1399px) {
        .kub-hero-gallery {
            height: 600px;
        }

        .kub-hero-card-1 {
            left: 35px;
            width: 270px;
            height: 430px;
        }

        .kub-hero-card-2 {
            left: 190px;
            width: 250px;
            height: 360px;
        }

        .kub-hero-card-3 {
            left: 315px;
            width: 285px;
            height: 415px;
        }
    }

    @media (max-width: 1199px) {
        .kub-hero-card-1 {
            left: 10px;
            width: 240px;
            height: 390px;
        }

        .kub-hero-card-2 {
            left: 145px;
            width: 220px;
            height: 330px;
        }

        .kub-hero-card-3 {
            left: 255px;
            width: 255px;
            height: 380px;
        }
    }

    @media (max-width: 1023px) {
        .kub-hero {
            min-height: auto;
            padding: 56px 0 62px;
        }

        .kub-hero-gallery {
            margin-top: 26px;
            height: auto;
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .kub-hero-card,
        .kub-hero-card-1,
        .kub-hero-card-2,
        .kub-hero-card-3 {
            position: relative;
            inset: auto;
            width: 100%;
            height: 240px;
            transform: none;
        }

        .kub-hero-card-1 {
            height: 320px;
        }
    }
</style>

<div class="kub-home-page">
    <section class="kub-hero">
        <div class="kub-hero-inner">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-10 lg:grid-cols-[1.02fr_0.98fr]">
                    <div>
                        <h1 class="kub-hero-title">Kubiliuks</h1>

                        <p class="kub-hero-subtitle">Kokybiški kubilai jūsų poilsiui</p>

                        <div class="kub-hero-actions mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('prekes') }}"
                               class="inline-flex items-center justify-center rounded-full bg-emerald-500 px-8 py-3.5 text-sm font-bold text-white shadow-[0_18px_40px_rgba(16,185,129,0.28)] hover:bg-emerald-400">
                                Prekės
                            </a>

                            <a href="{{ route('kontaktai') }}"
                               class="inline-flex items-center justify-center rounded-full border border-white/18 bg-white/10 px-8 py-3.5 text-sm font-semibold text-white backdrop-blur hover:bg-white/14">
                                Kontaktai
                            </a>
                        </div>
                    </div>

                    <div class="hidden lg:block">
                        <div class="kub-hero-gallery">
                            <a href="{{ route('prekes') }}" class="kub-hero-card kub-hero-card-1">
                                <img src="{{ $heroPics->get(0) ? $imgUrl($heroPics->get(0)->image ?? null) : asset('images/background.jpg') }}" alt="Kubiliuks gaminys">
                            </a>

                            <a href="{{ route('prekes') }}" class="kub-hero-card kub-hero-card-2">
                                <img src="{{ $heroPics->get(1) ? $imgUrl($heroPics->get(1)->image ?? null) : asset('images/background.jpg') }}" alt="Kubiliuks gaminys">
                            </a>

                            <a href="{{ route('prekes') }}" class="kub-hero-card kub-hero-card-3">
                                <img src="{{ $heroPics->get(2) ? $imgUrl($heroPics->get(2)->image ?? null) : asset('images/background.jpg') }}" alt="Individualus užsakymas">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="kub-section-title">Populiariausi gaminiai</h2>
            </div>

            <a href="{{ route('prekes') }}" class="inline-flex items-center text-sm font-bold text-stone-700 transition hover:text-emerald-700">
                Visi gaminiai →
            </a>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
            @forelse($popularProducts->take(4) as $p)
                <a href="{{ route('store.products.show', $p->slug) }}" class="kub-product-card">
                    <div class="kub-product-cover" style="background-image:url('{{ $imgUrl($p->image ?? null) }}')"></div>

                    <div class="p-5">
                        <div class="text-lg font-extrabold leading-tight text-stone-950">{{ $p->name }}</div>

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <div class="text-base font-bold text-stone-700">
                                {{ number_format((float)($p->price ?? 0), 2, ',', ' ') }} €
                            </div>

                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-700">
                                Žiūrėti
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-[28px] border border-dashed border-stone-300 bg-white/80 px-6 py-12 text-center text-stone-500">
                    Katalogas pildomas.
                </div>
            @endforelse
        </div>
    </section>

    <section class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 pt-2 pb-14 lg:pb-20">
        <div class="kub-cta px-6 py-10 sm:px-8 lg:px-14 lg:py-14">
            <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-5 py-2 text-[11px] font-bold uppercase tracking-[0.22em] text-white/85 backdrop-blur">
                        Individualus užsakymas
                    </div>

                    <h2 class="mt-5 text-white text-[clamp(2.3rem,4vw,4.2rem)] leading-[0.95] tracking-[-0.04em] font-black">
                        Reikia kito varianto?
                    </h2>

                    <p class="mt-4 text-lg leading-7 font-medium text-white/80">
                        Suderinsime jums tinkamą sprendimą.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('kontaktai') }}"
                       class="inline-flex items-center justify-center rounded-full bg-white px-7 py-3.5 text-sm font-bold text-stone-950 transition hover:-translate-y-0.5 hover:bg-emerald-50">
                        Susisiekti
                    </a>

                    <a href="{{ route('prekes') }}"
                       class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 px-7 py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-white/14">
                        Peržiūrėti gaminius
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection