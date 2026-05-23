<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PayseraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly CartService $cart,
        private readonly PayseraService $paysera
    ) {}

    // checkout ir užsakymo sukūrimas komentaro pradzia
    ///////////// Šitas metodas gauna checkout formą, patikrina duomenis ir sukuria užsakymą.
    public function store(Request $request)
    {
        // patikrinam useri
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Reikia prisijungti.',
            ], 401);
        }

        //Validacijos pradzia  backend validacija būtina, nes frontend validaciją žmogus gali apeiti per naršyklę.
        $validator = Validator::make($request->all(), [
            'website' => ['nullable', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            // Leidžiamas tik lietuviškas telefono formatas.
            'customer_phone' => ['required', 'string', 'regex:/^(\+3706\d{7}|86\d{7})$/'],
            'shipping_address' => ['required', 'string', 'min:5', 'max:255', 'regex:/^(?=.*[A-Za-zĄČĘĖĮŠŲŪŽąćęėįšųūž])(?=.*\d)[A-Za-zĄČĘĖĮŠŲŪŽąćęėįšųūž0-9\s\-.,\/]+$/u'],
            'shipping_city' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[A-Za-zĄČĘĖĮŠŲŪŽąćęėįšųūž\s\-]+$/u'],
            // Lietuvos pašto kodui paliktas 5 skaitmenų formatas.
            'shipping_postcode' => ['required', 'string', 'regex:/^\d{5}$/'],
            // Projekte pristatymas apribotas Lietuvai, todėl šalies pakeisti neleidžiama.
            'shipping_country' => ['required', 'string', 'in:Lietuva'],
            'payment_method' => ['required', 'in:cash_on_delivery,paysera'],
        ], [
            'customer_name.required' => 'Nurodykite vardą ir pavardę.',
            'customer_phone.required' => 'Nurodykite telefono numerį.',
            'customer_phone.regex' => 'Įveskite teisingą lietuvišką telefono numerį.',
            'shipping_address.required' => 'Nurodykite pristatymo adresą.',
            'shipping_address.min' => 'Pristatymo adresas per trumpas.',
            'shipping_address.regex' => 'Pristatymo adrese turi būti gatvė ir namo numeris.',
            'shipping_city.required' => 'Nurodykite miestą.',
            'shipping_city.regex' => 'Miesto pavadinime galima naudoti tik raides.',
            'shipping_postcode.required' => 'Nurodykite pašto kodą.',
            'shipping_postcode.regex' => 'Pašto kodas turi būti 5 skaitmenys.',
            'shipping_country.required' => 'Nurodykite šalį.',
            'shipping_country.in' => 'Šalis turi būti Lietuva.',
            'payment_method.required' => 'Pasirinkite apmokėjimo būdą.',
            'payment_method.in' => 'Pasirinktas netinkamas apmokėjimo būdas.',
        ]);

        // validacijos klaidos grazinimas komentaro pradzia
        // Jei validacija nepraeina, cia grazinama pirma klaida ir visi error laukeliai.
        // Frontend gali parodyti zinute vartotojui checkout puslapyje.
        // validacijos klaidos grazinimas komentaro pabaiga
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Paslėptas website laukelis yra paprastas botų filtras. Žmogus jo nemato, botai dažnai užpildo.
        // pasleptas botu laukelis komentaro pradzia
        // Website laukelio zmogus nemato, bet botai ji kartais uzpildo.
        // Jei jis uzpildytas, sistema uzsakymo nepriima.
        // pasleptas botu laukelis komentaro pabaiga
        if (!empty($data['website'])) {
            return response()->json([
                'message' => 'Nepavyko pateikti užsakymo.',
            ], 422);
        }

        // Paysera konfiguracijos patikrinimas komentaro pradzia
        // Čia patikrinama, ar Paysera sukonfigūruota. Jeigu vartotojas pasirinko Paysera, 
        // bet nėra projekto ID arba slaptažodžio, sistema neleis pereiti į neveikiantį mokėjimą.
        if (($data['payment_method'] ?? '') === 'paysera' && !$this->paysera->isConfigured()) {
            return response()->json([
                'message' => 'Mokėjimas banko pavedimu dar nesukonfigūruotas. Užpildykite Paysera duomenis .env faile.',
            ], 422);
        }

        $data['customer_email'] = (string) $user->email;
        $data['customer_phone'] = preg_replace('/\s+/', '', (string) $data['customer_phone']);
        $data['shipping_postcode'] = preg_replace('/\D+/', '', (string) $data['shipping_postcode']);
        $data['shipping_address'] = trim((string) $data['shipping_address']);
        $data['shipping_city'] = trim((string) $data['shipping_city']);
        // Net jei kas nors bandytų pakeisti šalį per HTML, serveryje vėl nustatoma Lietuva.
        $data['shipping_country'] = 'Lietuva';

       // Čia jau patikrinti checkout duomenys perduodami į OrderService. 
       //////////// Controlleris pats nekuria visos užsakymo logikos, jis tik priima, patikrina ir perduoda darbą servisui
        $order = $this->orders->createFromCart(
            $data,
            $data['payment_method'] ?? null
        );

        $this->cart->clear();

        $redirectUrl = route('orders.show', $order->id);
        $message = 'Užsakymas gautas. Su jumis susisieksime dėl apmokėjimo ir pristatymo detalių.';

        // Jei pasirinkta Paysera, klientas nukreipiamas į Paysera mokėjimo langą.
        // nukreipimas i Paysera komentaro pradzia
        // Jei pasirinktas Paysera mokejimas, cia sugeneruojama Paysera nuoroda.
        // Frontend gaus redirect_url ir nukreips vartotoja i mokejimo langa.
        // nukreipimas i Paysera komentaro pabaiga
        if (($data['payment_method'] ?? '') === 'paysera') {
            $redirectUrl = $this->paysera->buildCheckoutUrl($order);
            $message = 'Užsakymas sukurtas, bet bus patvirtintas tik po sėkmingo Paysera apmokėjimo.';
        }

        // Frontend gauna redirect_url ir pagal jį arba rodo užsakymą, arba nukreipia į Paysera.
        return response()->json([
            'message' => $message,
            'data' => [
                'id' => (int) $order->id,
                'status' => $order->status,
                'total_amount' => (float) $order->total_amount,
                'redirect_url' => $redirectUrl,
                'customer_email' => $order->customer_email,
                'payment_method' => $data['payment_method'],
            ],
        ], 201);
    }

    // checkout ir užsakymo sukūrimas komentaro pabaiga

    // vartotojo uzsakymu sarasas komentaro pradzia
    // Sitas metodas grazina prisijungusio vartotojo uzsakymus.
    // Vartotojas mato tik savo uzsakymus, nes filtruojama pagal Auth::id().
    // vartotojo uzsakymu sarasas komentaro pabaiga
    public function index(Request $request)
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json(['data' => []]);
        }

        $orders = Order::query()
            ->where('user_id', $userId)
            ->latest()
            ->get(['id', 'total_amount', 'status', 'created_at']);

        return response()->json(['data' => $orders]);
    }

    // vieno uzsakymo parodymas komentaro pradzia
    // Cia parodomas konkretus vartotojo uzsakymas su prekemis ir payment informacija.
    // Jei uzsakymas nepriklauso vartotojui, jis nerodomas.
    // vieno uzsakymo parodymas komentaro pabaiga
    public function show(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Reikia prisijungti.',
            ], 401);
        }

        $query = Order::query()->with([
            'items.product:id,name,slug,image',
            'payment:id,order_id,provider,status,transaction_id,amount,paid_at,meta',
        ]);

        if (!$user->is_admin) {
            $query->where('user_id', $user->id);
        }

        $order = $query->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => (int) $order->id,
                'status' => $order->status,
                'total_amount' => (float) $order->total_amount,
                'created_at' => optional($order->created_at)->toISOString(),
                'paid_at' => optional($order->paid_at)->toISOString(),
                'payment' => $order->payment ? [
                    'provider' => $order->payment->provider,
                    'status' => $order->payment->status,
                    'transaction_id' => $order->payment->transaction_id,
                    'paid_at' => optional($order->payment->paid_at)->toISOString(),
                    'meta' => $order->payment->meta,
                ] : null,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => (int) $item->id,
                        'product_id' => (int) $item->product_id,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'line_total' => (float) $item->line_total,
                        'product' => $item->product ? [
                            'id' => (int) $item->product->id,
                            'name' => $item->product->name,
                            'slug' => $item->product->slug,
                            'image' => $item->product->image,
                        ] : null,
                    ];
                })->values(),
            ],
        ]);
    }

    // vartotojo uzsakymo atsaukimas komentaro pradzia
    // Cia vartotojas gali atsaukti savo uzsakyma, jei jis dar pending ir neapmoketas.
    // Atsaukiant paprastu prekiu likuciai grazinami atgal i sandeli.
    // vartotojo uzsakymo atsaukimas komentaro pabaiga
    public function cancel(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Reikia prisijungti.',
            ], 401);
        }

        $order = Order::query()
            ->with(['items', 'payment'])
            ->findOrFail($id);

        if ((int) $order->user_id !== (int) $user->id && !$user->is_admin) {
            return response()->json([
                'message' => 'Neturi prieigos prie šio užsakymo.',
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Užsakymo atšaukti nebegalima.',
            ], 422);
        }

        // Atšaukimas daromas transakcijoje, kad kartu pasikeistų ir užsakymas, ir sandėlio likutis.
        // atsaukimo transakcija komentaro pradzia
        // Cia atsaukimas vykdomas transakcijoje.
        // Tai reiskia, kad statuso keitimas ir likuciu grazinimas vyksta kartu.
        // atsaukimo transakcija komentaro pabaiga
        DB::transaction(function () use ($order) {
            $productIds = $order->items
                ->pluck('product_id')
                ->map(fn ($value) => (int) $value)
                ->values()
                ->all();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($order->items as $item) {
                $product = $products->get((int) $item->product_id);

                if (!$product) {
                    continue;
                }

                $product->stock = (int) $product->stock + (int) $item->quantity;
                $product->save();
            }

            $order->status = 'cancelled';
            $order->save();

            if ($order->payment && $order->payment->status === 'unpaid') {
                $meta = $order->payment->meta ?? [];
                $meta['note'] = 'Užsakymas atšauktas. Sandėlio rezervacija panaikinta.';

                $order->payment->update([
                    'status' => 'cancelled',
                    'meta' => $meta,
                ]);
            }
        });

        $order->refresh();

        return response()->json([
            'message' => 'Užsakymas atšauktas.',
            'data' => [
                'id' => (int) $order->id,
                'status' => $order->status,
            ],
        ]);
    }
}
