@php
    function statusLt($status) {
        return match($status) {
            'pending' => 'Laukia patvirtinimo',
            'paid' => 'Patvirtintas',
            'shipped' => 'Vykdomi / išsiųsti',
            'cancelled' => 'Atšauktas',
            default => $status,
        };
    }

    function paymentStatusLt($status) {
        return match($status) {
            'unpaid' => 'Laukia suderinimo',
            'paid' => 'Apmokėta',
            'failed' => 'Nepavyko',
            'cancelled' => 'Atšaukta',
            'refunded' => 'Grąžinimas tvarkomas',
            default => $status,
        };
    }

    function paymentMethodLt($method) {
        return match($method) {
            'cash_on_delivery' => 'Apmokėjimas vietoje',
            'bank' => 'Bankinis pavedimas',
            'manual' => 'Rankinis suderinimas',
            'stripe' => 'Stripe',
            'paysera' => 'Paysera',
            default => $method ?: 'Nenurodyta',
        };
    }

    function providerLt($provider) {
        return match($provider) {
            'manual' => 'Rankinis suderinimas',
            'bank' => 'Bankinis pavedimas',
            'stripe' => 'Stripe',
            'paysera' => 'Paysera',
            default => $provider ?: 'Nenurodyta',
        };
    }

    $snapshot = collect($order->payment?->meta['cart_snapshot'] ?? [])->values();
@endphp

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Naujas užsakymas</title>
</head>
<body style="margin:0; padding:24px; background:#f5f5f4; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellspacing="0" cellpadding="0" style="max-width:900px; margin:auto; background:white; border-radius:12px; overflow:hidden;">
    <tr>
        <td style="padding:24px; background:#111827; color:white;">
            <h1 style="margin:0;">Naujas užsakymas #{{ $order->id }}</h1>
        </td>
    </tr>

    <tr>
        <td style="padding:24px;">
            <h2>Bendra informacija</h2>

            <p><strong>Užsakymo ID:</strong> #{{ $order->id }}</p>
            <p><strong>Data:</strong> {{ $order->created_at }}</p>
            <p><strong>Užsakymo būsena:</strong> {{ statusLt($order->status) }}</p>
            <p><strong>Bendra suma:</strong> {{ number_format($order->total_amount, 2, ',', ' ') }} €</p>

            <hr>

            <h2>Kliento informacija</h2>

            <p><strong>Vardas:</strong> {{ $order->customer_name }}</p>
            <p><strong>Email:</strong> {{ $order->customer_email }}</p>
            <p><strong>Telefonas:</strong> {{ $order->customer_phone ?: 'Nenurodyta' }}</p>
            <p><strong>Adresas:</strong> {{ $order->shipping_address }}</p>
            <p><strong>Miestas:</strong> {{ $order->shipping_city ?: 'Nenurodyta' }}</p>
            <p><strong>Pašto kodas:</strong> {{ $order->shipping_postcode ?: 'Nenurodyta' }}</p>
            <p><strong>Šalis:</strong> {{ $order->shipping_country ?: 'Nenurodyta' }}</p>

            <hr>

            <h2>Apmokėjimas</h2>

            <p><strong>Tiekėjas:</strong> {{ providerLt($order->payment?->provider) }}</p>
            <p><strong>Apmokėjimo būsena:</strong> {{ paymentStatusLt($order->payment?->status) }}</p>
            <p><strong>Pasirinktas būdas:</strong> {{ paymentMethodLt($order->payment?->meta['requested_method'] ?? null) }}</p>

            <hr>

            <h2>Užsakytos prekės</h2>

            @foreach($order->items as $index => $item)
                @php
                    $snapshotItem = $snapshot->get($index);
                    $displayName = $snapshotItem['name'] ?? ($item->product?->name ?? ('Prekė #' . $item->product_id));
                    $displaySubtitle = $snapshotItem['subtitle'] ?? '';
                @endphp

                <div style="margin-bottom:20px; padding:14px; border:1px solid #ddd; border-radius:10px;">
                    <p><strong>{{ $displayName }}</strong></p>

                    @if(!empty($displaySubtitle))
                        <p>{{ $displaySubtitle }}</p>
                    @endif

                    <p>Kiekis: {{ $item->quantity }}</p>
                    <p>Kaina: {{ number_format($item->unit_price, 2, ',', ' ') }} €</p>
                    <p>Suma: {{ number_format($item->line_total, 2, ',', ' ') }} €</p>
                </div>
            @endforeach
        </td>
    </tr>
</table>

</body>
</html>