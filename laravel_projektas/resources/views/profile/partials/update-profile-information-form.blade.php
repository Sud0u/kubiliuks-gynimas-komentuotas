<section>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-stone-900">Profilio duomenys</h2>
    </div>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-semibold text-stone-800 mb-2">Vardas</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 focus:border-emerald-600 focus:ring-emerald-600"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    autocomplete="name"
                >
                @error('name')
                    <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-stone-800 mb-2">El. paštas</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 focus:border-emerald-600 focus:ring-emerald-600"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="username"
                >
                @error('email')
                    <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800 transition"
            >
                Išsaugoti
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-sm text-emerald-700">Išsaugota</span>
            @endif
        </div>
    </form>
</section>