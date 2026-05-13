@extends('layouts.app')

@section('title', 'Prisijungimas – Kubiliuks')

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
                                Prisijungimas
                            </div>
                        </div>
                    </div>
                </div>

                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div id="loginClientError" class="mt-4 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4" id="loginForm" novalidate>
                    @csrf
                    <input type="hidden" name="recaptcha_token" id="recaptchaTokenLogin">

                    <div>
                        <label for="email" class="block text-sm font-semibold text-stone-800">El. paštas</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
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
                                autocomplete="current-password"
                                class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 pr-12 text-sm outline-none focus:ring-2 focus:ring-emerald-600"
                            >

                            <button
                                type="button"
                                id="togglePassLogin"
                                class="absolute inset-y-0 right-0 px-4 flex items-center text-stone-500 hover:text-stone-700"
                                aria-label="Rodyti slaptažodį"
                            >
                                <svg id="eyeLogin" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeOffLogin" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 012.116-3.592m3.511-2.586A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.96 9.96 0 01-4.043 5.389M15 12a3 3 0 00-3-3m0 0a2.99 2.99 0 00-2.12.879M12 9v0m0 6a3 3 0 002.12-.879M3 3l18 18" />
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <label class="inline-flex items-center gap-2 text-sm text-stone-700 select-none">
                            <input type="checkbox" name="remember" class="rounded border-black/10 text-emerald-700 focus:ring-emerald-600">
                            Prisiminti
                        </label>

                        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                            Pamiršai slaptažodį?
                        </a>
                    </div>

                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center rounded-2xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-60"
                        id="loginSubmitBtn"
                    >
                        Prisijungti
                    </button>

                    <p class="text-sm text-stone-600 text-center">
                        Neturi paskyros?
                        <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Registruotis</a>
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
    const btn = document.getElementById('togglePassLogin');
    const input = document.getElementById('password');
    const eye = document.getElementById('eyeLogin');
    const eyeOff = document.getElementById('eyeOffLogin');

    if (btn && input && eye && eyeOff) {
        btn.addEventListener('click', function () {
            const isPass = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPass ? 'text' : 'password');
            eye.classList.toggle('hidden', !isPass);
            eyeOff.classList.toggle('hidden', isPass);
        });
    }

    const form = document.getElementById('loginForm');
    const tokenInput = document.getElementById('recaptchaTokenLogin');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const errorBox = document.getElementById('loginClientError');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
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

    if (!form || !tokenInput || !submitBtn || !emailInput || !passwordInput) {
        return;
    }

    form.addEventListener('submit', function (e) {
        if (isSubmitting) {
            return;
        }

        clearClientError();

        const email = emailInput.value.trim();
        const password = passwordInput.value;

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
            grecaptcha.enterprise.execute(siteKey, { action: 'login' }).then(function (token) {
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