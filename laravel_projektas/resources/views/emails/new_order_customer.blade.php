@php
    $snapshot = collect($order->payment?->meta['cart_snapshot'] ?? [])->values();
    $requestedMethod = $order->payment?->meta['requested_method'] ?? null;
    $isPayseraPayment = ($order->payment?->provider === 'paysera') || ($requestedMethod === 'paysera');
    $paymentStatus = $order->payment?->status ?? 'unpaid';

    $paymentLabel = match($paymentStatus) {
        'paid' => 'Apmokėta',
        'cancelled' => 'Mokėjimas nutrauktas',
        'failed' => 'Nepavyko',
        'refunded' => 'Grąžinimas tvarkomas',
        default => $isPayseraPayment ? 'Laukia Paysera apmokėjimo' : 'Laukia suderinimo',
    };
@endphp

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Užsakymas gautas</title>
</head>
<body style="margin:0; padding:24px; background:#f5f5f4; font-family:Arial, Helvetica, sans-serif; color:#1c1917;">
    <table width="100%" cellspacing="0" cellpadding="0" style="max-width:700px; margin:auto; background:#ffffff; border-radius:16px; overflow:hidden;">
        <tr>
            <td style="padding:24px 28px; background:#111827; color:#ffffff;">
                <div style="font-size:12px; letter-spacing:1px; text-transform:uppercase; opacity:.8;">Kubiliuks</div>
                <h1 style="margin:10px 0 0; font-size:28px; line-height:1.2;">Užsakymas gautas</h1>
            </td>
        </tr>

        <tr>
            <td style="padding:28px;">
                <p style="margin:0 0 14px; font-size:16px; line-height:1.6;">
                    Sveiki, {{ $order->customer_name }}.
                </p>

                <p style="margin:0 0 14px; font-size:16px; line-height:1.6;">
                    Gavome jūsų užsakymą <strong>#{{ $order->id }}</strong>.
                </p>

                @if($isPayseraPayment)
                    <p style="margin:0 0 14px; font-size:16px; line-height:1.6;">
                        Užsakymas sukurtas, bet jis bus laikomas apmokėtu tik tada, kai gausime Paysera patvirtinimą.
                    </p>

                    <p style="margin:0 0 22px; font-size:16px; line-height:1.6;">
                        Jei Paysera mokėjimo langą uždarėte arba mokėjimo neužbaigėte, apmokėjimas lieka neatliktas.
                    </p>
                @else
                    <p style="margin:0 0 22px; font-size:16px; line-height:1.6;">
                        Su jumis susisieksime dėl apmokėjimo, pristatymo ir kitų detalių.
                    </p>
                @endif

                <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin:0 0 24px;">
                    <tr>
                        <td style="padding:14px 16px; border:1px solid #e7e5e4; background:#fafaf9; font-size:14px;">
                            <div style="color:#78716c; margin-bottom:6px;">Užsakymo numeris</div>
                            <div style="font-weight:700; color:#111827;">#{{ $order->id }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 16px; border:1px solid #e7e5e4; border-top:none; font-size:14px;">
                            <div style="color:#78716c; margin-bottom:6px;">Bendra suma</div>
                            <div style="font-weight:700; color:#111827;">{{ number_format($order->total_amount, 2, ',', ' ') }} €</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 16px; border:1px solid #e7e5e4; border-top:none; font-size:14px;">
                            <div style="color:#78716c; margin-bottom:6px;">Apmokėjimo būsena</div>
                            <div style="font-weight:700; color:#111827;">{{ $paymentLabel }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 16px; border:1px solid #e7e5e4; border-top:none; font-size:14px;">
                            <div style="color:#78716c; margin-bottom:6px;">Pristatymo adresas</div>
                            <div style="font-weight:700; color:#111827;">
                                {{ $order->shipping_address }}
                                @if($order->shipping_city), {{ $order->shipping_city }}@endif
                                @if($order->shipping_postcode), {{ $order->shipping_postcode }}@endif
                                @if($order->shipping_country), {{ $order->shipping_country }}@endif
                            </div>
                        </td>
                    </tr>
                </table>

                <h2 style="margin:0 0 14px; font-size:18px; color:#111827;">Užsakytos prekės</h2>

                @foreach($order->items as $index => $item)
                    @php
                        $snapshotItem = $snapshot->get($index);
                        $displayName = $snapshotItem['name'] ?? ($item->product?->name ?? ('Prekė #' . $item->product_id));
                        $displaySubtitle = $snapshotItem['subtitle'] ?? '';
                    @endphp

                    <div style="padding:14px 16px; margin:0 0 12px; border:1px solid #e7e5e4; border-radius:12px;">
                        <div style="font-weight:700; color:#111827; margin-bottom:6px;">
                            {{ $displayName }}
                        </div>

                        @if(!empty($displaySubtitle))
                            <div style="font-size:14px; color:#57534e; margin-bottom:6px;">
                                {{ $displaySubtitle }}
                            </div>
                        @endif

                        <div style="font-size:14px; color:#57534e; line-height:1.6;">
                            Kiekis: {{ $item->quantity }}<br>
                            Kaina: {{ number_format($item->unit_price, 2, ',', ' ') }} €<br>
                            Suma: {{ number_format($item->line_total, 2, ',', ' ') }} €
                        </div>
                    </div>
                @endforeach

                <div style="margin-top:28px;">
                    <a href="{{ route('orders.show', $order->id) }}"
                       style="display:inline-block; padding:14px 22px; background:#111827; color:#ffffff; text-decoration:none; border-radius:12px; font-weight:700;">
                        Peržiūrėti užsakymą
                    </a>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>