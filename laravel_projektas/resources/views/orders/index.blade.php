@extends('layouts.app')

@section('title', 'Mano užsakymai')

@section('content')
@php
    $statusMap = [
        'pending' => [
            'label' => 'Laukia patvirtinimo',
            'badge' => 'bg-amber-100 text-amber-800 border border-amber-200',
        ],
        'paid' => [
            'label' => 'Patvirtintas',
            'badge' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
        ],
        'shipped' => [
            'label' => 'Vykdomas / išsiųstas',
            'badge' => 'bg-blue-100 text-blue-800 border border-blue-200',
        ],
        'cancelled' => [
            'label' => 'Atšauktas',
            'badge' => 'bg-rose-100 text-rose-800 border border-rose-200',
        ],
    ];
@endphp

<section class="py-12 bg-stone-50 min-h-[70vh]">
    <div class="max-w-6xl mx-auto px-4 lg:px-0">
        <div class="flex items-center justify-between gap-3 mb-6">
            <h1 class="text-3xl font-extrabold text-stone-900">Mano užsakymai</h1>

            <a href="{{ route('prekes') }}" class="text-sm text-stone-600 hover:text-stone-900">
                ← Grįžti į prekes
            </a>
        </div>

        @if($orders->isEmpty())
            <div class="bg-white rounded-2xl border border-stone-200 p-8 text-center">
                <div class="text-lg font-semibold text-stone-900">Užsakymų nėra</div>

                <a href="{{ route('prekes') }}"
                   class="inline-flex mt-5 items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800">
                    Rodyti prekes
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    @php
                        $currentStatus = $statusMap[$order->status] ?? [
                            'label' => ucfirst($order->status),
                            'badge' => 'bg-stone-100 text-stone-800 border border-stone-200',
                        ];

                        $itemsCount = $order->relationLoaded('items')
                            ? $order->items->sum('quantity')
                            : null;
                    @endphp

                    <a href="{{ route('orders.show', $order->id) }}"
                       class="block bg-white rounded-2xl border border-stone-200 p-5 hover:bg-stone-50 hover:shadow-sm transition">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-3">
                                    <div class="text-lg font-bold text-stone-900">
                                        Užsakymas #{{ $order->id }}
                                    </div>

                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $currentStatus['badge'] }}">
                                        {{ $currentStatus['label'] }}
                                    </span>
                                </div>

                                <div class="text-sm text-stone-500 mt-2">
                                    {{ $order->created_at->format('Y-m-d H:i') }}
                                </div>

                                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm text-stone-600">
                                    <div>
                                        <span class="text-stone-500">Užsakymo numeris:</span>
                                        <span class="font-semibold text-stone-900">#{{ $order->id }}</span>
                                    </div>

                                    @if(!is_null($itemsCount))
                                        <div>
                                            <span class="text-stone-500">Prekių kiekis:</span>
                                            <span class="font-semibold text-stone-900">{{ $itemsCount }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-between lg:justify-end gap-6">
                                <div class="text-left lg:text-right">
                                    <div class="text-sm text-stone-500">Bendra suma</div>
                                    <div class="font-extrabold text-stone-900 text-xl">
                                        {{ number_format($order->total_amount, 2, ',', ' ') }} €
                                    </div>
                                </div>

                                <div class="text-stone-400 text-xl">
                                    →
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection