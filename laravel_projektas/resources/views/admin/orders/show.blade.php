<x-admin-layout :title="'Užsakymas #' . $orderId">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-stone-900">Užsakymas #{{ $orderId }}</h1>
            <p class="text-slate-500 mt-1">Administratoriaus peržiūra ir užsakymo būsenos valdymas.</p>
        </div>

        <a href="{{ route('admin.orders.index') }}"
           class="px-4 py-2 rounded-xl border border-black/10 bg-white text-sm font-semibold hover:bg-stone-50">
            ← Atgal į užsakymus
        </a>
    </div>

    <div id="msg" class="hidden mb-4 px-4 py-3 rounded-2xl border text-sm"></div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="rounded-3xl border border-black/10 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-stone-900">Pagrindinė informacija</h2>
                        <p class="text-sm text-slate-500">Kliento ir pristatymo duomenys.</p>
                    </div>

                    <span id="order-status-badge"
                          class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border">
                        —
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-black/10 p-4">
                        <p class="text-sm text-slate-500 mb-1">Klientas</p>
                        <p id="customer-name" class="font-semibold text-stone-900">—</p>
                    </div>

                    <div class="rounded-2xl border border-black/10 p-4">
                        <p class="text-sm text-slate-500 mb-1">Telefonas</p>
                        <p id="customer-phone" class="font-semibold text-stone-900">—</p>
                    </div>

                    <div class="rounded-2xl border border-black/10 p-4">
                        <p class="text-sm text-slate-500 mb-1">El. paštas</p>
                        <p id="customer-email" class="font-semibold text-stone-900 break-all">—</p>
                    </div>

                    <div class="rounded-2xl border border-black/10 p-4">
                        <p class="text-sm text-slate-500 mb-1">Sukurta</p>
                        <p id="created-at" class="font-semibold text-stone-900">—</p>
                    </div>

                    <div class="rounded-2xl border border-black/10 p-4 md:col-span-2">
                        <p class="text-sm text-slate-500 mb-1">Pristatymo adresas</p>
                        <p id="shipping-address" class="font-semibold text-stone-900">—</p>
                    </div>

                    <div class="rounded-2xl border border-black/10 p-4">
                        <p class="text-sm text-slate-500 mb-1">Miestas</p>
                        <p id="shipping-city" class="font-semibold text-stone-900">—</p>
                    </div>

                    <div class="rounded-2xl border border-black/10 p-4">
                        <p class="text-sm text-slate-500 mb-1">Pašto kodas / šalis</p>
                        <p id="shipping-postcode-country" class="font-semibold text-stone-900">—</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-black/10 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-stone-900">Užsakytos prekės</h2>
                        <p class="text-sm text-slate-500">Šio užsakymo pozicijos.</p>
                    </div>
                </div>

                <div id="items-wrap" class="space-y-4">
                    <p class="text-slate-500 text-sm">Kraunama...</p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-black/10 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-stone-900 mb-4">Santrauka</h2>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">Užsakymo ID</span>
                        <span id="summary-id" class="font-bold text-stone-900">—</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">Suma</span>
                        <span id="summary-total" class="font-bold text-stone-900">—</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">Mokėjimas</span>
                        <span id="payment-status" class="font-bold text-stone-900">—</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">Būsena</span>
                        <span id="summary-status" class="font-bold text-stone-900">—</span>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-black/10 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-stone-900 mb-4">Būsenos valdymas</h2>

                <label for="status-select" class="block text-sm font-medium text-stone-700 mb-2">
                    Keisti būseną
                </label>

                <select id="status-select"
                        class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="pending">Laukia patvirtinimo</option>
                    <option value="paid">Patvirtinti</option>
                    <option value="shipped">Vykdomi / išsiųsti</option>
                    <option value="cancelled">Atšauktas</option>
                </select>

                <button id="save-status-btn"
                        class="mt-4 w-full rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-700 transition">
                    Išsaugoti būseną
                </button>
            </div>
        </div>
    </div>

    <script>
        const orderId = {{ $orderId }};

        const msgBox = document.getElementById('msg');
        const itemsWrap = document.getElementById('items-wrap');
        const statusSelect = document.getElementById('status-select');
        const saveStatusBtn = document.getElementById('save-status-btn');

        function showMessage(text, type = 'success') {
            msgBox.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-700', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700');

            if (type === 'error') {
                msgBox.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
            } else {
                msgBox.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
            }

            msgBox.textContent = text;
        }

        function formatMoney(value) {
            return new Intl.NumberFormat('lt-LT', {
                style: 'currency',
                currency: 'EUR'
            }).format(Number(value || 0));
        }

        function formatDate(value) {
            if (!value) return '—';

            return new Date(value).toLocaleString('lt-LT', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function statusLabel(status) {
            switch (status) {
                case 'pending': return 'Laukia patvirtinimo';
                case 'paid': return 'Patvirtinti';
                case 'shipped': return 'Vykdomi / išsiųsti';
                case 'cancelled': return 'Atšauktas';
                default: return status ?? '—';
            }
        }

        function paymentLabel(status) {
            switch (status) {
                case 'paid': return 'Apmokėta';
                case 'unpaid': return 'Laukia suderinimo';
                case 'refunded': return 'Grąžinta';
                default: return status ?? '—';
            }
        }

        function badgeClasses(status) {
            switch (status) {
                case 'pending':
                    return 'border-amber-200 bg-amber-50 text-amber-700';
                case 'paid':
                    return 'border-emerald-200 bg-emerald-50 text-emerald-700';
                case 'shipped':
                    return 'border-blue-200 bg-blue-50 text-blue-700';
                case 'cancelled':
                    return 'border-rose-200 bg-rose-50 text-rose-700';
                default:
                    return 'border-stone-200 bg-stone-50 text-stone-700';
            }
        }

        async function loadOrder() {
            try {
                const response = await fetch(`/api/v1/admin/orders/${orderId}`, {
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error('Nepavyko gauti užsakymo duomenų.');
                }

                const result = await response.json();
                const order = result.data.order;
                const items = result.data.items ?? [];

                document.getElementById('customer-name').textContent = order.customer?.name ?? '—';
                document.getElementById('customer-phone').textContent = order.customer?.phone ?? '—';
                document.getElementById('customer-email').textContent = order.customer?.email ?? '—';
                document.getElementById('created-at').textContent = formatDate(order.created_at);
                document.getElementById('shipping-address').textContent = order.shipping?.address ?? '—';
                document.getElementById('shipping-city').textContent = order.shipping?.city ?? '—';
                document.getElementById('shipping-postcode-country').textContent =
                    `${order.shipping?.postcode ?? '—'} / ${order.shipping?.country ?? '—'}`;

                document.getElementById('summary-id').textContent = `#${order.id}`;
                document.getElementById('summary-total').textContent = formatMoney(order.total_amount);
                document.getElementById('payment-status').textContent = paymentLabel(order.payment?.status);
                document.getElementById('summary-status').textContent = statusLabel(order.status);

                const badge = document.getElementById('order-status-badge');
                badge.className = `inline-flex items-center rounded-full px-3 py-1 text-xs font-bold border ${badgeClasses(order.status)}`;
                badge.textContent = statusLabel(order.status);

                statusSelect.value = order.status;

                if (!items.length) {
                    itemsWrap.innerHTML = `<p class="text-slate-500 text-sm">Šiame užsakyme prekių nerasta.</p>`;
                    return;
                }

                itemsWrap.innerHTML = items.map(item => `
                    <div class="rounded-2xl border border-black/10 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="font-semibold text-stone-900">${item.name ?? 'Prekė'}</p>
                            <p class="text-sm text-slate-500">Kiekis: ${item.quantity}</p>
                            <p class="text-sm text-slate-500">Vieneto kaina: ${formatMoney(item.unit_price)}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-slate-500">Suma</p>
                            <p class="font-bold text-stone-900">${formatMoney(item.line_total)}</p>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                itemsWrap.innerHTML = `<p class="text-red-600 text-sm">${error.message}</p>`;
            }
        }

        async function updateStatus() {
            saveStatusBtn.disabled = true;
            saveStatusBtn.textContent = 'Saugoma...';

            try {
                const response = await fetch(`/api/v1/admin/orders/${orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: statusSelect.value
                    })
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Nepavyko atnaujinti būsenos.');
                }

                showMessage(result.message || 'Būsena atnaujinta.');
                await loadOrder();
            } catch (error) {
                showMessage(error.message, 'error');
            } finally {
                saveStatusBtn.disabled = false;
                saveStatusBtn.textContent = 'Išsaugoti būseną';
            }
        }

        saveStatusBtn.addEventListener('click', updateStatus);

        loadOrder();
    </script>
</x-admin-layout>