@extends('layouts.app')

@section('title', $product->name)

@section('content')
@php
    $imgUrl = function ($path) {
        if (!$path) return null;

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    };

    $galleryImages = $product->gallery_images ?? [];

    if (is_string($galleryImages)) {
        $galleryImages = json_decode($galleryImages, true) ?: [];
    }

    $images = array_values(array_filter(array_merge([
        $imgUrl($product->image),
        $imgUrl($product->image_2 ?? null),
        $imgUrl($product->image_3 ?? null),
    ], array_map($imgUrl, $galleryImages ?: []))));

    $placeholder = asset('images/no-image.png');

    $firstProductImg = function ($p) use ($imgUrl, $placeholder) {
        $src = $imgUrl($p->image ?? null)
            ?: $imgUrl($p->image_2 ?? null)
            ?: $imgUrl($p->image_3 ?? null);

        return $src ?: $placeholder;
    };

    $categoryName = $product->category?->name ?? 'Kategorija';

    if ($categoryName === 'Namelių priedai') {
        $categoryName = 'Nameliai';
    }

    if ($categoryName === 'Pirties įranga') {
        $categoryName = 'Kiti gaminiai';
    }

    $priceFormatted = number_format((float) $product->price, 2, ',', ' ') . ' €';
@endphp

<section class="py-8 sm:py-10 bg-stone-50">
    <div class="max-w-6xl mx-auto px-4 lg:px-0">

        <div class="mb-6">
            <a href="{{ url('/prekes') }}" class="text-sm text-stone-600 hover:text-stone-900">
                ← Grįžti į prekes
            </a>
        </div>

        <div class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr] items-start">
            <div class="space-y-4">
                <div class="bg-white rounded-3xl border border-stone-200 overflow-hidden">
                    <button type="button" class="w-full block" id="mainImageBtn" aria-label="Atidaryti nuotrauką">
                        <img
                            id="mainImage"
                            src="{{ $images[0] ?? $placeholder }}"
                            alt="{{ $product->name }}"
                            class="w-full h-[320px] sm:h-[460px] object-cover"
                            onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                        >
                    </button>
                </div>

                @if(count($images) > 1)
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                        @foreach($images as $i => $src)
                            <button
                                type="button"
                                class="bg-white rounded-2xl border border-stone-200 overflow-hidden hover:shadow-md transition thumb-btn"
                                data-index="{{ $i }}"
                                aria-label="Pasirinkti nuotrauką {{ $i + 1 }}"
                            >
                                <img
                                    src="{{ $src }}"
                                    alt="{{ $product->name }} {{ $i + 1 }}"
                                    class="w-full h-20 sm:h-24 object-cover"
                                    onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                                >
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-5">
                <div>
                    <div class="text-sm text-stone-500">
                        {{ $categoryName }}
                    </div>

                    <h1 class="mt-2 text-4xl sm:text-5xl font-extrabold tracking-tight text-stone-900">
                        {{ $product->name }}
                    </h1>
                </div>

                <div class="bg-white rounded-3xl border border-stone-200 p-5 sm:p-6">
                    <div class="text-4xl sm:text-5xl font-extrabold tracking-tight text-stone-900">
                        {{ $priceFormatted }}
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="flex items-center gap-3">
                            @if($product->stock > 0)
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-emerald-700 font-semibold">
                                    Yra sandėlyje ({{ $product->stock }} vnt.)
                                </span>
                            @else
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                                <span class="text-red-700 font-semibold">
                                    Išparduota
                                </span>
                            @endif
                        </div>

                        @if($product->production_time)
                            <div class="text-sm text-stone-500">
                                Pagaminimo terminas:
                                <span class="font-semibold text-stone-800">{{ $product->production_time }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 pt-6 border-t border-stone-200">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="inline-flex items-center rounded-full border border-stone-200 bg-white overflow-hidden w-full sm:w-auto">
                                <button
                                    type="button"
                                    id="qtyMinusBtn"
                                    title="Sumažinti kiekį"
                                    class="px-5 py-3 text-xl text-stone-700 disabled:opacity-40 disabled:cursor-not-allowed"
                                    onclick="qtyMinus()"
                                    @disabled($product->stock <= 0)
                                >
                                    −
                                </button>

                                <div class="px-5 py-3 font-semibold text-stone-900 min-w-[64px] text-center" id="qtyVal">1</div>

                                <button
                                    type="button"
                                    id="qtyPlusBtn"
                                    title="Padidinti kiekį"
                                    class="px-5 py-3 text-xl text-stone-700 disabled:opacity-40 disabled:cursor-not-allowed"
                                    onclick="qtyPlus()"
                                    @disabled($product->stock <= 0)
                                >
                                    +
                                </button>
                            </div>

                            @if($product->stock > 0)
                                <button
                                    type="button"
                                    class="w-full sm:flex-1 px-6 py-4 rounded-full bg-emerald-700 text-white font-semibold hover:bg-emerald-800 transition"
                                    onclick="addToCart({{ $product->id }})"
                                >
                                    Į krepšelį
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="w-full sm:flex-1 px-6 py-4 rounded-full bg-stone-300 text-white font-semibold cursor-not-allowed"
                                    disabled
                                >
                                    Išparduota
                                </button>
                            @endif
                        </div>

                        <div id="qtyHint" class="mt-4 text-sm text-stone-500 min-h-[20px]"></div>
                    </div>
                </div>
            </div>
        </div>

        @if($product->description)
            <div class="mt-10 bg-white rounded-3xl border border-stone-200 p-6 sm:p-8">
                <h2 class="text-2xl font-extrabold text-stone-900">Aprašymas</h2>

                <div class="mt-4 text-stone-700 leading-8 text-base">
                    {{ $product->description }}
                </div>
            </div>
        @endif

        @if(!empty($similarProducts) && $similarProducts->count())
            <div class="mt-12">
                <h2 class="text-2xl font-extrabold text-stone-900">Panašūs produktai</h2>

                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($similarProducts as $sp)
                        @php
                            $spImg = $firstProductImg($sp);
                        @endphp

                        <div class="bg-white rounded-3xl border border-stone-200 overflow-hidden hover:shadow-lg transition">
                            <a href="{{ url('/prekes/' . $sp->slug) }}" class="block">
                                <div class="aspect-[4/3] bg-stone-100 overflow-hidden">
                                    <img
                                        src="{{ $spImg }}"
                                        alt="{{ $sp->name }}"
                                        class="w-full h-full object-cover"
                                        onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                                    />
                                </div>

                                <div class="p-4">
                                    <div class="font-semibold text-stone-900 line-clamp-2">
                                        {{ $sp->name }}
                                    </div>

                                    <div class="mt-2 font-extrabold text-emerald-800">
                                        {{ number_format((float) $sp->price, 2, ',', ' ') }} €
                                    </div>
                                </div>
                            </a>

                            <div class="px-4 pb-4">
                                @if(($sp->stock ?? 0) > 0)
                                    <button
                                        type="button"
                                        class="w-full rounded-full py-3 font-semibold text-white bg-emerald-700 hover:bg-emerald-800 transition"
                                        onclick="addToCartQuick({{ $sp->id }})"
                                    >
                                        Į krepšelį
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        class="w-full rounded-full py-3 font-semibold text-white bg-stone-300 cursor-not-allowed"
                                        disabled
                                    >
                                        Išparduota
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>

<div id="lb" class="lb hidden" aria-hidden="true">
    <div class="lb__backdrop" data-lb-close></div>

    <div class="lb__content" role="dialog" aria-modal="true">
        <button type="button" class="lb__close" data-lb-close aria-label="Uždaryti">×</button>

        <button type="button" class="lb__nav lb__nav--left" id="lbPrev" aria-label="Ankstesnė">‹</button>
        <img id="lbImage" class="lb__image" src="" alt="">
        <button type="button" class="lb__nav lb__nav--right" id="lbNext" aria-label="Kita">›</button>
    </div>
</div>

<style>
    .lb.hidden { display:none; }
    .lb {
        position: fixed;
        inset: 0;
        z-index: 9999;
    }
    .lb__backdrop {
        position:absolute;
        inset:0;
        background:rgba(0,0,0,.75);
    }
    .lb__content {
        position:relative;
        z-index:2;
        width:min(92vw, 1100px);
        height:min(88vh, 800px);
        margin:4vh auto;
        display:flex;
        align-items:center;
        justify-content:center;
    }
    .lb__image {
        max-width:100%;
        max-height:100%;
        object-fit:contain;
        border-radius:18px;
        box-shadow:0 20px 60px rgba(0,0,0,.35);
        background:#fff;
    }
    .lb__close,
    .lb__nav {
        position:absolute;
        z-index:3;
        border:none;
        background:rgba(255,255,255,.9);
        color:#111827;
        width:46px;
        height:46px;
        border-radius:999px;
        font-size:28px;
        line-height:1;
        cursor:pointer;
        display:flex;
        align-items:center;
        justify-content:center;
        box-shadow:0 10px 30px rgba(0,0,0,.15);
    }
    .lb__close {
        top:16px;
        right:16px;
    }
    .lb__nav--left {
        left:16px;
    }
    .lb__nav--right {
        right:16px;
    }
</style>

<script>
    const productStock = {{ (int) $product->stock }};
    let qty = 1;

    function updateQtyUI() {
        const qtyVal = document.getElementById('qtyVal');
        const minusBtn = document.getElementById('qtyMinusBtn');
        const plusBtn = document.getElementById('qtyPlusBtn');
        const qtyHint = document.getElementById('qtyHint');

        if (qtyVal) qtyVal.textContent = qty;

        if (minusBtn) {
            minusBtn.disabled = qty <= 1 || productStock <= 0;
        }

        if (plusBtn) {
            plusBtn.disabled = qty >= productStock || productStock <= 0;
        }

        if (qtyHint) {
            if (productStock > 0) {
                qtyHint.textContent = 'Galite pasirinkti iki ' + productStock + ' vnt.';
            } else {
                qtyHint.textContent = '';
            }
        }
    }

    function qtyMinus() {
        if (qty > 1) {
            qty--;
            updateQtyUI();
        }
    }

    function qtyPlus() {
        if (qty < productStock) {
            qty++;
            updateQtyUI();
        }
    }

    async function addToCart(productId) {
        try {
            const response = await fetch('/api/v1/cart/items', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: qty
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Nepavyko įdėti prekės į krepšelį.');
            }

            if (typeof window.showToast === 'function') {
                window.showToast('Prekė įdėta į krepšelį', 'ok');
            }

            if (typeof window.refreshCartBadge === 'function') {
                window.refreshCartBadge();
            }
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error.message || 'Nepavyko įdėti prekės į krepšelį.', 'error');
            } else {
                alert(error.message);
            }
        }
    }

    async function addToCartQuick(productId) {
        try {
            const response = await fetch('/api/v1/cart/items', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Nepavyko įdėti prekės į krepšelį.');
            }

            if (typeof window.showToast === 'function') {
                window.showToast('Prekė įdėta į krepšelį', 'ok');
            }

            if (typeof window.refreshCartBadge === 'function') {
                window.refreshCartBadge();
            }
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error.message || 'Nepavyko įdėti prekės į krepšelį.', 'error');
            } else {
                alert(error.message);
            }
        }
    }

    const galleryImages = @json($images);
    let galleryIndex = 0;

    const mainImage = document.getElementById('mainImage');
    const thumbButtons = Array.from(document.querySelectorAll('.thumb-btn'));
    const mainImageBtn = document.getElementById('mainImageBtn');

    function showImage(index) {
        if (!galleryImages.length) return;

        if (index < 0) index = galleryImages.length - 1;
        if (index >= galleryImages.length) index = 0;

        galleryIndex = index;

        if (mainImage) {
            mainImage.src = galleryImages[galleryIndex];
        }

        thumbButtons.forEach((btn, i) => {
            if (i === galleryIndex) {
                btn.classList.add('ring-2', 'ring-emerald-600');
            } else {
                btn.classList.remove('ring-2', 'ring-emerald-600');
            }
        });
    }

    thumbButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const index = Number(btn.dataset.index || 0);
            showImage(index);
        });
    });

    const lb = document.getElementById('lb');
    const lbImage = document.getElementById('lbImage');
    const lbPrev = document.getElementById('lbPrev');
    const lbNext = document.getElementById('lbNext');
    const lbCloseButtons = document.querySelectorAll('[data-lb-close]');

    function openLightbox(index = 0) {
        if (!galleryImages.length || !lb || !lbImage) return;

        showImage(index);
        lbImage.src = galleryImages[galleryIndex];
        lb.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if (!lb) return;

        lb.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function lightboxPrev() {
        if (!galleryImages.length) return;
        galleryIndex = galleryIndex <= 0 ? galleryImages.length - 1 : galleryIndex - 1;
        showImage(galleryIndex);
        if (lbImage) lbImage.src = galleryImages[galleryIndex];
    }

    function lightboxNext() {
        if (!galleryImages.length) return;
        galleryIndex = galleryIndex >= galleryImages.length - 1 ? 0 : galleryIndex + 1;
        showImage(galleryIndex);
        if (lbImage) lbImage.src = galleryImages[galleryIndex];
    }

    if (mainImageBtn) {
        mainImageBtn.addEventListener('click', () => openLightbox(galleryIndex));
    }

    if (lbPrev) {
        lbPrev.addEventListener('click', lightboxPrev);
    }

    if (lbNext) {
        lbNext.addEventListener('click', lightboxNext);
    }

    lbCloseButtons.forEach((btn) => {
        btn.addEventListener('click', closeLightbox);
    });

    document.addEventListener('keydown', (e) => {
        if (!lb || lb.classList.contains('hidden')) return;

        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') lightboxPrev();
        if (e.key === 'ArrowRight') lightboxNext();
    });

    showImage(0);
    updateQtyUI();
</script>
@endsection