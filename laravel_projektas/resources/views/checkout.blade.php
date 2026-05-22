@extends('layouts.app')

@section('title', 'Užsakymo pateikimas')

{-- checkout puslapis komentaro pradzia --}
{{-- Sitas failas yra vartotojo uzsakymo pateikimo puslapis. --}}
{{-- Cia yra forma, kur vartotojas iveda kontakta, adresa ir pasirenka mokejimo buda. --}}
{-- checkout puslapis komentaro pabaiga --}
@section('content')
<section class="py-8 sm:py-12 bg-stone-50">
    <div class="max-w-6xl mx-auto px-4 lg:px-0">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-stone-900">Užsakymo pateikimas</h1>
            </div>

            <a href="{{ route('cart') }}" class="text-sm text-stone-600 hover:text-stone-900 whitespace-nowrap">
                ← Grįžti į krepšelį
            </a>
        </div>

        @guest
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <div class="text-stone-900 font-semibold text-lg">Reikia prisijungti</div>
                <div class="text-stone-600 text-sm mt-2">
                    Kad galėtumėte pateikti užsakymą, reikia prisijungti arba susikurti paskyrą.
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}"
                       class="px-6 py-3 rounded-xl bg-stone-900 text-white font-semibold hover:bg-stone-800 transition">
                        Prisijungti
                    </a>

                    <a href="{{ route('register') }}"
                       class="px-6 py-3 rounded-xl border border-stone-300 text-stone-900 font-semibold hover:bg-stone-100 transition">
                        Registruotis
                    </a>
                </div>
            </div>
        @endguest

        @auth
            @php
                $u = auth()->user();
            @endphp

            <div id="msg" class="hidden mb-4 p-4 rounded-xl border text-sm"></div>

            <div id="checkoutSuccess" class="hidden bg-white rounded-3xl border border-emerald-200 p-6 sm:p-8 shadow-sm">
                <div class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700">
                    Užsakymas gautas
                </div>

                <h2 class="mt-4 text-2xl sm:text-3xl font-extrabold text-stone-900">
                    Ačiū, jūsų užsakymas sėkmingai pateiktas
                </h2>

                <p id="checkoutSuccessText" class="mt-3 max-w-2xl text-stone-600 leading-7">
                    Su jumis susisieksime dėl apmokėjimo ir pristatymo detalių.
                </p>

                <div class="mt-6 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-stone-500">Užsakymo numeris</div>
                        <div id="successOrderId" class="mt-2 text-xl font-extrabold text-stone-900">—</div>
                    </div>

                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-stone-500">Suma</div>
                        <div id="successOrderTotal" class="mt-2 text-xl font-extrabold text-stone-900">—</div>
                    </div>

                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-stone-500">Patvirtinimas išsiųstas į</div>
                        <div id="successOrderEmail" class="mt-2 text-base font-bold text-stone-900 break-all">—</div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <a id="successOrderLink"
                       href="{{ route('orders.index') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-stone-900 px-6 py-3 text-sm font-semibold text-white hover:bg-stone-800 transition">
                        Peržiūrėti užsakymą
                    </a>

                    <a href="{{ route('prekes') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-stone-300 px-6 py-3 text-sm font-semibold text-stone-900 hover:bg-stone-100 transition">
                        Toliau naršyti prekes
                    </a>
                </div>
            </div>

            <div id="checkoutGrid" class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl border border-stone-200 p-5 sm:p-6">
                        <div class="mb-5">
                            <div class="text-lg font-bold text-stone-900">Pirkėjo informacija</div>
                        </div>

                        {-- checkout forma komentaro pradzia --}
{{-- Cia prasideda forma, kurios duomenys veliau issiunciami i API. --}}
{{-- Backend puseje OrderController dar karta patikrina visus laukus. --}}
{-- checkout forma komentaro pabaiga --}
 {{-- checkout formos pradziaVartotojas čia įveda pristatymo duomenis ir pasirenka mokėjimo būdą. 
 Forma nėra siunčiama paprastu puslapio perkrovimu,
  nes apačioje JavaScript ją pagauna ir išsiunčia per API. --}}
<form id="checkoutForm" class="grid gap-4 md:grid-cols-2" novalidate>
                            <input
                                type="text"
                                name="website"
                                value=""
                                tabindex="-1"
                                autocomplete="off"
                                class="hidden"
                            >
                                                    {-- pasirenkamas payment metodas --}
                            <input type="hidden" name="payment_method" id="payment_method" value="cash_on_delivery">

                            <div class="md:col-span-2">
                                <label for="customer_name" class="text-sm font-medium text-stone-700">Vardas, pavardė</label>
                                <input
                                    id="customer_name"
                                    name="customer_name"
                                    value="{{ $u->name }}"
                                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-3 bg-stone-50 text-stone-900"
                                    readonly
                                >
                            </div>

                            <div>
                                <label for="customer_email" class="text-sm font-medium text-stone-700">El. paštas</label>
                                <input
                                    id="customer_email"
                                    name="customer_email"
                                    type="email"
                                    value="{{ $u->email }}"
                                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-3 bg-stone-50 text-stone-900"
                                    readonly
                                >
                            </div>

                            <div>
                                <label for="customer_phone" class="text-sm font-medium text-stone-700">Telefonas</label>
                                <input
                                    id="customer_phone"
                                    name="customer_phone"
                                    inputmode="tel"
                                    maxlength="12"
                                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-3 text-stone-900"
                                >
                                <div id="error_customer_phone" class="hidden mt-1 text-xs font-medium text-red-600"></div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="shipping_address" class="text-sm font-medium text-stone-700">Pristatymo adresas</label>
                                <input
                                    id="shipping_address"
                                    name="shipping_address"
                                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-3 text-stone-900"
                                >
                                <div id="error_shipping_address" class="hidden mt-1 text-xs font-medium text-red-600"></div>
                            </div>

                            <div>
                                <label for="shipping_city" class="text-sm font-medium text-stone-700">Miestas</label>
                                <input
                                    id="shipping_city"
                                    name="shipping_city"
                                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-3 text-stone-900"
                                >
                                <div id="error_shipping_city" class="hidden mt-1 text-xs font-medium text-red-600"></div>
                            </div>

                            <div>
                                <label for="shipping_postcode" class="text-sm font-medium text-stone-700">Pašto kodas</label>
                                <input
                                    id="shipping_postcode"
                                    name="shipping_postcode"
                                    inputmode="numeric"
                                    maxlength="5"
                                    class="mt-1 w-full rounded-xl border border-stone-200 px-4 py-3 text-stone-900"
                                >
                                <div id="error_shipping_postcode" class="hidden mt-1 text-xs font-medium text-red-600"></div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="shipping_country" class="text-sm font-medium text-stone-700">Šalis</label>
                                <input
                                    id="shipping_country"
                                    name="shipping_country"
                                    class="mt-1 w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-stone-900"
                                    value="Lietuva"
                                    readonly
                                >
                                <div id="error_shipping_country" class="hidden mt-1 text-xs font-medium text-red-600"></div>
                            </div>

                            <div class="md:col-span-2 pt-2 border-t border-stone-200">
                                <div class="text-lg font-bold text-stone-900 mb-3">Apmokėjimo būdas</div>

                                <div class="grid gap-3">
                                    <label
                                        class="payment-card cursor-pointer rounded-2xl border-2 border-emerald-500 bg-emerald-50 p-4 transition"
                                        data-payment-card="cash_on_delivery"
                                    >
                                        <div class="flex items-start gap-3">
                                            <input
                                                type="radio"
                                                name="payment_choice"
                                                class="mt-1 payment-choice"
                                                value="cash_on_delivery"
                                                checked
                                            >

                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-stone-900">Apmokėjimas vietoje</div>

                                                <div class="mt-1 text-sm text-stone-500">
                                                    Užsakymą pateiksite dabar, o dėl apmokėjimo susisieksime vėliau.
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <label
                                        class="payment-card cursor-pointer rounded-2xl border border-stone-200 bg-white p-4 transition"
                                        data-payment-card="paysera"
                                    >
                                        <div class="flex items-start gap-3">
                                            <input
                                                type="radio"
                                                name="payment_choice"
                                                class="mt-1 payment-choice"
                                                value="paysera"
                                            >

                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-stone-900">Banko pavedimu</div>

                                                <div class="mt-1 text-sm text-stone-500">
                                                    Užsakymą apmokėsite per saugų bankinio apmokėjimo langą.
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="md:col-span-2 flex flex-col sm:flex-row sm:items-center gap-3 pt-2">
                                <button
                                    id="submitBtn"
                                    type="submit"
                                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-stone-900 text-white font-semibold hover:bg-stone-800 transition"
                                >
                                    Pateikti užsakymą
                                </button>

                                <a href="{{ route('cart') }}" class="text-sm text-stone-600 hover:text-stone-900">
                                    ← Grįžti į krepšelį
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="bg-white rounded-2xl border border-stone-200 p-5">
                        <div class="text-lg font-bold text-stone-900">
                            Užsakymo suvestinė
                        </div>

                        <div id="summaryLoading" class="mt-4 text-sm text-stone-500">
                            Kraunama krepšelio informacija...
                        </div>

                        <div id="summaryError" class="hidden mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"></div>

                        <div id="summaryBox" class="hidden">
                            <div id="summaryItems" class="mt-4 space-y-3"></div>

                            <div class="mt-4 pt-4 border-t border-stone-200 space-y-2 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-stone-600">Prekių kiekis</span>
                                    <span id="summaryCount" class="font-semibold text-stone-900">0</span>
                                </div>

                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-stone-600">Pristatymas</span>
                                    <span class="font-semibold text-stone-900 text-right">Derinama atskirai</span>
                                </div>

                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-stone-600">Apmokėjimas</span>
                                    <span id="summaryPayment" class="font-semibold text-stone-900 text-right">Vietoje</span>
                                </div>

                                <div class="pt-3 border-t border-stone-200">
                                    <div class="flex items-center justify-between gap-3 text-base">
                                        <span class="font-semibold text-stone-900">Iš viso</span>
                                        <span id="summaryTotal" class="font-extrabold text-stone-900">0,00 €</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        @endauth
    </div>
</section>
@endsection

@section('scripts')
@auth
<script>
(() => {
    const apiBase = '/api/v1/cart';

    const msgBox = document.getElementById('msg');
    const summaryLoading = document.getElementById('summaryLoading');
    const summaryError = document.getElementById('summaryError');
    const summaryBox = document.getElementById('summaryBox');
    const summaryItems = document.getElementById('summaryItems');
    const summaryCount = document.getElementById('summaryCount');
    const summaryTotal = document.getElementById('summaryTotal');
    const summaryPayment = document.getElementById('summaryPayment');
    // checkout JavaScript forma komentaro pradzia
// Cia JavaScript pasiima checkout forma is puslapio.
// Veliau prie jos pridedamas submit veiksmas, kad uzsakymas butu siunciamas per API.
// checkout JavaScript forma komentaro pabaiga
const checkoutForm = document.getElementById('checkoutForm');
    const submitBtn = document.getElementById('submitBtn');
    const paymentMethodInput = document.getElementById('payment_method');
    const paymentChoices = document.querySelectorAll('.payment-choice');
    const paymentCards = document.querySelectorAll('[data-payment-card]');
    const checkoutGrid = document.getElementById('checkoutGrid');
    const checkoutSuccess = document.getElementById('checkoutSuccess');
    const checkoutSuccessText = document.getElementById('checkoutSuccessText');
    const successOrderId = document.getElementById('successOrderId');
    const successOrderTotal = document.getElementById('successOrderTotal');
    const successOrderEmail = document.getElementById('successOrderEmail');
    const successOrderLink = document.getElementById('successOrderLink');

    const customerPhone = document.getElementById('customer_phone');
    const shippingAddress = document.getElementById('shipping_address');
    const shippingCity = document.getElementById('shipping_city');
    const shippingPostcode = document.getElementById('shipping_postcode');
    const shippingCountry = document.getElementById('shipping_country');

    function money(v) {
        return new Intl.NumberFormat('lt-LT', {
            style: 'currency',
            currency: 'EUR'
        }).format(Number(v || 0));
    }

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function esc(s) {
        return String(s ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showMsg(text, ok = true) {
        if (!msgBox) return;

        msgBox.classList.remove('hidden');
        msgBox.classList.remove(
            'border-green-200', 'bg-green-50', 'text-green-900',
            'border-red-200', 'bg-red-50', 'text-red-900'
        );

        if (ok) {
            msgBox.classList.add('border-green-200', 'bg-green-50', 'text-green-900');
        } else {
            msgBox.classList.add('border-red-200', 'bg-red-50', 'text-red-900');
        }

        msgBox.innerHTML = text;
    }

    function clearSummaryError() {
        summaryError.classList.add('hidden');
        summaryError.textContent = '';
    }

    function setSummaryError(text) {
        summaryError.classList.remove('hidden');
        summaryError.textContent = text;
    }

    function hideSummaryBox() {
        summaryBox.classList.add('hidden');
    }

    function showSummaryBox() {
        summaryBox.classList.remove('hidden');
    }

    function showSuccessState(response) {
        const data = response?.data || {};
        const message = response?.message || 'Užsakymas gautas. Su jumis susisieksime dėl apmokėjimo ir pristatymo detalių.';

        successOrderId.textContent = data.id ? `#${data.id}` : '—';
        successOrderTotal.textContent = money(data.total_amount || 0);
        successOrderEmail.textContent = data.customer_email || '—';
        successOrderLink.href = data.redirect_url || '{{ route('orders.index') }}';
        checkoutSuccessText.textContent = message;

        checkoutGrid.classList.add('hidden');
        msgBox.classList.add('hidden');
        checkoutSuccess.classList.remove('hidden');

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function updatePaymentCards() {
        paymentCards.forEach(card => {
            card.classList.remove('border-emerald-500', 'bg-emerald-50');
            card.classList.add('border-stone-200', 'bg-white');
        });

        const selected = document.querySelector('.payment-choice:checked');

        if (selected) {
            const selectedCard = document.querySelector(`[data-payment-card="${selected.value}"]`);
            if (selectedCard) {
                selectedCard.classList.remove('border-stone-200', 'bg-white');
                selectedCard.classList.add('border-emerald-500', 'bg-emerald-50');
            }
        }
    }

    function updatePaymentSummary() {
        const selected = document.querySelector('.payment-choice:checked');

        if (!selected) {
            paymentMethodInput.value = 'cash_on_delivery';
            summaryPayment.textContent = 'Vietoje';
            return;
        }

        paymentMethodInput.value = selected.value;

        if (selected.value === 'cash_on_delivery') {
            summaryPayment.textContent = 'Vietoje';
        } else {
            summaryPayment.textContent = 'Banko pavedimu';
        }
    }

    function setFieldError(fieldId, text) {
        const input = document.getElementById(fieldId);
        const error = document.getElementById(`error_${fieldId}`);

        if (input) {
            input.classList.remove('border-stone-200');
            input.classList.add('border-red-400', 'bg-red-50');
        }

        if (error) {
            error.textContent = text;
            error.classList.remove('hidden');
        }
    }

    function clearFieldError(fieldId) {
        const input = document.getElementById(fieldId);
        const error = document.getElementById(`error_${fieldId}`);

        if (input) {
            input.classList.remove('border-red-400', 'bg-red-50');
            input.classList.add('border-stone-200');
        }

        if (error) {
            error.textContent = '';
            error.classList.add('hidden');
        }
    }

    function clearAllFieldErrors() {
        [
            'customer_phone',
            'shipping_address',
            'shipping_city',
            'shipping_postcode',
            'shipping_country',
        ].forEach(clearFieldError);
    }

    // checkout frontend validacija komentaro pradzia
    // Šita vieta tikrina laukus dar prieš siunčiant į backend. Backend tikrinimas vis tiek lieka OrderController faile.
    function validatePhone(phone) {
        return /^(\+3706\d{7}|86\d{7})$/.test(phone);
    }

    // Pašto kodas turi būti 5 skaitmenys.
    function validatePostcode(postcode) {
        return /^\d{5}$/.test(postcode);
    }

    function validateCity(city) {
        return /^[A-Za-zĄČĘĖĮŠŲŪŽąčęėįšųūž\s\-]{2,50}$/.test(city);
    }

    // Šalis užrakinta į Lietuvą, nes šitam projekte pristatymas numatytas Lietuvoje.
    function validateCountry(country) {
        return country === 'Lietuva';
    }

    function validateAddress(address) {
        return /^(?=.*[A-Za-zĄČĘĖĮŠŲŪŽąčęėįšųūž])(?=.*\d)[A-Za-zĄČĘĖĮŠŲŪŽąčęėįšųūž0-9\s\-.,/]{5,100}$/.test(address);
    }

    function sanitizePhoneInput(value) {
        let cleaned = value.replace(/[^\d+]/g, '');

        if (cleaned.includes('+')) {
            cleaned = (cleaned.startsWith('+') ? '+' : '') + cleaned.replace(/\+/g, '').replace(/^\+/, '');
        }

        if (cleaned.startsWith('+')) {
            return '+' + cleaned.slice(1).replace(/\D/g, '').slice(0, 11);
        }

        return cleaned.replace(/\D/g, '').slice(0, 9);
    }

    function sanitizePostcodeInput(value) {
        return value.replace(/\D/g, '').slice(0, 5);
    }

    function validateForm() {
        clearAllFieldErrors();

        let valid = true;

        const phone = customerPhone.value.trim();
        const address = shippingAddress.value.trim();
        const city = shippingCity.value.trim();
        const postcode = shippingPostcode.value.trim();
        const country = shippingCountry.value.trim();

        if (!phone) {
            setFieldError('customer_phone', 'Įveskite telefono numerį.');
            valid = false;
        } else if (!validatePhone(phone)) {
            setFieldError('customer_phone', 'Įveskite teisingą lietuvišką telefono numerį.');
            valid = false;
        }

        if (!address) {
            setFieldError('shipping_address', 'Įveskite pristatymo adresą.');
            valid = false;
        } else if (!validateAddress(address)) {
            setFieldError('shipping_address', 'Įveskite gatvę ir namo numerį.');
            valid = false;
        }

        if (!city) {
            setFieldError('shipping_city', 'Įveskite miestą.');
            valid = false;
        } else if (!validateCity(city)) {
            setFieldError('shipping_city', 'Miesto pavadinimas gali turėti tik raides.');
            valid = false;
        }

        if (!postcode) {
            setFieldError('shipping_postcode', 'Įveskite pašto kodą.');
            valid = false;
        } else if (!validatePostcode(postcode)) {
            setFieldError('shipping_postcode', 'Pašto kodas turi būti 5 skaitmenys.');
            valid = false;
        }

        if (!country) {
            setFieldError('shipping_country', 'Įveskite šalį.');
            valid = false;
        } else if (!validateCountry(country)) {
            setFieldError('shipping_country', 'Šalis turi būti Lietuva.');
            valid = false;
        }

        if (!valid) {
            showMsg('Patikrinkite užsakymo formą.', false);
        }

        return valid;
    }

    // checkout frontend validacija komentaro pabaiga

    // checkout async funkcija komentaro pradzia
// Cia prasideda async funkcija, nes reikia laukti serverio atsakymo.
// Uzsakymo pateikimas vyksta fone be papildomo rankinio perkrovimo.
// checkout async funkcija komentaro pabaiga
async function api(url, method = 'GET', body = null) {
        const headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
        };

        const options = {
            method,
            headers,
            credentials: 'same-origin',
        };

        if (body !== null) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(body);
        }

        // uzsakymo siuntimas i serveri komentaro pradzia
        // Cia checkout duomenys issiunciami i Laravel API.
        // Serveris tada sukuria uzsakyma arba grazina validacijos klaidas.
        // uzsakymo siuntimas i serveri komentaro pabaiga
        const response = await fetch(url, options);

        let data = null;
        try {
            data = await response.json();
        } catch (_) {}

        if (!response.ok) {
            const message = data?.message || data?.error || `Klaida (${response.status})`;
            throw new Error(message);
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
            const qty = Number(it.quantity ?? it.qty ?? it.kiekis ?? 1);
            const name =
                it.name ??
                it.product_name ??
                product?.name ??
                product?.title ??
                'Prekė';

            let subtitle = '';

            if (it.subtitle) {
                subtitle = it.subtitle;
            } else if (it.meta && it.meta.size_label && it.meta.inside_label && it.meta.wood_label) {
                subtitle = `${it.meta.size_label} · ${it.meta.inside_label} · ${it.meta.wood_label}`;
            }

            const price = Number(it.price ?? it.unit_price ?? product?.price ?? 0);

            return {
                name,
                subtitle,
                qty,
                price,
                subtotal: price * qty,
            };
        });
    }

    function renderSummary(items) {
        if (!items.length) {
            summaryItems.innerHTML = `
                <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                    Krepšelis tuščias.
                </div>
            `;
            summaryCount.textContent = '0';
            summaryTotal.textContent = money(0);
            return;
        }

        let count = 0;
        let total = 0;

        summaryItems.innerHTML = items.map((item) => {
            count += item.qty;
            total += item.subtotal;

            return `
                <div class="rounded-xl border border-stone-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-stone-900">${esc(item.name)}</div>
                            ${item.subtitle ? `<div class="mt-1 text-sm text-stone-500">${esc(item.subtitle)}</div>` : ''}
                            <div class="mt-1 text-sm text-stone-500">${item.qty} vnt. × ${money(item.price)}</div>
                        </div>
                        <div class="font-bold text-stone-900 whitespace-nowrap">${money(item.subtotal)}</div>
                    </div>
                </div>
            `;
        }).join('');

        summaryCount.textContent = count;
        summaryTotal.textContent = money(total);
    }

    async function loadSummary() {
        summaryLoading.classList.remove('hidden');
        hideSummaryBox();
        clearSummaryError();

        try {
            const data = await api(apiBase);
            const root = getRoot(data);
            const items = normalizeItems(root);

            renderSummary(items);
            updatePaymentSummary();

            summaryLoading.classList.add('hidden');
            showSummaryBox();
        } catch (error) {
            summaryLoading.classList.add('hidden');
            hideSummaryBox();
            setSummaryError(error.message || 'Nepavyko gauti krepšelio informacijos.');
        }
    }

    paymentChoices.forEach((choice) => {
        choice.addEventListener('change', function () {
            updatePaymentCards();
            updatePaymentSummary();
        });
    });

    customerPhone.addEventListener('input', function () {
        this.value = sanitizePhoneInput(this.value);
        clearFieldError(this.id);
    });

    shippingPostcode.addEventListener('input', function () {
        this.value = sanitizePostcodeInput(this.value);
        clearFieldError(this.id);
    });

    [shippingAddress, shippingCity, shippingCountry].forEach((input) => {
        if (!input) return;

        input.addEventListener('input', () => {
            clearFieldError(input.id);
        });
    });

    updatePaymentCards();
    updatePaymentSummary();
    loadSummary();

    // checkout formos uzpildymas siunciamas duomenys siunciami per api.
    checkoutForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!validateForm()) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        showMsg('Vykdomas užsakymo pateikimas...', true);
        
                            // surenkami uzsakovo duomenys
        const payload = {
            website: checkoutForm.website.value.trim(),
            customer_name: checkoutForm.customer_name.value.trim(),
            customer_phone: checkoutForm.customer_phone.value.trim(),
            shipping_address: checkoutForm.shipping_address.value.trim(),
            shipping_city: checkoutForm.shipping_city.value.trim(),
            shipping_postcode: checkoutForm.shipping_postcode.value.trim(),
            shipping_country: checkoutForm.shipping_country.value.trim(),
            payment_method: paymentMethodInput.value,
        };

        try {
            const response = await api('/api/v1/orders', 'POST', payload);

            // Jei backend grąžina Paysera redirect_url, klientas nukreipiamas į mokėjimo langą.
            if (response?.data?.payment_method === 'paysera' && response?.data?.redirect_url) {
                showMsg('Pereinama prie apmokėjimo...', true);
                window.location.href = response.data.redirect_url;
                return;
            }

            showSuccessState(response);
        } catch (error) {
            showMsg(error.message || 'Nepavyko pateikti užsakymo.', false);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
        }
    });
    // checkout formos siuntimas komentaro pabaiga
})();
</script>
@endauth
@endsection