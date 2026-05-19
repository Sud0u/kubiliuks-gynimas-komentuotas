@extends('layouts.app')

@section('title', 'Registracija – Kubiliuks')

@section('content')
<section class="py-14 lg:py-16 bg-stone-50">
    <div class="max-w-6xl mx-auto px-4 lg:px-0">
        <div class="flex justify-center">
            <div class="w-full max-w-md bg-white rounded-[28px] border border-black/10 shadow-sm p-7 lg:p-8">
                <div class="flex items-center justify-center mb-6">
                    <div class="inline-flex items-center gap-3 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm">
                            <span class="relative block h-5 w-5">
                                <span class="absolute inset-y-0 left-0 w-[6px] rounded-full bg-emerald-600"></span>
                                <span class="absolute left-[8px] top-0 h-full w-[6px] rounded-full bg-stone-900"></span>
                                <span class="absolute right-0 top-[2px] h-[16px] w-[6px] rounded-full bg-emerald-400"></span>
                            </span>
                        </span>

                        <div class="leading-none">
                            <div class="text-[22px] font-extrabold tracking-tight text-stone-900">
                                <span class="text-emerald-700">Kubi</span><span>liuks</span>
                            </div>
                            <div class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-stone-400">
                                Registracija
                            </div>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div id="registerClientError" class="mt-4 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                {-- registracijos forma komentaro pradzia --}
{{-- Cia yra vartotojo registracijos forma. --}}
{{-- Vartotojas iveda varda, email, slaptazodi ir turi pazymeti taisykliu checkbox. --}}
{-- registracijos forma komentaro pabaiga --}
<form method="POST" action="{{ route('register') }}" class="space-y-4" id="registerForm" novalidate>
                    @csrf
                    {-- recaptcha registracijos puslapyje komentaro pradzia --}
                    {{-- Cia registracijos puslapis susijes su reCAPTCHA tokenu. --}}
                    {{-- Tokenas perduodamas i RegisteredUserController, kur serveris ji patikrina. --}}
                    {-- recaptcha registracijos puslapyje komentaro pabaiga --}
                    <input type="hidden" name="recaptcha_token" id="recaptchaTokenRegister">

                    <div>
                        <label for="name" class="block text-sm font-semibold text-stone-800">Vardas</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-600"
                        >
                        @error('name')
                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-stone-800">El. paštas</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-600"
                        >
                        @error('email')
                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-stone-800">Slaptažodis</label>

                        <div class="mt-1 relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="new-password"
                                class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 pr-12 text-sm outline-none focus:ring-2 focus:ring-emerald-600"
                            >

                            <button
                                type="button"
                                id="togglePassRegister"
                                class="absolute inset-y-0 right-0 px-4 flex items-center text-stone-500 hover:text-stone-700"
                                aria-label="Rodyti slaptažodį"
                            >
                                <svg id="eyeRegister" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeOffRegister" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 012.116-3.592m3.511-2.586A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.96 9.96 0 01-4.043 5.389M15 12a3 3 0 00-3-3m0 0a2.99 2.99 0 00-2.12.879M12 9v0m0 6a3 3 0 002.12-.879M3 3l18 18" />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-3 rounded-2xl border border-black/10 bg-stone-50 px-4 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-[12px] font-semibold uppercase tracking-[0.18em] text-stone-500">Slaptažodžio stiprumas</span>
                                <span id="passwordStrengthLabel" class="text-sm font-semibold text-stone-500">Neįvestas</span>
                            </div>

                            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-stone-200">
                                <div id="passwordStrengthBar" class="h-full w-0 rounded-full transition-all duration-300"></div>
                            </div>

                            <div class="mt-4 space-y-2 text-sm">
                                <div id="passwordRuleLength" class="flex items-center gap-2 text-stone-500">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-stone-300 text-[11px] font-bold">✓</span>
                                    <span>Naudokite bent 8 simbolius</span>
                                </div>

                                <div id="passwordRuleUppercase" class="flex items-center gap-2 text-stone-500">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-stone-300 text-[11px] font-bold">✓</span>
                                    <span>Naudokite bent 1 didžiąją raidę</span>
                                </div>

                                <div id="passwordRuleDigits" class="flex items-center gap-2 text-stone-500">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-stone-300 text-[11px] font-bold">✓</span>
                                    <span>Naudokite bent 1 skaičių</span>
                                </div>

                                <div id="passwordRuleSpecial" class="flex items-center gap-2 text-stone-500">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-stone-300 text-[11px] font-bold">✓</span>
                                    <span>Naudokite bent 1 specialų simbolį</span>
                                </div>
                            </div>
                        </div>

                        @error('password')
                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-stone-800">Pakartoti slaptažodį</label>

                        <div class="mt-1 relative">
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 pr-12 text-sm outline-none focus:ring-2 focus:ring-emerald-600"
                            >

                            <button
                                type="button"
                                id="togglePassRegister2"
                                class="absolute inset-y-0 right-0 px-4 flex items-center text-stone-500 hover:text-stone-700"
                                aria-label="Rodyti slaptažodį"
                            >
                                <svg id="eyeRegister2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeOffRegister2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 012.116-3.592m3.511-2.586A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.96 9.96 0 01-4.043 5.389M15 12a3 3 0 00-3-3m0 0a2.99 2.99 0 00-2.12.879M12 9v0m0 6a3 3 0 002.12-.879M3 3l18 18" />
                                </svg>
                            </button>
                        </div>

                        <div id="passwordMatchMessage" class="mt-2 hidden text-sm"></div>

                        @error('password_confirmation')
                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Patvirtinimas reikalingas tam, kad vartotojas prieš registraciją sutiktų su taisyklėmis. --}}
                    <div>
                        <label for="terms" class="flex items-start gap-3 rounded-2xl border border-black/10 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                            {-- taisykliu checkbox frontend puseje komentaro pradzia --}
                            {{-- Cia yra checkbox, kuri vartotojas privalo pazymeti pries registracija. --}}
                            {{-- Jei nepazymi, frontend rodo klaida, o backend vis tiek papildomai tikrina terms accepted. --}}
                            {-- taisykliu checkbox frontend puseje komentaro pabaiga --}
                            <input
                                id="terms"
                                name="terms"
                                type="checkbox"
                                value="1"
                                {{ old('terms') ? 'checked' : '' }}
                                required
                                class="mt-1 h-4 w-4 rounded border-stone-300 text-emerald-700 focus:ring-emerald-600"
                            >

                            <span>
                                Perskaičiau ir sutinku su
                                <a href="{{ route('terms') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-700 underline underline-offset-2 hover:text-emerald-800">taisyklėmis</a>
                                ir
                                <a href="{{ route('privacy') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-700 underline underline-offset-2 hover:text-emerald-800">privatumo politika</a>.
                            </span>
                        </label>

                        @error('terms')
                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center rounded-2xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-60"
                        id="registerSubmitBtn"
                    >
                        Registruotis
                    </button>

                    <p class="text-sm text-stone-600 text-center">
                        Jau turi paskyrą?
                        <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Prisijungti</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
@if($recaptchaEnabled && !empty($recaptchaSiteKey))
<script src="https://www.google.com/recaptcha/enterprise.js?render={{ $recaptchaSiteKey }}"></script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    function toggle(btnId, inputId, eyeId, eyeOffId) {
        const btn = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        const eye = document.getElementById(eyeId);
        const eyeOff = document.getElementById(eyeOffId);

        if (!btn || !input || !eye || !eyeOff) {
            return;
        }

        btn.addEventListener('click', function () {
            const isPass = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPass ? 'text' : 'password');
            eye.classList.toggle('hidden', !isPass);
            eyeOff.classList.toggle('hidden', isPass);
        });
    }

    toggle('togglePassRegister', 'password', 'eyeRegister', 'eyeOffRegister');
    toggle('togglePassRegister2', 'password_confirmation', 'eyeRegister2', 'eyeOffRegister2');

    const form = document.getElementById('registerForm');
    const tokenInput = document.getElementById('recaptchaTokenRegister');
    const submitBtn = document.getElementById('registerSubmitBtn');
    const errorBox = document.getElementById('registerClientError');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    const termsInput = document.getElementById('terms');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const passwordStrengthLabel = document.getElementById('passwordStrengthLabel');
    const passwordMatchMessage = document.getElementById('passwordMatchMessage');
    const ruleLength = document.getElementById('passwordRuleLength');
    const ruleUppercase = document.getElementById('passwordRuleUppercase');
    const ruleDigits = document.getElementById('passwordRuleDigits');
    const ruleSpecial = document.getElementById('passwordRuleSpecial');
    const recaptchaEnabled = @json($recaptchaEnabled);
    const siteKey = @json($recaptchaSiteKey);
    let isSubmitting = false;

    function showClientError(message) {
        if (!errorBox) {
            alert(message);
            return;
        }

        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    }

    function clearClientError() {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = '';
        errorBox.classList.add('hidden');
    }

    function isValidEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function hasUppercase(value) {
        return /[A-Z]/.test(value);
    }

    function hasAtLeastOneDigit(value) {
        return /\d/.test(value);
    }

    function hasSpecialCharacter(value) {
        return /[^A-Za-z0-9]/.test(value);
    }

    function updateRuleState(element, valid) {
        if (!element) {
            return;
        }

        element.classList.toggle('text-emerald-700', valid);
        element.classList.toggle('text-stone-500', !valid);

        const icon = element.querySelector('span');
        if (!icon) {
            return;
        }

        icon.classList.toggle('border-emerald-600', valid);
        icon.classList.toggle('bg-emerald-600', valid);
        icon.classList.toggle('text-white', valid);

        icon.classList.toggle('border-stone-300', !valid);
        icon.classList.toggle('bg-white', !valid);
        icon.classList.toggle('text-stone-500', !valid);
    }

    function updatePasswordStrength() {
        const password = passwordInput.value;

        const hasLength = password.length >= 8;
        const hasUpper = hasUppercase(password);
        const hasDigit = hasAtLeastOneDigit(password);
        const hasSpecial = hasSpecialCharacter(password);

        updateRuleState(ruleLength, hasLength);
        updateRuleState(ruleUppercase, hasUpper);
        updateRuleState(ruleDigits, hasDigit);
        updateRuleState(ruleSpecial, hasSpecial);

        const score = [hasLength, hasUpper, hasDigit, hasSpecial].filter(Boolean).length;

        passwordStrengthBar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-emerald-600');

        if (password.length === 0) {
            passwordStrengthBar.style.width = '0%';
            passwordStrengthLabel.textContent = 'Neįvestas';
            passwordStrengthLabel.className = 'text-sm font-semibold text-stone-500';
            return;
        }

        if (score <= 1) {
            passwordStrengthBar.style.width = '25%';
            passwordStrengthBar.classList.add('bg-red-500');
            passwordStrengthLabel.textContent = 'Silpnas';
            passwordStrengthLabel.className = 'text-sm font-semibold text-red-600';
            return;
        }

        if (score <= 3) {
            passwordStrengthBar.style.width = '70%';
            passwordStrengthBar.classList.add('bg-yellow-500');
            passwordStrengthLabel.textContent = 'Vidutinis';
            passwordStrengthLabel.className = 'text-sm font-semibold text-yellow-600';
            return;
        }

        passwordStrengthBar.style.width = '100%';
        passwordStrengthBar.classList.add('bg-emerald-600');
        passwordStrengthLabel.textContent = 'Stiprus';
        passwordStrengthLabel.className = 'text-sm font-semibold text-emerald-700';
    }

    function updatePasswordMatchState() {
        const password = passwordInput.value;
        const passwordConfirmation = passwordConfirmationInput.value;

        if (passwordConfirmation.length === 0) {
            passwordMatchMessage.classList.add('hidden');
            passwordMatchMessage.textContent = '';
            passwordMatchMessage.className = 'mt-2 hidden text-sm';
            return;
        }

        passwordMatchMessage.classList.remove('hidden');

        if (password === passwordConfirmation) {
            passwordMatchMessage.textContent = 'Slaptažodžiai sutampa.';
            passwordMatchMessage.className = 'mt-2 text-sm text-emerald-700';
            return;
        }

        passwordMatchMessage.textContent = 'Slaptažodžiai nesutampa.';
        passwordMatchMessage.className = 'mt-2 text-sm text-red-600';
    }

    passwordInput?.addEventListener('input', function () {
        updatePasswordStrength();
        updatePasswordMatchState();
        clearClientError();
    });

    passwordConfirmationInput?.addEventListener('input', function () {
        updatePasswordMatchState();
        clearClientError();
    });

    nameInput?.addEventListener('input', clearClientError);
    emailInput?.addEventListener('input', clearClientError);
    termsInput?.addEventListener('change', clearClientError);

    updatePasswordStrength();
    updatePasswordMatchState();

    form?.addEventListener('submit', function (e) {
        if (isSubmitting) {
            return;
        }

        clearClientError();

        const name = nameInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const passwordConfirmation = passwordConfirmationInput.value;
        const termsAccepted = termsInput ? termsInput.checked : false;

        if (name.length < 2) {
            e.preventDefault();
            showClientError('Įveskite vardą.');
            nameInput.focus();
            return;
        }

        if (email === '') {
            e.preventDefault();
            showClientError('Įveskite el. paštą.');
            emailInput.focus();
            return;
        }

        if (!isValidEmail(email)) {
            e.preventDefault();
            showClientError('Įveskite teisingą el. pašto adresą.');
            emailInput.focus();
            return;
        }

        if (password.trim() === '') {
            e.preventDefault();
            showClientError('Įveskite slaptažodį.');
            passwordInput.focus();
            return;
        }

        if (password.length < 8) {
            e.preventDefault();
            showClientError('Slaptažodis turi būti bent iš 8 simbolių.');
            passwordInput.focus();
            return;
        }

        if (!hasUppercase(password) || !hasAtLeastOneDigit(password) || !hasSpecialCharacter(password)) {
            e.preventDefault();
            showClientError('Slaptažodis turi turėti bent 1 didžiąją raidę, bent 1 skaičių ir bent 1 specialų simbolį.');
            passwordInput.focus();
            return;
        }

        if (passwordConfirmation.trim() === '') {
            e.preventDefault();
            showClientError('Pakartokite slaptažodį.');
            passwordConfirmationInput.focus();
            return;
        }

        if (password !== passwordConfirmation) {
            e.preventDefault();
            showClientError('Slaptažodžiai nesutampa.');
            passwordConfirmationInput.focus();
            return;
        }

        if (!termsAccepted) {
            e.preventDefault();
            showClientError('Norėdami užsiregistruoti, turite sutikti su taisyklėmis ir privatumo politika.');
            termsInput?.focus();
            return;
        }

        if (!recaptchaEnabled || !siteKey) {
            return;
        }

        e.preventDefault();
        submitBtn.disabled = true;

        if (typeof grecaptcha === 'undefined' || typeof grecaptcha.enterprise === 'undefined') {
            submitBtn.disabled = false;
            showClientError('Nepavyko įkelti Google saugumo patikros. Perkraukite puslapį ir bandykite dar kartą.');
            return;
        }

        grecaptcha.enterprise.ready(function () {
            grecaptcha.enterprise.execute(siteKey, { action: 'register' }).then(function (token) {
                if (!token) {
                    submitBtn.disabled = false;
                    showClientError('Google saugumo patikra nepavyko. Bandykite dar kartą.');
                    return;
                }

                tokenInput.value = token;
                isSubmitting = true;
                form.submit();
            }).catch(function () {
                submitBtn.disabled = false;
                showClientError('Nepavyko atlikti Google saugumo patikros. Bandykite dar kartą.');
            });
        });
    });
});
</script>
@endsection