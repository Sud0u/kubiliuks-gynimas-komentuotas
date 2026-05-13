@extends('layouts.app')

@section('title', 'Naujas slaptažodis – Kubiliuks')

@section('content')
<section class="py-14 lg:py-16 bg-stone-50">
    <div class="max-w-6xl mx-auto px-4 lg:px-0">
        <div class="flex justify-center">
            <div class="w-full max-w-md bg-white rounded-[28px] border border-black/10 shadow-sm p-7 lg:p-8">
                <div class="text-center">
                    <div class="text-[22px] font-extrabold tracking-tight text-stone-900">
                        <span class="text-emerald-700">Kubi</span><span>liuks</span>
                    </div>

                    <h1 class="mt-5 text-2xl font-bold text-stone-900">
                        Naujas slaptažodis
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-stone-500">
                        Įveskite naują slaptažodį.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div>
                        <label for="email" class="block text-sm font-semibold text-stone-800">El. paštas</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $request->email) }}"
                            required
                            autocomplete="email"
                            class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-600"
                        >

                        @error('email')
                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-stone-800">Naujas slaptažodis</label>

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
                                class="toggle-password absolute inset-y-0 right-0 px-4 flex items-center text-stone-500 hover:text-stone-800"
                                data-target="password"
                                aria-label="Rodyti slaptažodį"
                            >
                                <svg class="eye-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>

                                <svg class="eye-off-icon h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 10.587A2 2 0 0012 14a2 2 0 001.414-.586M9.88 4.24A9.9 9.9 0 0112 4c5 0 9.27 3.11 11 7.5a11.7 11.7 0 01-3.155 4.568M6.52 6.52A11.7 11.7 0 001 11.5C2.73 15.89 7 19 12 19c1.4 0 2.73-.24 3.96-.68" />
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-stone-800">Pakartokite slaptažodį</label>

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
                                class="toggle-password absolute inset-y-0 right-0 px-4 flex items-center text-stone-500 hover:text-stone-800"
                                data-target="password_confirmation"
                                aria-label="Rodyti slaptažodį"
                            >
                                <svg class="eye-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>

                                <svg class="eye-off-icon h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 10.587A2 2 0 0012 14a2 2 0 001.414-.586M9.88 4.24A9.9 9.9 0 0112 4c5 0 9.27 3.11 11 7.5a11.7 11.7 0 01-3.155 4.568M6.52 6.52A11.7 11.7 0 001 11.5C2.73 15.89 7 19 12 19c1.4 0 2.73-.24 3.96-.68" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center rounded-2xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800"
                    >
                        Pakeisti slaptažodį
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-password').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const eyeIcon = button.querySelector('.eye-icon');
            const eyeOffIcon = button.querySelector('.eye-off-icon');

            if (!input || !eyeIcon || !eyeOffIcon) {
                return;
            }

            const isPassword = input.getAttribute('type') === 'password';

            input.setAttribute('type', isPassword ? 'text' : 'password');
            eyeIcon.classList.toggle('hidden', isPassword);
            eyeOffIcon.classList.toggle('hidden', !isPassword);
        });
    });
});
</script>
@endsection