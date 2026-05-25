@extends('layouts.app')

@section('title', 'Krepšelis – Kubiliuks')

@section('content')
<section class="py-8 sm:py-10 lg:py-12 bg-stone-50">
    <div class="max-w-6xl mx-auto px-4 lg:px-0">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-3xl font-extrabold text-stone-900">Krepšelis</h1>
                <p class="mt-2 text-sm text-stone-500">
                    Peržiūrėkite pasirinktas prekes prieš pateikdami užsakymą.
                </p>
            </div>

            <a href="{{ route('prekes') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                Tęsti pirkimą
            </a>
        </div>

        <div id="cartAlert" class="hidden mt-6 rounded-2xl border px-4 py-3 text-sm"></div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-black/10 shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-5 py-4 border-b border-black/10">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-stone-900">Prekės</div>

                            <button id="btnReload" class="text-sm font-semibold text-stone-700 hover:text-stone-900">
                                Atnaujinti
                            </button>
                        </div>
                    </div>

                    <div id="cartItems" class="divide-y divide-black/10"></div>

                    <div id="cartEmpty" class="hidden px-5 py-10 text-center">
                        <div class="text-stone-900 font-semibold">Krepšelis tuščias</div>
                        <div class="mt-2 text-sm text-stone-500">
                            Įsidėkite bent vieną prekę, kad galėtumėte tęsti užsakymą.
                        </div>

                        <a href="{{ route('prekes') }}"
                           class="inline-flex mt-4 items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800">
                            Rodyti prekes
                        </a>
                    </div>

                    <div id="cartLoading" class="px-5 py-10 text-center text-stone-600 text-sm">
                        Kraunama...
                    </div>
                </div>
            </div>

            <aside>
                <div class="bg-white rounded-2xl border border-black/10 shadow-sm p-5 lg:sticky lg:top-24">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-semibold text-stone-900">Suvestinė</div>
                        <div id="cartCount" class="text-xs font-semibold text-stone-600">0 prek.</div>
                    </div>

                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-stone-600">Bendra suma</span>
                            <span id="cartTotal" class="text-lg font-extrabold text-stone-900">0,00 €</span>
                        </div>
                    </div>

                    <div class="mt-5">
                        <button
                            id="goCheckout"
                            type="button"
                            class="w-full inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800 transition"
                        >
                            Tęsti
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
(() => {
    const apiBase = '/api/v1/cart';

    const elItems = document.getElementById('cartItems');
    const elEmpty = document.getElementById('cartEmpty');
    const elLoading = document.getElementById('cartLoading');
    const elTotal = document.getElementById('cartTotal');
    const elCount = document.getElementById('cartCount');
    const elAlert = document.getElementById('cartAlert');
    const btnReload = document.getElementById('btnReload');
    const btnCheckout = document.getElementById('goCheckout');

    let isBusy = false;
    let currentItemsCount = 0;

    const money = (v) => new Intl.NumberFormat('lt-LT', {
        style: 'currency',
        currency: 'EUR'
    }).format(Number(v || 0));

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showAlert(text, type = 'error') {
        if (!elAlert) return;

        elAlert.classList.remove('hidden');
        elAlert.textContent = text;

        if (window.showToast) {
            const toastType = type === 'ok' ? 'ok' : type === 'warn' ? 'warn' : 'error';
            window.showToast(text, toastType);
        }

        elAlert.classList.remove(
            'border-red-200', 'bg-red-50', 'text-red-800',
            'border-emerald-200', 'bg-emerald-50', 'text-emerald-800',
            'border-amber-200', 'bg-amber-50', 'text-amber-800'
        );

        if (type === 'ok') {
            elAlert.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
        } else if (type === 'warn') {
            elAlert.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-800');
        } else {
            elAlert.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        }
    }

    function hideAlert() {
        if (!elAlert) return;
        elAlert.classList.add('hidden');
        elAlert.textContent = '';
    }

    function updateCheckoutState() {
        if (!btnCheckout) return;

        if (currentItemsCount < 1) {
            btnCheckout.disabled = true;
            btnCheckout.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            btnCheckout.disabled = false;
            btnCheckout.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    async function api(url, method = 'GET', body = null) {
        const headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
        };

        const opts = {
            method,
            headers,
            credentials: 'same-origin'
        };

        if (body !== null) {
            headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }

        const res = await fetch(url, opts);

        let data = null;
        try {
            data = await res.json();
        } catch (_) {}

        if (!res.ok) {
            const msg = data?.message || data?.error || `Klaida (${res.status})`;
            throw new Error(msg);
        }

        return data;
    }

    function getRoot(data) {
        return (data && typeof data === 'object' && data.data && typeof data.data === 'object')
            ? data.data
            : data;
    }

    function normalizeItems(root) {
        const items = Array.isArray(root?.items) ? root.items : [];

        return items.map((it) => {
            const product = it.product || it.preke || null;

            const endpointId =
                it.id ??
                it.item_id ??
                it.cart_item_id ??
                it.product_id ??
                product?.id ??
                null;

            const qty = Number(it.quantity ?? it.qty ?? it.kiekis ?? 1);

            const name =
                it.name ??
                it.product_name ??
                product?.name ??
                product?.title ??
                'Prekė';

            const price =
                it.price ??
                it.unit_price ??
                product?.price ??
                0;

            const image =
                it.image_url ??
                it.image ??
                product?.image_url ??
                product?.image ??
                null;

            const slug =
                it.slug ??
                product?.slug ??
                null;

            return {
                endpointId,
                productId: product?.id ?? it.product_id ?? null,
                name,
                qty,
                price: Number(price || 0),
                image,
                slug,
                subtotal: Number(price || 0) * qty
            };
        });
    }

    function imageUrl(path) {
        if (!path) return '';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        if (path.startsWith('/storage/') || path.startsWith('/images/')) return path;
        if (path.startsWith('storage/')) return `/${path}`;
        return `/storage/${path}`;
    }

    function productUrl(item) {
        if (item.slug) {
            return `/prekes/${item.slug}`;
        }

        return '#';
    }

    // cia viena krepselio preke paverciama HTML kortele.
    function renderItem(item) {
        const img = item.image
            ? `<img src="${imageUrl(item.image)}" alt="${item.name}" class="h-20 w-20 rounded-xl object-cover border border-black/10">`
            : `<div class="h-20 w-20 rounded-xl border border-dashed border-black/10 bg-stone-100"></div>`;

        const href = productUrl(item);

        return `
            <div class="px-4 sm:px-5 py-4">
                <div class="flex items-start gap-4">
                    <a href="${href}" class="shrink-0">
                        ${img}
                    </a>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <a href="${href}" class="font-semibold text-stone-900 hover:text-emerald-700 line-clamp-2">
                                    ${item.name}
                                </a>

                                <div class="mt-1 text-sm text-stone-500">
                                    ${money(item.price)}
                                </div>
                            </div>

                            <button
                                type="button"
                                data-remove="${item.endpointId}"
                                class="text-sm font-medium text-stone-500 hover:text-red-600"
                            >
                                Šalinti
                            </button>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                            <div class="inline-flex items-center rounded-xl border border-black/10 overflow-hidden">
                                <button
                                    type="button"
                                    data-minus="${item.endpointId}"
                                    class="h-10 w-10 text-stone-700 hover:bg-stone-100"
                                >−</button>

                                <div class="h-10 min-w-10 px-4 flex items-center justify-center text-sm font-semibold text-stone-900 border-x border-black/10">
                                    ${item.qty}
                                </div>

                                <button
                                    type="button"
                                    data-plus="${item.endpointId}"
                                    class="h-10 w-10 text-stone-700 hover:bg-stone-100"
                                >+</button>
                            </div>

                            <div class="text-sm font-bold text-stone-900">
                                ${money(item.subtotal)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // cia is API paimamas session krepselis ir atvaizduojamas puslapyje.
    async function loadCart(showReloadToast = false) {
        hideAlert();
        elLoading.classList.remove('hidden');
        elEmpty.classList.add('hidden');
        elItems.innerHTML = '';

        try {
            // GET /api/v1/cart grazina prekes, kiekius ir bendra suma.
            const data = await api(apiBase);
            const root = getRoot(data);
            const items = normalizeItems(root);

            currentItemsCount = items.reduce((sum, item) => sum + item.qty, 0);
            elCount.textContent = `${currentItemsCount} prek.`;

            const total = items.reduce((sum, item) => sum + item.subtotal, 0);
            elTotal.textContent = money(total);

            updateCheckoutState();

            if (items.length < 1) {
                elEmpty.classList.remove('hidden');
            } else {
                // cia prekes realiai atsiranda krepselio puslapio HTML'e.
                elItems.innerHTML = items.map(renderItem).join('');
            }

            if (showReloadToast && window.showToast) {
                window.showToast('Krepšelis atnaujintas.', 'ok');
            }

            if (window.refreshCartBadge) {
                window.refreshCartBadge();
            }
        } catch (error) {
            showAlert(error.message || 'Nepavyko užkrauti krepšelio.');
        } finally {
            elLoading.classList.add('hidden');
        }
    }

    // kiekio +/- pakeitimai siunciami i backend ir po to krepselis perkraunamas.
    async function updateItem(itemId, qty) {
        if (isBusy) return;
        isBusy = true;

        try {
            await api(`${apiBase}/items/${itemId}`, 'PATCH', { qty });
            await loadCart();

            if (window.showToast) {
                window.showToast('Krepšelis atnaujintas.', 'ok');
            }
        } catch (error) {
            showAlert(error.message || 'Nepavyko atnaujinti kiekio.');
        } finally {
            isBusy = false;
        }
    }

    async function removeItem(itemId) {
        if (isBusy) return;
        isBusy = true;

        try {
            await api(`${apiBase}/items/${itemId}`, 'DELETE');
            await loadCart();

            if (window.showToast) {
                window.showToast('Prekė pašalinta iš krepšelio.', 'ok');
            }
        } catch (error) {
            showAlert(error.message || 'Nepavyko pašalinti prekės.');
        } finally {
            isBusy = false;
        }
    }

    document.addEventListener('click', async (e) => {
        const plusBtn = e.target.closest('[data-plus]');
        const minusBtn = e.target.closest('[data-minus]');
        const removeBtn = e.target.closest('[data-remove]');

        if (plusBtn) {
            const itemId = plusBtn.getAttribute('data-plus');
            const qtyBox = plusBtn.parentElement.querySelector('.border-x');
            const currentQty = Number(qtyBox?.textContent || 1);
            await updateItem(itemId, currentQty + 1);
            return;
        }

        if (minusBtn) {
            const itemId = minusBtn.getAttribute('data-minus');
            const qtyBox = minusBtn.parentElement.querySelector('.border-x');
            const currentQty = Number(qtyBox?.textContent || 1);

            if (currentQty <= 1) {
                await removeItem(itemId);
                return;
            }

            await updateItem(itemId, currentQty - 1);
            return;
        }

        if (removeBtn) {
            const itemId = removeBtn.getAttribute('data-remove');
            await removeItem(itemId);
        }
    });

    if (btnReload) {
        btnReload.addEventListener('click', () => loadCart(true));
    }

    if (btnCheckout) {
        btnCheckout.addEventListener('click', () => {
            if (currentItemsCount < 1) {
                showAlert('Krepšelis tuščias.', 'warn');
                return;
            }

            window.location.href = '{{ route('checkout') }}';
        });
    }

    loadCart();
})();
</script>
@endsection