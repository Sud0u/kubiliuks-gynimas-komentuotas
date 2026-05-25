<div id="cookieBanner" class="hidden fixed inset-x-3 bottom-3 z-[9999] sm:left-4 sm:right-auto sm:max-w-xl">
    <div class="rounded-2xl border border-black/10 bg-white p-4 shadow-2xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="text-sm font-bold text-stone-900">Slapukai</div>
                <p class="mt-1 text-sm leading-5 text-stone-600">
                    Naudojame būtinuosius slapukus. Kitus galite valdyti.
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap gap-2">
                <button type="button" id="cookieBannerSettings" class="rounded-full border border-black/10 px-4 py-2 text-sm font-semibold text-stone-800 transition hover:bg-stone-100">
                    Nustatymai
                </button>

                <button type="button" id="cookieBannerReject" class="rounded-full border border-black/10 px-4 py-2 text-sm font-semibold text-stone-800 transition hover:bg-stone-100">
                    Atmesti
                </button>

                <button type="button" id="cookieBannerAccept" class="rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-700">
                    Priimti
                </button>
            </div>
        </div>
    </div>
</div>

<button type="button" id="cookieFloatingButton" class="hidden fixed bottom-4 left-4 z-[9998] h-12 w-12 items-center justify-center rounded-full bg-stone-900 text-xl text-white shadow-xl transition hover:bg-stone-700">
    🍪
</button>

<div id="cookiePanel" class="hidden fixed bottom-20 left-4 z-[9999] w-[calc(100%-2rem)] max-w-sm rounded-2xl border border-black/10 bg-white shadow-2xl">
    <div class="flex items-center justify-between border-b border-black/10 px-5 py-4">
        <h2 class="text-base font-bold text-stone-900">Slapukų nustatymai</h2>

        <button type="button" id="cookieCloseSettings" class="text-2xl leading-none text-stone-500 transition hover:text-stone-900">
            ×
        </button>
    </div>

    <div class="space-y-3 px-5 py-4">
        <div class="rounded-xl border border-black/10 p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-stone-900">Būtini</h3>
                    <p class="mt-1 text-sm text-stone-500">Svetainės veikimui</p>
                </div>

                <span class="rounded-full bg-stone-900 px-3 py-1 text-xs font-semibold text-white">
                    Būtina
                </span>
            </div>

            <button type="button" class="cookie-info-toggle mt-3 text-sm font-semibold text-stone-500 hover:text-stone-900">
                Plačiau ↓
            </button>

            <p class="cookie-info hidden mt-2 text-sm leading-5 text-stone-500">
                Reikalingi krepšeliui, prisijungimui ir užsakymo pateikimui.
            </p>
        </div>

        <div class="rounded-xl border border-black/10 p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-stone-900">Statistika</h3>
                    <p class="mt-1 text-sm text-stone-500">Svetainės gerinimui</p>
                </div>

                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" id="cookieAnalytics" class="peer sr-only">
                    <span class="h-6 w-11 rounded-full bg-stone-300 transition peer-checked:bg-stone-900"></span>
                    <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                </label>
            </div>

            <button type="button" class="cookie-info-toggle mt-3 text-sm font-semibold text-stone-500 hover:text-stone-900">
                Plačiau ↓
            </button>

            <p class="cookie-info hidden mt-2 text-sm leading-5 text-stone-500">
                Leidžia matyti bendrą svetainės naudojimą.
            </p>
        </div>

        <div class="rounded-xl border border-black/10 p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-stone-900">Rinkodara</h3>
                    <p class="mt-1 text-sm text-stone-500">Reklamos nustatymams</p>
                </div>

                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" id="cookieMarketing" class="peer sr-only">
                    <span class="h-6 w-11 rounded-full bg-stone-300 transition peer-checked:bg-stone-900"></span>
                    <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                </label>
            </div>

            <button type="button" class="cookie-info-toggle mt-3 text-sm font-semibold text-stone-500 hover:text-stone-900">
                Plačiau ↓
            </button>

            <p class="cookie-info hidden mt-2 text-sm leading-5 text-stone-500">
                Naudojama tik jei ateityje bus įjungta reklama.
            </p>
        </div>
    </div>

    <div class="flex flex-wrap justify-end gap-2 border-t border-black/10 px-5 py-4">
        <button type="button" id="cookieRejectAll" class="rounded-full border border-black/10 px-4 py-2 text-sm font-semibold text-stone-800 transition hover:bg-stone-100">
            Atmesti
        </button>

        <button type="button" id="cookieSaveSettings" class="rounded-full border border-black/10 px-4 py-2 text-sm font-semibold text-stone-800 transition hover:bg-stone-100">
            Išsaugoti
        </button>

        <button type="button" id="cookieAcceptAll" class="rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-700">
            Priimti
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const consentKey = 'kubiliuks_cookie_consent';
        const cookieDays = 180;

        const banner = document.getElementById('cookieBanner');
        const panel = document.getElementById('cookiePanel');
        const floatingButton = document.getElementById('cookieFloatingButton');

        const analyticsInput = document.getElementById('cookieAnalytics');
        const marketingInput = document.getElementById('cookieMarketing');

        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + date.toUTCString() + '; path=/; SameSite=Lax';
        }

        function getCookie(name) {
            const value = '; ' + document.cookie;
            const parts = value.split('; ' + name + '=');

            if (parts.length === 2) {
                return decodeURIComponent(parts.pop().split(';').shift());
            }

            return null;
        }

        function getConsent() {
            const saved = getCookie(consentKey) || localStorage.getItem(consentKey);

            if (!saved) {
                return null;
            }

            try {
                return JSON.parse(saved);
            } catch (e) {
                return null;
            }
        }

        function applyConsent(settings) {
            window.KubiliuksCookies = {
                necessary: true,
                analytics: Boolean(settings.analytics),
                marketing: Boolean(settings.marketing),
                has: function (category) {
                    return Boolean(this[category]);
                }
            };

            document.querySelectorAll('script[type="text/plain"][data-cookie-category]').forEach(function (script) {
                const category = script.getAttribute('data-cookie-category');

                if (!window.KubiliuksCookies.has(category)) {
                    return;
                }

                const newScript = document.createElement('script');

                if (script.dataset.src) {
                    newScript.src = script.dataset.src;
                }

                newScript.text = script.textContent;
                document.head.appendChild(newScript);
                script.remove();
            });

            window.dispatchEvent(new CustomEvent('kubiliuks:cookies-ready', {
                detail: window.KubiliuksCookies
            }));
        }

        function saveConsent(settings) {
            const consent = {
                necessary: true,
                analytics: Boolean(settings.analytics),
                marketing: Boolean(settings.marketing),
                saved_at: new Date().toISOString()
            };

            const encoded = JSON.stringify(consent);

            localStorage.setItem(consentKey, encoded);
            setCookie(consentKey, encoded, cookieDays);

            applyConsent(consent);
            banner.classList.add('hidden');
            panel.classList.add('hidden');
            floatingButton.classList.remove('hidden');
            floatingButton.classList.add('flex');
        }

        function openSettings() {
            const consent = getConsent();

            analyticsInput.checked = consent ? Boolean(consent.analytics) : false;
            marketingInput.checked = consent ? Boolean(consent.marketing) : false;

            banner.classList.add('hidden');
            panel.classList.remove('hidden');
        }

        const currentConsent = getConsent();

        if (currentConsent) {
            applyConsent(currentConsent);
            floatingButton.classList.remove('hidden');
            floatingButton.classList.add('flex');
        } else {
            banner.classList.remove('hidden');
        }

        document.getElementById('cookieBannerAccept').addEventListener('click', function () {
            saveConsent({ analytics: true, marketing: true });
        });

        document.getElementById('cookieBannerReject').addEventListener('click', function () {
            saveConsent({ analytics: false, marketing: false });
        });

        document.getElementById('cookieBannerSettings').addEventListener('click', openSettings);
        floatingButton.addEventListener('click', openSettings);

        document.getElementById('cookieCloseSettings').addEventListener('click', function () {
            panel.classList.add('hidden');

            if (!getConsent()) {
                banner.classList.remove('hidden');
            }
        });

        document.getElementById('cookieAcceptAll').addEventListener('click', function () {
            saveConsent({ analytics: true, marketing: true });
        });

        document.getElementById('cookieRejectAll').addEventListener('click', function () {
            saveConsent({ analytics: false, marketing: false });
        });

        document.getElementById('cookieSaveSettings').addEventListener('click', function () {
            saveConsent({
                analytics: analyticsInput.checked,
                marketing: marketingInput.checked
            });
        });

        document.querySelectorAll('.cookie-info-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const info = button.nextElementSibling;

                info.classList.toggle('hidden');
                button.textContent = info.classList.contains('hidden') ? 'Plačiau ↓' : 'Mažiau ↑';
            });
        });
    });
</script>