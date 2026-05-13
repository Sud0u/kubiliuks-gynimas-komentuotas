<section>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-stone-900">Paskyros ištrynimas</h2>
    </div>

    <p class="mb-4 text-sm text-stone-600">
        Įveskite savo slaptažodį ir patvirtinkite paskyros ištrynimą.
    </p>

    @if (session('status') === 'account-deleted')
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            Paskyra sėkmingai ištrinta.
        </div>
    @endif

    <form method="post" action="{{ route('profile.destroy') }}" class="space-y-5">
        @csrf
        @method('delete')

        <div>
            <label for="delete_password" class="block text-sm font-semibold text-stone-800 mb-2">
                Slaptažodis
            </label>

            <input
                id="delete_password"
                name="password"
                type="password"
                required
                class="w-full rounded-xl border border-stone-300 px-4 py-3 focus:border-red-500 focus:ring-red-500"
                placeholder="Įveskite slaptažodį"
            >

            @error('password', 'userDeletion')
                <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-semibold text-red-700 hover:bg-red-100 transition"
                onclick="return confirm('Ar tikrai norite ištrinti paskyrą?');"
            >
                Ištrinti paskyrą
            </button>
        </div>
    </form>
</section>