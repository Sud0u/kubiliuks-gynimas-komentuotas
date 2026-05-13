<x-admin-layout :title="'Apžvalga'">

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="rounded-[28px] bg-white soft p-6">
                    <div class="text-sm text-slate-500">Prekių kiekis</div>
                    <div class="mt-3 text-4xl font-extrabold" id="statProducts">—</div>
                </div>

                <div class="rounded-[28px] bg-white soft p-6">
                    <div class="text-sm text-slate-500">Užsakymų kiekis</div>
                    <div class="mt-3 text-4xl font-extrabold" id="statOrders">—</div>
                </div>

                <div class="rounded-[28px] bg-white soft p-6">
                    <div class="text-sm text-slate-500">Apyvarta (apmokėta)</div>
                    <div class="mt-3 text-4xl font-extrabold" id="statRevenue">—</div>
                </div>
            </div>

            <div class="rounded-[28px] bg-white soft p-6">
                <div class="flex items-center justify-between">
                    <div class="font-extrabold text-slate-900">Paskutiniai užsakymai</div>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">Atidaryti →</a>
                </div>

                <div class="mt-4 text-sm text-slate-500" id="recentOrdersLoading">Kraunama...</div>
                <div class="mt-4 grid gap-3 hidden" id="recentOrdersBox"></div>
            </div>

        </div>

        <div class="xl:col-span-4 space-y-6">

            <div class="rounded-[28px] bg-white soft p-6">
                <div class="flex items-center justify-between">
                    <div class="font-extrabold text-slate-900">Mažas likutis</div>
                    <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">Atidaryti →</a>
                </div>

                <div class="mt-4 text-sm text-slate-500" id="lowStockLoading">Kraunama...</div>
                <div class="mt-4 grid gap-3 hidden" id="lowStockBox"></div>
            </div>

            <div class="rounded-[28px] bg-white soft p-6">
                <div class="text-sm text-slate-500">Laukia apmokėjimo</div>
                <div class="mt-3 text-4xl font-extrabold" id="statPending">—</div>
                <div class="mt-4">
                    <a href="{{ route('admin.orders.index') }}"
                       class="block text-center rounded-2xl px-5 py-3 text-white font-semibold btn-blue soft2 hover:opacity-95 transition">
                        Peržiūrėti užsakymus
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script>
        function esc(s) {
            return String(s ?? '').replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[m]));
        }

        function money(v) {
            const n = Number(v || 0);
            return n.toLocaleString('lt-LT', { style: 'currency', currency: 'EUR' });
        }

        function statusLt(status) {
            switch (status) {
                case 'pending':
                    return 'Laukia patvirtinimo';
                case 'confirmed':
                    return 'Patvirtintas';
                case 'processing':
                    return 'Vykdomi / išsiųsti';
                case 'cancelled':
                    return 'Atšauktas';
                default:
                    return status ?? '—';
            }
        }

        function statusBadgeClass(status) {
            switch (status) {
                case 'pending':
                    return 'bg-amber-50 text-amber-700 border border-amber-200';
                case 'confirmed':
                    return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                case 'processing':
                    return 'bg-blue-50 text-blue-700 border border-blue-200';
                case 'cancelled':
                    return 'bg-red-50 text-red-700 border border-red-200';
                default:
                    return 'bg-slate-50 text-slate-700 border border-slate-200';
            }
        }

        async function api(url) {
            const res = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) {
                return null;
            }

            return await res.json();
        }

        async function loadDashboard() {
            const p = await api('/api/v1/admin/products');

            if (p) {
                document.getElementById('statProducts').textContent = p.summary?.total ?? '—';

                const low = Array.isArray(p.low_stock) ? p.low_stock.slice(0, 6) : [];
                const box = document.getElementById('lowStockBox');
                const loading = document.getElementById('lowStockLoading');

                loading.classList.add('hidden');
                box.classList.remove('hidden');

                if (!low.length) {
                    box.innerHTML = `<div class="text-sm text-slate-500">Šiuo metu nėra prekių su mažu likučiu.</div>`;
                } else {
                    box.innerHTML = low.map(x => `
                        <a href="/admin/products/${x.id}/edit" class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4 hover:bg-slate-100 transition">
                            <div class="truncate">
                                <div class="font-semibold text-slate-900 truncate">${esc(x.name)}</div>
                                <div class="text-xs text-slate-500">Reikia papildyti</div>
                            </div>
                            <div class="text-sm text-slate-700 font-semibold">Liko: ${esc(x.stock)}</div>
                        </a>
                    `).join('');
                }
            } else {
                document.getElementById('lowStockLoading').textContent = 'Nepavyko užkrauti prekių.';
            }

            const o = await api('/api/v1/admin/orders');

            if (o) {
                document.getElementById('statOrders').textContent = o.counts?.all ?? '—';
                document.getElementById('statPending').textContent = o.counts?.pending ?? '—';
                document.getElementById('statRevenue').textContent = money(o.paid_revenue ?? 0);

                const recentRaw = Array.isArray(o.recent) ? o.recent : [];
                const recent = recentRaw
                    .filter(x => x.status !== 'cancelled')
                    .slice(0, 6);

                const box = document.getElementById('recentOrdersBox');
                const loading = document.getElementById('recentOrdersLoading');

                loading.classList.add('hidden');
                box.classList.remove('hidden');

                if (!recent.length) {
                    box.innerHTML = `<div class="text-sm text-slate-500">Aktyvių užsakymų šiuo metu nėra.</div>`;
                } else {
                    box.innerHTML = recent.map(x => `
                        <a href="/admin/orders/${x.id}" class="block rounded-2xl border border-slate-100 bg-slate-50 p-4 hover:bg-slate-100 transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="font-semibold text-slate-900">Užsakymas #${x.id}</div>
                                <div class="font-semibold text-slate-900">${money(x.total_amount)}</div>
                            </div>

                            <div class="mt-3">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${statusBadgeClass(x.status)}">
                                    ${esc(statusLt(x.status))}
                                </span>
                            </div>
                        </a>
                    `).join('');
                }
            } else {
                document.getElementById('recentOrdersLoading').textContent = 'Nepavyko užkrauti užsakymų.';
            }
        }

        loadDashboard();
    </script>

</x-admin-layout>