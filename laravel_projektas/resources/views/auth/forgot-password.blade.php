@extends('layouts.app')

@section('title', 'Slaptažodžio atkūrimas – Kubiliuks')

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
                        Slaptažodžio atkūrimas
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-stone-500">
                        Įveskite el. paštą. Atsiųsime atkūrimo nuorodą.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-stone-800">El. paštas</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="mt-1 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-600"
                        >

                        @error('email')
                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center rounded-2xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800"
                    >
                        Siųsti nuorodą
                    </button>

                    <p class="text-sm text-stone-600 text-center">
                        Prisiminėte?
                        <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">
                            Prisijungti
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection