@extends('layouts.app')

@section('title', 'Susikurk savo kubilą – Kubiliuks')

@section('content')
@php
    // cia aprasyti pasirinkimai, kuriuos vartotojas mato builderyje.
    $insideOptions = [
        [
            'key' => 'balta',
            'label' => 'Balta',
            'price' => 0,
            'hex' => '#f3f4f6',
            'text_color' => '#1c1917',
        ],
        [
            'key' => 'melyna',
            'label' => 'Mėlyna',
            'price' => 90,
            'hex' => '#5b7cff',
            'text_color' => '#ffffff',
        ],
        [
            'key' => 'raudona',
            'label' => 'Raudona',
            'price' => 90,
            'hex' => '#ff835f',
            'text_color' => '#ffffff',
        ],
        [
            'key' => 'zalia',
            'label' => 'Žalia',
            'price' => 90,
            'hex' => '#42c46f',
            'text_color' => '#ffffff',
        ],
    ];

    $woodOptions = [
        [
            'key' => 'base-ruda',
            'label' => 'Šviesi ruda',
            'price' => 0,
            'hex' => '#8b6a4f',
            'text_color' => '#ffffff',
        ],
        [
            'key' => 'chestnut-ruda',
            'label' => 'Kaštoninė',
            'price' => 120,
            'hex' => '#6c4a35',
            'text_color' => '#ffffff',
        ],
        [
            'key' => 'darkred-ruda',
            'label' => 'Rudai raudona',
            'price' => 160,
            'hex' => '#6a2f24',
            'text_color' => '#ffffff',
        ],
        [
            'key' => 'deepcrimson-ruda',
            'label' => 'Bordo',
            'price' => 210,
            'hex' => '#4c1f1a',
            'text_color' => '#ffffff',
        ],
    ];

    $sizeOptions = [
        [
            'key' => '180',
            'label' => '180 cm',
            'price' => 0,
        ],
        [
            'key' => '200',
            'label' => '200 cm',
            'price' => 300,
        ],
        [
            'key' => '220',
            'label' => '220 cm',
            'price' => 650,
        ],
    ];

    // bazine kaina, nuo kurios skaiciuojami pasirinkimu priedai.
    $basePrice = 2200;
    $imageVersion = 'v3';
    $baseImage = asset('images/kubilai/balta-base-ruda.png') . '?v=' . $imageVersion;
    $imagesBase = asset('images/kubilai');
@endphp

<section class="bg-stone-50">
    <style>
        .builder-panel {
            border-radius: 2rem;
            border: 1px solid rgba(28, 25, 23, 0.08);
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(28, 25, 23, 0.04);
        }

        .builder-preview-shell {
            position: relative;
            overflow: hidden;
            border-radius: 1.75rem;
            border: 1px solid rgba(28, 25, 23, 0.08);
            background:
                radial-gradient(circle at top center, rgba(255,255,255,0.95), transparent 42%),
                linear-gradient(180deg, #fafaf9 0%, #f1f5f9 100%);
        }

        .builder-preview-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.07), transparent 24%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent 22%);
            pointer-events: none;
        }

        .builder-preview-image {
            position: relative;
            z-index: 1;
            max-height: 640px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            user-select: none;
            mix-blend-mode: multiply;
            filter: drop-shadow(0 20px 35px rgba(28, 25, 23, 0.10));
            transition: opacity 0.18s ease, transform 0.22s ease;
            will-change: opacity, transform;
        }

        .builder-preview-image.is-loading {
            opacity: 0.45;
            transform: scale(0.985);
        }

        .builder-swatch-card,
        .builder-size-card {
            border-radius: 1.5rem;
            border: 2px solid #e7e5e4;
            background: #ffffff;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
            cursor: pointer;
        }

        .builder-swatch-card:hover,
        .builder-size-card:hover {
            transform: translateY(-2px);
            border-color: rgba(16, 185, 129, 0.22);
            box-shadow: 0 12px 24px rgba(28, 25, 23, 0.05);
        }

        .builder-swatch-card.is-active,
        .builder-size-card.is-active {
            border-color: #10b981;
            background: #ecfdf5;
            box-shadow: 0 14px 26px rgba(16, 185, 129, 0.10);
        }

        .builder-swatch {
            position: relative;
            height: 3.6rem;
            width: 3.6rem;
            border-radius: 9999px;
            border: 4px solid #ffffff;
            box-shadow:
                0 0 0 1px rgba(28, 25, 23, 0.10),
                0 12px 22px rgba(28, 25, 23, 0.12);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            flex-shrink: 0;
        }

        .builder-swatch-card:hover .builder-swatch,
        .builder-swatch-card.is-active .builder-swatch {
            transform: scale(1.06);
            box-shadow:
                0 0 0 1px rgba(28, 25, 23, 0.10),
                0 16px 28px rgba(28, 25, 23, 0.16);
        }

        .builder-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.72rem 1rem;
            font-size: 0.78rem;
            font-weight: 800;
            line-height: 1;
            transition: background-color 0.18s ease, color 0.18s ease;
        }

        .builder-add-button {
            border-radius: 1rem;
            background: #1c1917;
            color: #ffffff;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
            box-shadow: 0 12px 24px rgba(28, 25, 23, 0.14);
        }

        .builder-add-button:hover {
            transform: translateY(-1px);
            background: #0f0f0f;
            box-shadow: 0 16px 30px rgba(28, 25, 23, 0.18);
        }

        .builder-add-button:active {
            transform: translateY(0);
        }

        @media (max-width: 640px) {
            .builder-option-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10 lg:py-14">
        <div class="max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-emerald-700">
                Individuali komplektacija
            </p>

            <h1 class="mt-3 text-4xl md:text-6xl font-extrabold tracking-tight text-stone-900">
                Susikurk savo kubilą
            </h1>

            <p class="mt-4 text-base md:text-lg leading-8 text-stone-600">
                Pasirink dydį, vidaus spalvą ir medienos atspalvį.
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
            <div class="xl:col-span-7">
                <div class="xl:sticky xl:top-8">
                    <div class="builder-panel overflow-hidden">
                        <div class="px-6 py-5 border-b border-stone-200/80">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-400">
                                Peržiūra
                            </p>
                            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-stone-900">
                                Tavo kubilas
                            </h2>
                        </div>

                        <div class="p-5 md:p-8">
                            <div class="builder-preview-shell">
                                <div class="aspect-[4/3] md:aspect-[16/12] flex items-center justify-center p-4 md:p-8">
                                    {{-- sita nuotrauka keiciasi pagal pasirinkta vidaus ir medienos spalva. --}}
                                    <img
                                        id="tub-preview-image"
                                        src="{{ $baseImage }}"
                                        alt="Pasirinkto kubilo peržiūra"
                                        class="builder-preview-image"
                                    >
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <span id="preview-inside-badge" class="builder-chip text-white bg-stone-900">
                                    Vidus: Balta
                                </span>

                                <span id="preview-wood-badge" class="builder-chip text-white" style="background-color:#8b6a4f;">
                                    Mediena: Šviesi ruda
                                </span>

                                <span id="preview-size-badge" class="builder-chip text-stone-800 bg-stone-200">
                                    Dydis: 180 cm
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-5">
                <div class="space-y-6">
                    <div class="builder-panel overflow-hidden">
                        <div class="px-6 py-5 border-b border-stone-200/80">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-400">
                                Pasirinkimai
                            </p>
                            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-stone-900">
                                Konfigūracija
                            </h2>
                        </div>

                        <div class="p-6">
                            <div>
                                <h3 class="text-2xl font-extrabold tracking-tight text-stone-900">Dydis</h3>

                                <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @foreach($sizeOptions as $index => $size)
                                        <label class="builder-size-card size-card p-4 {{ $index === 0 ? 'is-active' : '' }}">
                                            <input
                                                type="radio"
                                                name="tub_size"
                                                class="sr-only size-option"
                                                value="{{ $size['key'] }}"
                                                data-label="{{ $size['label'] }}"
                                                data-price="{{ $size['price'] }}"
                                                {{ $index === 0 ? 'checked' : '' }}
                                            >

                                            <div class="text-lg font-extrabold text-stone-900">{{ $size['label'] }}</div>

                                            <div class="mt-3 text-sm font-bold {{ $size['price'] > 0 ? 'text-emerald-700' : 'text-stone-500' }}">
                                                {{ $size['price'] > 0 ? '+' . number_format($size['price'], 0, ',', ' ') . ' €' : 'Įskaičiuota' }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-8">
                                <h3 class="text-2xl font-extrabold tracking-tight text-stone-900">Vidaus spalva</h3>

                                <div class="mt-5 grid builder-option-grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach($insideOptions as $index => $inside)
                                        <label class="builder-swatch-card inside-card p-4 {{ $index === 0 ? 'is-active' : '' }}">
                                            <input
                                                type="radio"
                                                name="tub_inside"
                                                class="sr-only inside-option"
                                                value="{{ $inside['key'] }}"
                                                data-label="{{ $inside['label'] }}"
                                                data-price="{{ $inside['price'] }}"
                                                data-hex="{{ $inside['hex'] }}"
                                                data-text-color="{{ $inside['text_color'] }}"
                                                {{ $index === 0 ? 'checked' : '' }}
                                            >

                                            <div class="flex flex-col items-center text-center gap-3">
                                                <span
                                                    class="builder-swatch"
                                                    style="background-color: {{ $inside['hex'] }};"
                                                ></span>

                                                <div class="text-sm font-extrabold text-stone-900">{{ $inside['label'] }}</div>

                                                <div class="text-xs font-bold {{ $inside['price'] > 0 ? 'text-emerald-700' : 'text-stone-500' }}">
                                                    {{ $inside['price'] > 0 ? '+' . number_format($inside['price'], 0, ',', ' ') . ' €' : 'Įskaičiuota' }}
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-8">
                                <h3 class="text-2xl font-extrabold tracking-tight text-stone-900">Medienos spalva</h3>

                                <div class="mt-5 grid builder-option-grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach($woodOptions as $index => $wood)
                                        <label class="builder-swatch-card wood-card p-4 {{ $index === 0 ? 'is-active' : '' }}">
                                            <input
                                                type="radio"
                                                name="tub_wood"
                                                class="sr-only wood-option"
                                                value="{{ $wood['key'] }}"
                                                data-label="{{ $wood['label'] }}"
                                                data-price="{{ $wood['price'] }}"
                                                data-hex="{{ $wood['hex'] }}"
                                                data-text-color="{{ $wood['text_color'] }}"
                                                {{ $index === 0 ? 'checked' : '' }}
                                            >

                                            <div class="flex flex-col items-center text-center gap-3">
                                                <span
                                                    class="builder-swatch"
                                                    style="background: linear-gradient(135deg, {{ $wood['hex'] }} 0%, #2f241d 100%);"
                                                ></span>

                                                <div class="text-sm font-extrabold leading-5 text-stone-900">{{ $wood['label'] }}</div>

                                                <div class="text-xs font-bold {{ $wood['price'] > 0 ? 'text-emerald-700' : 'text-stone-500' }}">
                                                    {{ $wood['price'] > 0 ? '+' . number_format($wood['price'], 0, ',', ' ') . ' €' : 'Įskaičiuota' }}
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="builder-panel overflow-hidden">
                        <div class="px-6 py-5 border-b border-stone-200/80">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-400">
                                Santrauka
                            </p>
                            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-stone-900">
                                Pasirinkimas
                            </h2>
                        </div>

                        <div class="p-6">
                            <div class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-400">
                                Kaina
                            </div>

                            <div id="tub-total-price" class="mt-2 text-4xl md:text-5xl font-extrabold tracking-tight text-stone-900">
                                {{ number_format($basePrice, 0, ',', ' ') }} €
                            </div>

                            <div class="mt-6 space-y-4 text-sm">
                                <div class="flex items-center justify-between gap-4 border-b border-stone-100 pb-3">
                                    <span class="text-stone-500">Dydis</span>
                                    <span id="summary-size" class="font-extrabold text-stone-900">180 cm</span>
                                </div>

                                <div class="flex items-center justify-between gap-4 border-b border-stone-100 pb-3">
                                    <span class="text-stone-500">Vidus</span>
                                    <span id="summary-inside" class="font-extrabold text-stone-900">Balta</span>
                                </div>

                                <div class="flex items-center justify-between gap-4 border-b border-stone-100 pb-3">
                                    <span class="text-stone-500">Mediena</span>
                                    <span id="summary-wood" class="font-extrabold text-stone-900">Šviesi ruda</span>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-stone-500">Gamyba</span>
                                    <span class="font-extrabold text-stone-900">6–8 savaitės</span>
                                </div>
                            </div>

                            <div id="builder-msg" class="hidden mt-5 rounded-2xl border px-4 py-3 text-sm"></div>

                            <div class="mt-6">
                                <button
                                    id="add-custom-tub-btn"
                                    type="button"
                                    class="builder-add-button w-full px-6 py-4 text-base font-semibold"
                                >
                                    Į krepšelį
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const basePrice = {{ $basePrice }};
    const imagesBase = @json($imagesBase);
    const imageVersion = @json($imageVersion);

    // cia pasiimamos pasirinkimu korteles, kad paspaudus atsinaujintu vaizdas ir kaina.
    const sizeCards = document.querySelectorAll('.size-card');

    const sizeOptions = document.querySelectorAll('.size-option');

    const insideCards = document.querySelectorAll('.inside-card');

    const insideOptions = document.querySelectorAll('.inside-option');

    const woodCards = document.querySelectorAll('.wood-card');

    const woodOptions = document.querySelectorAll('.wood-option');

    const previewImage = document.getElementById('tub-preview-image');
    const previewInsideBadge = document.getElementById('preview-inside-badge');
    const previewWoodBadge = document.getElementById('preview-wood-badge');
    const previewSizeBadge = document.getElementById('preview-size-badge');

    const totalPriceElement = document.getElementById('tub-total-price');
    const summarySize = document.getElementById('summary-size');
    const summaryInside = document.getElementById('summary-inside');
    const summaryWood = document.getElementById('summary-wood');

    const addButton = document.getElementById('add-custom-tub-btn');
    const builderMsg = document.getElementById('builder-msg');

    function formatPrice(price) {
        return new Intl.NumberFormat('lt-LT').format(price) + ' €';
    }

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function getSelectedSize() {
        return document.querySelector('.size-option:checked');
    }

    function getSelectedInside() {
        return document.querySelector('.inside-option:checked');
    }

    function getSelectedWood() {
        return document.querySelector('.wood-option:checked');
    }

    function setActiveCard(cards, selectedInput) {
        cards.forEach(card => card.classList.remove('is-active'));

        if (!selectedInput) {
            return;
        }

        const selectedCard = selectedInput.closest('label');
        if (selectedCard) {
            selectedCard.classList.add('is-active');
        }
    }

    // pagal pasirinkta vidu ir mediena sudaromas nuotraukos kelias.
    function currentImagePath() {
        const selectedInside = getSelectedInside();
        const selectedWood = getSelectedWood();

        if (!selectedInside || !selectedWood) {
            return 'images/kubilai/balta-base-ruda.png';
        }

        return `images/kubilai/${selectedInside.value}-${selectedWood.value}.png`;
    }

    // galutine kaina = bazine kaina + pasirinkimu priedai.
    function currentTotal() {
        const selectedSize = getSelectedSize();
        const selectedInside = getSelectedInside();
        const selectedWood = getSelectedWood();

        const sizePrice = selectedSize ? Number(selectedSize.dataset.price) : 0;
        const insidePrice = selectedInside ? Number(selectedInside.dataset.price) : 0;
        const woodPrice = selectedWood ? Number(selectedWood.dataset.price) : 0;

        return basePrice + sizePrice + insidePrice + woodPrice;
    }

    function showBuilderMsg(text, ok = true) {
        if (!builderMsg) return;

        builderMsg.classList.remove('hidden');
        builderMsg.classList.remove(
            'border-green-200', 'bg-green-50', 'text-green-900',
            'border-red-200', 'bg-red-50', 'text-red-900'
        );

        if (ok) {
            builderMsg.classList.add('border-green-200', 'bg-green-50', 'text-green-900');
        } else {
            builderMsg.classList.add('border-red-200', 'bg-red-50', 'text-red-900');
        }

        builderMsg.textContent = text;
    }

    // cia realiai pakeiciama kubilo nuotrauka ekrane.
    function updatePreview() {
        const selectedInside = getSelectedInside();
        const selectedWood = getSelectedWood();

        if (!selectedInside || !selectedWood || !previewImage) {
            return;
        }

        const imageName = `${selectedInside.value}-${selectedWood.value}.png?v=${imageVersion}`;

        const nextSrc = `${imagesBase}/${imageName}`;

        previewImage.classList.add('is-loading');

        const preloader = new Image();
        preloader.onload = function () {
            previewImage.src = nextSrc;

            previewImage.alt = `${selectedInside.dataset.label} vidus ir ${selectedWood.dataset.label} mediena`;

            requestAnimationFrame(() => {
                previewImage.classList.remove('is-loading');
            });
        };

        preloader.onerror = function () {
            previewImage.classList.remove('is-loading');
        };

        preloader.src = nextSrc;
    }

    function updateSummary() {
        const selectedSize = getSelectedSize();
        const selectedInside = getSelectedInside();
        const selectedWood = getSelectedWood();

        const total = currentTotal();

        totalPriceElement.textContent = formatPrice(total);
        summarySize.textContent = selectedSize ? selectedSize.dataset.label : '180 cm';
        summaryInside.textContent = selectedInside ? selectedInside.dataset.label : 'Balta';
        summaryWood.textContent = selectedWood ? selectedWood.dataset.label : 'Šviesi ruda';

        previewInsideBadge.textContent = `Vidus: ${selectedInside ? selectedInside.dataset.label : 'Balta'}`;
        previewWoodBadge.textContent = `Mediena: ${selectedWood ? selectedWood.dataset.label : 'Šviesi ruda'}`;
        previewSizeBadge.textContent = `Dydis: ${selectedSize ? selectedSize.dataset.label : '180 cm'}`;

        if (selectedInside) {
            previewInsideBadge.style.backgroundColor = selectedInside.dataset.hex;
            previewInsideBadge.style.color = selectedInside.dataset.textColor || '#ffffff';
        } else {
            previewInsideBadge.style.backgroundColor = '#1c1917';
            previewInsideBadge.style.color = '#ffffff';
        }

        if (selectedWood) {
            previewWoodBadge.style.backgroundColor = selectedWood.dataset.hex;
            previewWoodBadge.style.color = selectedWood.dataset.textColor || '#ffffff';
        } else {
            previewWoodBadge.style.backgroundColor = '#8b6a4f';
            previewWoodBadge.style.color = '#ffffff';
        }
    }

    function refreshStates() {
        setActiveCard(sizeCards, getSelectedSize());
        setActiveCard(insideCards, getSelectedInside());
        setActiveCard(woodCards, getSelectedWood());
    }

    function updateBuilder() {
        refreshStates();
        updatePreview();
        updateSummary();
    }

    async function api(url, method = 'GET', body = null) {
        const headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
        };

        const options = {
            method,
            headers,
            credentials: 'same-origin',
        };

        if (body !== null) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(body);
        }

        const response = await fetch(url, options);

        let data = null;
        try {
            data = await response.json();
        } catch (_) {}

        if (!response.ok) {
            const message = data?.message || data?.error || `Klaida (${response.status})`;
            throw new Error(message);
        }

        return data;
    }


    // si funkcija issiuncia pasirinkta kubila i backend, kad jis atsidurtu krepselyje.
    async function addIndividualTubToCart() {
        const selectedSize = getSelectedSize();
        const selectedInside = getSelectedInside();
        const selectedWood = getSelectedWood();
        if (!selectedSize || !selectedInside || !selectedWood) {
            showBuilderMsg('Pasirinkite visas kubilo parinktis.', false);
            return;
        }

        addButton.disabled = true;
        addButton.classList.add('opacity-60', 'cursor-not-allowed');
        addButton.textContent = 'Dedama...';

        try {
            // cia pasirinkimai keliauja i /api/v1/cart/custom-tub.
            await api('/api/v1/cart/custom-tub', 'POST', {
                size_key: selectedSize.value,
                inside_key: selectedInside.value,
                wood_key: selectedWood.value,
                qty: 1,
            });

            if (window.refreshCartBadge) {
                window.refreshCartBadge();
            }

            if (window.showToast) {
                window.showToast('Individualus kubilas įdėtas į krepšelį.', 'ok');
            }

            showBuilderMsg('Individualus kubilas įdėtas į krepšelį.', true);

            addButton.textContent = 'Įdėta į krepšelį';
        } catch (error) {
            showBuilderMsg(error.message || 'Nepavyko įdėti kubilo į krepšelį.', false);
        } finally {
            addButton.disabled = false;
            addButton.classList.remove('opacity-60', 'cursor-not-allowed');
            addButton.textContent = 'Į krepšelį';
        }
    }


    // pakeitus pasirinkima iskart atnaujinama kaina, tekstai ir nuotrauka.
    sizeOptions.forEach(option => {
        option.addEventListener('change', updateBuilder);
    });

    insideOptions.forEach(option => {
        option.addEventListener('change', updateBuilder);
    });

    woodOptions.forEach(option => {
        option.addEventListener('change', updateBuilder);
    });

    if (addButton) {
        addButton.addEventListener('click', addIndividualTubToCart);
    }

    updateBuilder();
});
</script>
@endsection
