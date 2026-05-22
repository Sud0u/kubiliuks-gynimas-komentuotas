<x-admin-layout :title="'Užsakymai'">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-stone-900">Užsakymai</h1>
            <p class="text-slate-500 mt-1">Čia rodomi visi klientų užsakymai.</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
        <button type="button" data-status="all"
            class="status-card text-left rounded-2xl border border-black/10 bg-white p-5 transition hover:shadow-sm">
            <div class="text-sm text-stone-500">Visi užsakymai</div>
            <div id="countAll" class="mt-2 text-2xl font-extrabold text-stone-900">0</div>
        </button>

        <button type="button" data-status="pending"
            class="status-card text-left rounded-2xl border border-amber-200 bg-amber-50 p-5 transition hover:shadow-sm">
            <div class="text-sm text-amber-700">Laukia patvirtinimo</div>
            <div id="countPending" class="mt-2 text-2xl font-extrabold text-amber-900">0</div>
        </button>

        <button type="button" data-status="paid"
            class="status-card text-left rounded-2xl border border-emerald-200 bg-emerald-50 p-5 transition hover:shadow-sm">
            <div class="text-sm text-emerald-700">Patvirtinti</div>
            <div id="countPaid" class="mt-2 text-2xl font-extrabold text-emerald-900">0</div>
        </button>

        <button type="button" data-status="shipped"
            class="status-card text-left rounded-2xl border border-blue-200 bg-blue-50 p-5 transition hover:shadow-sm">
            <div class="text-sm text-blue-700">Vykdomi / išsiųsti</div>
            <div id="countShipped" class="mt-2 text-2xl font-extrabold text-blue-900">0</div>
        </button>
    </div>

    <div class="rounded-3xl border border-black/10 bg-white p-4 md:p-5 mb-6">
        <div class="flex flex-col lg:flex-row gap-4 lg:items-end lg:justify-between">
            <div class="flex-1">
                <label for="searchInput" class="block text-sm font-semibold text-stone-700 mb-2">
                    Paieška
                </label>

                <input
                    id="searchInput"
                    type="text"
                    placeholder="Ieškoti pagal ID, vardą, el. paštą, telefoną ar miestą"
                    class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
            </div>

            <div class="flex gap-3">
                <button
                    id="searchBtn"
                    type="button"
                    class="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700 transition"
                >
                    Ieškoti
                </button>

                <button
                    id="resetBtn"
                    type="button"
                    class="rounded-2xl border border-black/10 bg-white px-5 py-3 text-sm font-bold text-stone-700 hover:bg-stone-50 transition"
                >
                    Išvalyti
                </button>
            </div>
        </div>

        <div class="mt-4 text-sm text-slate-500">
            <span id="activeFilterText">Rodomi visi užsakymai</span>
        </div>
    </div>

    <div id="loading" class="text-stone-600">Kraunama...</div>
    <div id="error" class="hidden rounded-2xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm mb-6"></div>
    <div id="empty" class="hidden rounded-2xl border border-black/10 bg-white p-10 text-center text-stone-600"></div>

    <div id="list" class="hidden space-y-4"></div>

    <div id="pagination" class="hidden mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div id="paginationInfo" class="text-sm text-slate-500">—</div>

        <div class="flex items-center gap-2">
            <button
                id="prevPageBtn"
                type="button"
                class="rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                ← Ankstesnis
            </button>

            <div id="pageIndicator" class="text-sm font-semibold text-stone-700 min-w-[90px] text-center">
                —
            </div>

            <button
                id="nextPageBtn"
                type="button"
                class="rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Kitas →
            </button>
        </div>
    </div>

    <script>
        const state = {
            status: 'all',
            q: '',
            page: 1,
            perPage: 12,
            meta: null
        };

        const loading = document.getElementById('loading');
        const list = document.getElementById('list');
        const error = document.getElementById('error');
        const empty = document.getElementById('empty');
        const searchInput = document.getElementById('searchInput');
        const activeFilterText = document.getElementById('activeFilterText');
        const pagination = document.getElementById('pagination');
        const paginationInfo = document.getElementById('paginationInfo');
        const pageIndicator = document.getElementById('pageIndicator');
        const prevPageBtn = document.getElementById('prevPageBtn');
        const nextPageBtn = document.getElementById('nextPageBtn');

        function fmtDate(iso) {
            if (!iso) return '—';

            return new Date(iso).toLocaleString('lt-LT', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function money(v) {
            return new Intl.NumberFormat('lt-LT', {
                style: 'currency',
                currency: 'EUR'
            }).format(Number(v || 0));
        }

        function esc(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function statusMeta(status) {
            const st = String(status || '').toLowerCase();

            if (st === 'paid') {
                return {
                    label: 'Patvirtintas',
                    badge: 'border-emerald-200 bg-emerald-50 text-emerald-800'
                };
            }

            if (st === 'pending') {
                return {
                    label: 'Laukia patvirtinimo',
                    badge: 'border-amber-200 bg-amber-50 text-amber-800'
                };
            }

            if (st === 'shipped') {
                return {
                    label: 'Vykdomas / išsiųstas',
                    badge: 'border-blue-200 bg-blue-50 text-blue-800'
                };
            }

            if (st === 'cancelled') {
                return {
                    label: 'Atšauktas',
                    badge: 'border-red-200 bg-red-50 text-red-800'
                };
            }

            return {
                label: status || '—',
                badge: 'border-stone-200 bg-stone-50 text-stone-700'
            };
        }

        function statusFilterLabel(status) {
            switch (status) {
                case 'pending': return 'Rodomi tik laukiantys patvirtinimo';
                case 'paid': return 'Rodomi tik patvirtinti';
                case 'shipped': return 'Rodomi tik vykdomi / išsiųsti';
                case 'cancelled': return 'Rodomi tik atšaukti';
                default: return 'Rodomi visi užsakymai';
            }
        }

        function paymentText(order) {
            const payment = order.payment || null;

            if (!payment || !payment.status) {
                return 'Mokėjimas nenurodytas';
            }

            const isPaysera = payment.provider === 'paysera' || payment.meta?.requested_method === 'paysera';

            const statusMap = {
                unpaid: isPaysera ? 'Laukia Paysera apmokėjimo' : 'Laukia suderinimo',
                paid: 'Apmokėta',
                failed: 'Nepavyko',
                cancelled: 'Mokėjimas nutrauktas',
                refunded: 'Grąžinta'
            };

            const providerMap = {
                manual: 'rankinis suderinimas',
                bank: 'bankinis pavedimas',
                stripe: 'Stripe',
                paysera: 'Paysera'
            };

            const statusLabel = statusMap[payment.status] || payment.status;
            const providerLabel = payment.provider ? (providerMap[payment.provider] || payment.provider) : '';

            return providerLabel ? `${statusLabel} (${providerLabel})` : statusLabel;
        }

        function customerName(order) {
            return order.customer?.name || order.customer_name || 'Nenurodyta';
        }

        function customerPhone(order) {
            return order.customer?.phone || order.customer_phone || 'Nenurodyta';
        }

        function customerEmail(order) {
            return order.customer?.email || order.customer_email || 'Nenurodyta';
        }

        function cityText(order) {
            return order.shipping?.city || order.shipping_city || 'Nenurodyta';
        }

        function updateSummary(counts) {
            document.getElementById('countAll').textContent = counts?.all ?? 0;
            document.getElementById('countPending').textContent = counts?.pending ?? 0;
            document.getElementById('countPaid').textContent = counts?.paid ?? 0;
            document.getElementById('countShipped').textContent = counts?.shipped ?? 0;
        }

        function updateActiveStatusCards() {
            document.querySelectorAll('.status-card').forEach(card => {
                const cardStatus = card.dataset.status;
                const isActive = cardStatus === state.status;

                card.classList.remove('ring-2', 'ring-emerald-500');

                if (isActive) {
                    card.classList.add('ring-2', 'ring-emerald-500');
                }
            });
        }

        function renderOrders(orders) {
            if (!orders.length) {
                empty.classList.remove('hidden');
                empty.textContent = 'Užsakymų pagal pasirinktus filtrus nerasta.';
                list.classList.add('hidden');
                return;
            }

            empty.classList.add('hidden');
            list.classList.remove('hidden');

            list.innerHTML = orders.map(order => {
                const status = statusMeta(order.status);

                return `
                    <a href="/admin/orders/${order.id}" class="block rounded-2xl border border-black/10 bg-white p-5 hover:bg-stone-50 hover:shadow-sm transition">
                        <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <div class="text-lg font-bold text-stone-900">
                                        Užsakymas #${order.id}
                                    </div>

                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border ${status.badge}">
                                        ${status.label}
                                    </span>
                                </div>

                                <div class="mt-2 text-sm text-stone-500">
                                    Sukurtas: ${fmtDate(order.created_at)}
                                </div>

                                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4 text-sm">
                                    <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3">
                                        <div class="text-stone-500 mb-1">Klientas</div>
                                        <div class="font-semibold text-stone-900 break-words">${esc(customerName(order))}</div>
                                    </div>

                                    <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3">
                                        <div class="text-stone-500 mb-1">Telefonas</div>
                                        <div class="font-semibold text-stone-900 break-words">${esc(customerPhone(order))}</div>
                                    </div>

                                    <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3">
                                        <div class="text-stone-500 mb-1">El. paštas</div>
                                        <div class="font-semibold text-stone-900 break-all">${esc(customerEmail(order))}</div>
                                    </div>

                                    <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3">
                                        <div class="text-stone-500 mb-1">Miestas</div>
                                        <div class="font-semibold text-stone-900 break-words">${esc(cityText(order))}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="xl:w-72 shrink-0">
                                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-stone-500">Suma</span>
                                            <span class="font-extrabold text-stone-900">${money(order.total_amount)}</span>
                                        </div>

                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-stone-500">Mokėjimas</span>
                                            <span class="font-semibold text-stone-900 text-right">${esc(paymentText(order))}</span>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-stone-200">
                                        <div class="inline-flex items-center text-sm font-semibold text-stone-900">
                                            Atidaryti →
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                `;
            }).join('');
        }

        function updatePagination(meta) {
            state.meta = meta || null;

            if (!meta || (meta.last_page ?? 1) <= 1) {
                pagination.classList.add('hidden');
                return;
            }

            pagination.classList.remove('hidden');

            paginationInfo.textContent = `Rodoma ${meta.from ?? 0}–${meta.to ?? 0} iš ${meta.total ?? 0}`;
            pageIndicator.textContent = `Puslapis ${meta.current_page ?? 1} / ${meta.last_page ?? 1}`;

            prevPageBtn.disabled = (meta.current_page ?? 1) <= 1;
            nextPageBtn.disabled = (meta.current_page ?? 1) >= (meta.last_page ?? 1);
        }

        async function loadAdminOrders() {
            loading.classList.remove('hidden');
            error.classList.add('hidden');
            list.classList.add('hidden');
            empty.classList.add('hidden');

            updateActiveStatusCards();
            activeFilterText.textContent = statusFilterLabel(state.status);

            const params = new URLSearchParams({
                status: state.status,
                q: state.q,
                page: String(state.page),
                per_page: String(state.perPage)
            });

            try {
                const res = await fetch(`/api/v1/admin/orders?${params.toString()}`, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                });

                const json = await res.json().catch(() => null);

                if (!res.ok) {
                    loading.classList.add('hidden');
                    error.classList.remove('hidden');
                    error.textContent = json?.message || ('Nepavyko užkrauti. (HTTP ' + res.status + ')');
                    return;
                }

                const orders = Array.isArray(json?.data) ? json.data : [];
                const counts = json?.counts || {};
                const meta = json?.meta || null;

                updateSummary(counts);
                renderOrders(orders);
                updatePagination(meta);

                loading.classList.add('hidden');
            } catch (e) {
                loading.classList.add('hidden');
                error.classList.remove('hidden');
                error.textContent = 'Klaida kraunant užsakymus.';
            }
        }

        document.querySelectorAll('.status-card').forEach(card => {
            card.addEventListener('click', function () {
                state.status = this.dataset.status || 'all';
                state.page = 1;
                loadAdminOrders();
            });
        });

        document.getElementById('searchBtn').addEventListener('click', function () {
            state.q = searchInput.value.trim();
            state.page = 1;
            loadAdminOrders();
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                state.q = searchInput.value.trim();
                state.page = 1;
                loadAdminOrders();
            }
        });

        document.getElementById('resetBtn').addEventListener('click', function () {
            state.status = 'all';
            state.q = '';
            state.page = 1;
            searchInput.value = '';
            loadAdminOrders();
        });

        prevPageBtn.addEventListener('click', function () {
            if ((state.meta?.current_page ?? 1) > 1) {
                state.page = (state.meta?.current_page ?? 1) - 1;
                loadAdminOrders();
            }
        });

        nextPageBtn.addEventListener('click', function () {
            if ((state.meta?.current_page ?? 1) < (state.meta?.last_page ?? 1)) {
                state.page = (state.meta?.current_page ?? 1) + 1;
                loadAdminOrders();
            }
        });

        loadAdminOrders();
    </script>
</x-admin-layout>