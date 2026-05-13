@extends('layouts.app')

@section('title', 'Prekės – Kubiliuks')

@section('content')
<section class="py-8 sm:py-10 lg:py-12 bg-stone-50">
    <div class="max-w-6xl mx-auto px-4 lg:px-0">

        <div class="flex flex-col gap-4 sm:gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-stone-900">Prekės</h1>
            </div>

            <div class="w-full sm:w-[260px] lg:w-[260px] shrink-0">
                <label for="pSort" class="block text-sm font-semibold text-stone-700 mb-2">
                    Rūšiavimas
                </label>

                <select
                    id="pSort"
                    onchange="window.setSort(this.value)"
                    class="w-full h-12 px-4 rounded-2xl border border-stone-200 bg-white shadow-sm outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-300 text-stone-900"
                >
                    <option value="new">Naujausi</option>
                    <option value="price_asc">Kaina: žemiausia</option>
                    <option value="price_desc">Kaina: aukščiausia</option>
                    <option value="name_asc">Pavadinimas</option>
                </select>
            </div>
        </div>

        <div class="mt-5">
            <div class="text-sm font-semibold text-stone-700 mb-3">Kategorijos</div>
            <div id="pCats" class="flex flex-wrap gap-2 relative z-10"></div>
        </div>

        <div class="mt-8 sm:mt-10">
            <div id="productsGrid" class="grid gap-5 sm:gap-6 md:grid-cols-2 xl:grid-cols-3"></div>

            <div id="productsEmpty" class="hidden">
                <div class="rounded-3xl border border-stone-200 bg-white py-14 px-6 text-center text-stone-600">
                    Nieko nerasta.
                </div>
            </div>

            <div id="productsLoading">
                <div class="rounded-3xl border border-stone-200 bg-white py-14 px-6 text-center text-stone-600">
                    Kraunama...
                </div>
            </div>
        </div>

    </div>
</section>

<script>
    function getCsrfToken() {
        const el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function resolveImageUrl(p) {
        if (p.image_url) return p.image_url;

        if (p.image) {
            if (p.image.startsWith('/storage/')) return p.image;
            if (p.image.startsWith('http')) return p.image;
            return '/storage/' + p.image.replace(/^\/+/, '');
        }

        return '/images/no-image.png';
    }

    function num(x, fallback = 0) {
        const n = Number(x);
        return Number.isFinite(n) ? n : fallback;
    }

    function formatPriceEUR(value) {
        const v = num(value, 0);
        return v.toLocaleString('lt-LT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' €';
    }

    async function apiAddToCart(productId, qty = 1) {
        try {
            const res = await fetch('/api/v1/cart/items', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, qty: qty })
            });

            if (res.status === 201 || res.status === 200) {
                if (window.refreshCartBadge) {
                    await window.refreshCartBadge();
                }

                if (window.showToast) {
                    window.showToast('Prekė įdėta į krepšelį.', 'ok');
                }

                return;
            }

            const json = await res.json().catch(() => null);
            const msg = json?.message || `Nepavyko įdėti į krepšelį. (HTTP ${res.status})`;

            if (window.showToast) {
                window.showToast(msg, 'error');
            } else {
                alert(msg);
            }
        } catch (e) {
            const msg = 'Klaida. Bandykite dar kartą.';

            if (window.showToast) {
                window.showToast(msg, 'error');
            } else {
                alert(msg);
            }
        }
    }

    const CATEGORY_LIST = [
        { key: 'all', label: 'Visi produktai' },
        { key: 'Kubilai', label: 'Kubilai' },
        { key: 'Nameliai', label: 'Nameliai' },
        { key: 'Kiti gaminiai', label: 'Kiti gaminiai' },
    ];

    function normalizeCategoryName(name) {
        const value = String(name || '').trim();

        if (value === 'Namelių priedai') return 'Nameliai';
        if (value === 'Pirties įranga') return 'Kiti gaminiai';

        return value;
    }

    function getCategoryNameFromProduct(p) {
        if (p.category && p.category.name) return normalizeCategoryName(p.category.name);
        if (p.category_name) return normalizeCategoryName(p.category_name);
        if (p.categoryName) return normalizeCategoryName(p.categoryName);
        return '';
    }

    let ALL = [];
    let ACTIVE_CAT = 'all';
    let SORT = 'new';

    function pillClass(active) {
        return active
            ? 'bg-emerald-700 border-emerald-700 text-white shadow-sm'
            : 'bg-white border-stone-200 text-stone-700 hover:border-stone-300';
    }

    function renderPills() {
        const wrap = document.getElementById('pCats');

        wrap.innerHTML = CATEGORY_LIST.map(c => {
            const active = (ACTIVE_CAT === c.key);

            return `
                <button
                    type="button"
                    onclick="window.setCat('${escapeHtml(c.key)}')"
                    class="px-4 sm:px-5 h-10 sm:h-11 rounded-full text-sm font-semibold border transition whitespace-nowrap ${pillClass(active)}"
                    style="pointer-events:auto"
                >${escapeHtml(c.label)}</button>
            `;
        }).join('');
    }

    function applyFilters(items) {
        if (ACTIVE_CAT === 'all') return items;

        return items.filter(p => {
            const cat = getCategoryNameFromProduct(p);
            return cat === ACTIVE_CAT;
        });
    }

    function applySort(items) {
        const arr = [...items];

        if (SORT === 'price_asc') {
            arr.sort((a, b) => num(a.price) - num(b.price));
        } else if (SORT === 'price_desc') {
            arr.sort((a, b) => num(b.price) - num(a.price));
        } else if (SORT === 'name_asc') {
            arr.sort((a, b) => String(a.name ?? '').localeCompare(String(b.name ?? ''), 'lt'));
        } else {
            arr.sort((a, b) => {
                const da = a.created_at ? Date.parse(a.created_at) : 0;
                const db = b.created_at ? Date.parse(b.created_at) : 0;

                if (db !== da) return db - da;

                return num(b.id) - num(a.id);
            });
        }

        return arr;
    }

    function productCard(p) {
        const img = resolveImageUrl(p);
        const name = escapeHtml(p.name);
        const price = formatPriceEUR(p.price);
        const slug = encodeURIComponent(p.slug ?? '');
        const stock = num(p.stock, 0);
        const isSoldOut = stock <= 0;
        const categoryName = escapeHtml(getCategoryNameFromProduct(p) || 'Kiti gaminiai');

        return `
            <div class="bg-white rounded-3xl border border-stone-200 overflow-hidden shadow-sm hover:shadow-lg transition flex flex-col h-full">
                <a href="/prekes/${slug}" class="block">
                    <div class="aspect-[4/3] bg-stone-100 overflow-hidden relative">
                        ${isSoldOut ? `
                            <div class="absolute top-3 left-3 z-10 inline-flex items-center rounded-full bg-rose-100 text-rose-700 border border-rose-200 px-3 py-1 text-xs font-bold">
                                Išparduota
                            </div>
                        ` : ''}

                        <img
                            src="${img}"
                            alt="${name}"
                            class="w-full h-full object-cover"
                            onerror="this.onerror=null;this.src='/images/no-image.png';"
                            loading="lazy"
                        />
                    </div>
                </a>

                <div class="p-4 sm:p-5 flex flex-col grow">
                    <div class="text-xs font-semibold uppercase tracking-wide text-stone-500">
                        ${categoryName}
                    </div>

                    <a href="/prekes/${slug}" class="block mt-2">
                        <div class="font-extrabold text-stone-900 text-lg leading-snug line-clamp-2 min-h-[3.5rem]">
                            ${name}
                        </div>
                    </a>

                    <div class="mt-4 text-emerald-800 font-extrabold text-2xl">
                        ${price}
                    </div>

                    <div class="mt-5">
                        ${
                            isSoldOut
                                ? `
                                    <button
                                        type="button"
                                        class="w-full h-12 rounded-full bg-stone-300 text-white text-sm font-semibold cursor-not-allowed"
                                        disabled
                                    >
                                        Išparduota
                                    </button>
                                  `
                                : `
                                    <button
                                        type="button"
                                        class="w-full h-12 rounded-full bg-emerald-700 text-white text-sm font-semibold hover:bg-emerald-800 transition"
                                        onclick="apiAddToCart(${Number(p.id)}, 1)"
                                    >
                                        Į krepšelį
                                    </button>
                                  `
                        }
                    </div>
                </div>
            </div>
        `;
    }

    function renderProducts() {
        const grid = document.getElementById('productsGrid');
        const empty = document.getElementById('productsEmpty');

        const filtered = applyFilters(ALL);
        const sorted = applySort(filtered);

        if (!sorted.length) {
            grid.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');
        grid.innerHTML = sorted.map(productCard).join('');
    }

    async function loadProducts() {
        const loading = document.getElementById('productsLoading');
        const empty = document.getElementById('productsEmpty');

        try {
            const res = await fetch('/api/v1/products', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const json = await res.json().catch(() => ({}));

            loading.classList.add('hidden');

            ALL = Array.isArray(json.data) ? json.data : [];

            if (!ALL.length) {
                empty.classList.remove('hidden');
                return;
            }

            renderPills();
            renderProducts();
        } catch (e) {
            loading.classList.add('hidden');
            empty.classList.remove('hidden');

            empty.innerHTML = `
                <div class="rounded-3xl border border-red-200 bg-red-50 py-14 px-6 text-center text-red-800">
                    Nepavyko užkrauti prekių. Bandykite dar kartą vėliau.
                </div>
            `;
        }
    }

    window.setCat = function(catKey) {
        ACTIVE_CAT = catKey || 'all';
        renderPills();
        renderProducts();
    };

    window.setSort = function(sortKey) {
        SORT = sortKey || 'new';
        renderProducts();
    };

    document.addEventListener('DOMContentLoaded', () => {
        loadProducts();

        if (window.refreshCartBadge) {
            window.refreshCartBadge();
        }
    });
</script>
@endsection