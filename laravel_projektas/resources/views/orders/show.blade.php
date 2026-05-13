@extends('layouts.app')

@section('title', 'Užsakymas #' . $order->id)

@section('content')
@php
    $statusMap = [
        'pending' => [
            'label' => 'Laukia patvirtinimo',
            'badge' => 'bg-amber-100 text-amber-800 border border-amber-200',
            'box' => 'bg-amber-50 border-amber-200 text-amber-900',
            'message' => 'Jūsų užsakymas gautas. Su jumis bus susisiekta dėl apmokėjimo ir pristatymo detalių.',
        ],
        'paid' => [
            'label' => 'Patvirtintas',
            'badge' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            'box' => 'bg-emerald-50 border-emerald-200 text-emerald-900',
            'message' => 'Užsakymas patvirtintas.',
        ],
        'shipped' => [
            'label' => 'Vykdomas / išsiųstas',
            'badge' => 'bg-blue-100 text-blue-800 border border-blue-200',
            'box' => 'bg-blue-50 border-blue-200 text-blue-900',
            'message' => 'Užsakymas jau vykdomas arba išsiųstas.',
        ],
        'cancelled' => [
            'label' => 'Atšauktas',
            'badge' => 'bg-rose-100 text-rose-800 border border-rose-200',
            'box' => 'bg-rose-50 border-rose-200 text-rose-900',
            'message' => 'Šis užsakymas buvo atšauktas.',
        ],
    ];

    $paymentMap = [
        'unpaid' => 'Laukia suderinimo',
        'paid' => 'Apmokėta',
        'failed' => 'Nepavyko',
        'cancelled' => 'Atšaukta',
        'refunded' => 'Grąžinimas tvarkomas',
    ];

    $currentStatus = $statusMap[$order->status] ?? [
        'label' => ucfirst($order->status),
        'badge' => 'bg-stone-100 text-stone-800 border border-stone-200',
        'box' => 'bg-stone-50 border-stone-200 text-stone-900',
        'message' => 'Užsakymo būsena atnaujinta.',
    ];

    $paymentStatus = $order->payment?->status ?? 'unpaid';
    $paymentLabel = $paymentMap[$paymentStatus] ?? ucfirst($paymentStatus);

    $methodMap = [
        'manual' => 'Rankinis suderinimas',
        'bank' => 'Bankinis pavedimas po suderinimo',
        'stripe' => 'Stripe',
        'paysera' => 'Paysera',
    ];

    $paymentMethodLabel = $methodMap[$order->payment?->provider ?? ''] ?? ($order->payment?->provider ?: 'Nenurodyta');
    $snapshot = collect($order->payment?->meta['cart_snapshot'] ?? [])->values();
@endphp

<section class="py-8 sm:py-12 bg-stone-50 min-h-[70vh]">
    <div class="max-w-6xl mx-auto px-4 lg:px-0">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-stone-900">Užsakymas #{{ $order->id }}</h1>
                <p class="text-sm text-stone-500 mt-1">
                    Pateiktas {{ $order->created_at->format('Y-m-d H:i') }}
                </p>
            </div>

            <a href="{{ route('orders.index') }}" class="text-sm text-stone-600 hover:text-stone-900 whitespace-nowrap">
                ← Atgal į užsakymus
            </a>
        </div>

        <div id="order-alert" class="hidden mb-6 rounded-2xl border px-4 py-3 text-sm"></div>

        <div class="mb-6 rounded-2xl border p-4 {{ $currentStatus['box'] }}">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="font-semibold">
                        {{ $currentStatus['message'] }}
                    </div>
                    <div class="text-sm opacity-80 mt-1">
                        Užsakymo būsena: {{ $currentStatus['label'] }}
                    </div>
                </div>

                <div class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold {{ $currentStatus['badge'] }} w-fit">
                    {{ $currentStatus['label'] }}
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-stone-200 p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="text-xl font-bold text-stone-900">Užsakytos prekės</h2>
                        <span class="text-sm text-stone-500">
                            {{ $order->items->sum('quantity') }} vnt.
                        </span>
                    </div>

                    <div class="space-y-4">
                        @forelse($order->items as $index => $item)
                            @php
                                $snapshotItem = $snapshot->get($index);
                                $displayName = $snapshotItem['name'] ?? ($item->product?->name ?? ('Prekė #' . $item->product_id));
                                $displaySubtitle = $snapshotItem['subtitle'] ?? '';
                            @endphp

                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 rounded-2xl border border-stone-200 p-4">
                                <div class="min-w-0">
                                    <div class="font-semibold text-stone-900 break-words">
                                        {{ $displayName }}
                                    </div>

                                    @if(!empty($displaySubtitle))
                                        <div class="text-sm text-stone-500 mt-1">
                                            {{ $displaySubtitle }}
                                        </div>
                                    @endif

                                    <div class="text-sm text-stone-500 mt-1">
                                        {{ number_format($item->unit_price, 2, '.', ' ') }} € / vnt.
                                        · kiekis: {{ $item->quantity }}
                                    </div>
                                </div>

                                <div class="text-left sm:text-right shrink-0">
                                    <div class="font-bold text-stone-900">
                                        {{ number_format($item->line_total, 2, '.', ' ') }} €
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                                Šis užsakymas neturi prekių eilučių.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-stone-200 p-5 sm:p-6">
                    <h2 class="text-xl font-bold text-stone-900 mb-4">Pristatymo informacija</h2>

                    <div class="grid gap-4 md:grid-cols-2 text-sm">
                        <div>
                            <div class="text-stone-500 mb-1">Pirkėjas</div>
                            <div class="font-semibold text-stone-900">{{ $order->customer_name }}</div>
                        </div>

                        <div>
                            <div class="text-stone-500 mb-1">El. paštas</div>
                            <div class="font-semibold text-stone-900 break-all">{{ $order->customer_email }}</div>
                        </div>

                        <div>
                            <div class="text-stone-500 mb-1">Telefonas</div>
                            <div class="font-semibold text-stone-900">
                                {{ $order->customer_phone ?: 'Nenurodyta' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-stone-500 mb-1">Šalis</div>
                            <div class="font-semibold text-stone-900">
                                {{ $order->shipping_country ?: 'Nenurodyta' }}
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <div class="text-stone-500 mb-1">Pristatymo adresas</div>
                            <div class="font-semibold text-stone-900">
                                {{ $order->shipping_address ?: 'Nenurodyta' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-stone-500 mb-1">Miestas</div>
                            <div class="font-semibold text-stone-900">
                                {{ $order->shipping_city ?: 'Nenurodyta' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-stone-500 mb-1">Pašto kodas</div>
                            <div class="font-semibold text-stone-900">
                                {{ $order->shipping_postcode ?: 'Nenurodyta' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="bg-white rounded-2xl border border-stone-200 p-5 sm:p-6">
                    <h2 class="text-xl font-bold text-stone-900 mb-4">Suvestinė</h2>

                    <div class="space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-stone-500">Užsakymo būsena</span>
                            <span class="font-semibold text-stone-900">{{ $currentStatus['label'] }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-stone-500">Apmokėjimo būsena</span>
                            <span class="font-semibold text-stone-900">{{ $paymentLabel }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-stone-500">Užsakymo tipas</span>
                            <span class="font-semibold text-stone-900">{{ $paymentMethodLabel }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-stone-500">Prekių kiekis</span>
                            <span class="font-semibold text-stone-900">{{ $order->items->sum('quantity') }}</span>
                        </div>

                        <div class="pt-4 border-t border-stone-200 flex items-center justify-between gap-4">
                            <span class="font-semibold text-stone-900">Bendra suma</span>
                            <span class="text-xl font-extrabold text-stone-900">
                                {{ number_format($order->total_amount, 2, '.', ' ') }} €
                            </span>
                        </div>
                    </div>
                </div>

                @if($order->status === 'pending')
                    <div class="bg-white rounded-2xl border border-stone-200 p-5 sm:p-6">
                        <h2 class="text-xl font-bold text-stone-900 mb-3">Galite atšaukti užsakymą</h2>
                        <p class="text-sm text-stone-500 leading-6">
                            Jeigu apsigalvojote, kol užsakymas dar laukia patvirtinimo, galite jį atšaukti patys.
                        </p>

                        <button
                            id="cancelOrderBtn"
                            type="button"
                            class="mt-4 w-full rounded-xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 hover:bg-rose-100 transition"
                            data-order-id="{{ $order->id }}"
                        >
                            Atšaukti užsakymą
                        </button>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</section>
@endsection

@section('scripts')
@if($order->status === 'pending')
<script>
(() => {
    const btn = document.getElementById('cancelOrderBtn');
    const alertBox = document.getElementById('order-alert');

    if (!btn) return;

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showAlert(text, ok = true) {
        alertBox.classList.remove('hidden');
        alertBox.classList.remove(
            'border-green-200', 'bg-green-50', 'text-green-900',
            'border-red-200', 'bg-red-50', 'text-red-900'
        );

        if (ok) {
            alertBox.classList.add('border-green-200', 'bg-green-50', 'text-green-900');
        } else {
            alertBox.classList.add('border-red-200', 'bg-red-50', 'text-red-900');
        }

        alertBox.textContent = text;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    btn.addEventListener('click', async () => {
        if (!confirm('Ar tikrai norite atšaukti šį užsakymą?')) {
            return;
        }

        btn.disabled = true;
        btn.classList.add('opacity-60', 'cursor-not-allowed');

        try {
            const response = await fetch(`/api/v1/orders/${btn.dataset.orderId}/cancel`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data?.message || 'Nepavyko atšaukti užsakymo.');
            }

            showAlert('Užsakymas atšauktas.', true);

            setTimeout(() => {
                window.location.reload();
            }, 800);
        } catch (error) {
            showAlert(error.message || 'Nepavyko atšaukti užsakymo.', false);
            btn.disabled = false;
            btn.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    });
})();
</script>
@endif
@endsection