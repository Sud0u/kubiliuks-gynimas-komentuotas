<x-admin-layout :title="'Prekės'">
    @php
        function productImageUrl($path) {
            if (!$path) return null;

            $path = trim($path);

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            if (str_starts_with($path, '/')) {
                $path = ltrim($path, '/');
            }

            if (str_starts_with($path, 'public/')) {
                $path = substr($path, strlen('public/'));
            }

            if (str_starts_with($path, 'storage/')) {
                return asset($path);
            }

            if (str_starts_with($path, 'images/')) {
                return asset($path);
            }

            return asset('storage/' . $path);
        }

        $status = $status ?? request()->query('status', 'active');
        $q = $q ?? request()->query('q', '');
        $countAll = $countAll ?? null;
        $countActive = $countActive ?? null;
        $countInactive = $countInactive ?? null;

        $btnClass = function ($key) use ($status) {
            $active = $status === $key;

            if ($active) {
                return 'bg-stone-900 text-white border border-stone-900';
            }

            return 'bg-white text-stone-700 border border-black/10 hover:bg-stone-50';
        };
    @endphp

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold">Prekės</h1>

            <p class="text-slate-500 mt-1">
                @if(!is_null($countAll) && !is_null($countActive) && !is_null($countInactive))
                    Viso: {{ $countAll }} • Aktyvios: {{ $countActive }} • Paslėptos: {{ $countInactive }}
                @else
                    Viso: {{ method_exists($products, 'total') ? $products->total() : count($products) }}
                @endif
            </p>
        </div>
    </div>

    <div class="mb-5 rounded-2xl border border-black/10 bg-white p-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.products.index', ['status' => 'active', 'q' => $q]) }}"
                   class="px-4 py-2 rounded-full text-sm font-semibold {{ $btnClass('active') }}">
                    Aktyvios
                </a>

                <a href="{{ route('admin.products.index', ['status' => 'inactive', 'q' => $q]) }}"
                   class="px-4 py-2 rounded-full text-sm font-semibold {{ $btnClass('inactive') }}">
                    Paslėptos
                </a>

                <a href="{{ route('admin.products.index', ['status' => 'all', 'q' => $q]) }}"
                   class="px-4 py-2 rounded-full text-sm font-semibold {{ $btnClass('all') }}">
                    Visos
                </a>
            </div>

            <form method="GET" action="{{ route('admin.products.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">

                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Paieška pagal pavadinimą"
                    class="w-full lg:w-[320px] rounded-xl border border-black/10 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-200"
                >

                <button
                    type="submit"
                    class="rounded-xl bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-800">
                    Ieškoti
                </button>
            </form>
        </div>
    </div>

    @if($products->count() === 0)
        <div class="rounded-2xl border border-black/10 bg-white p-10 text-center text-stone-600">
            Nieko nerasta.
        </div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($products as $p)
                @php $img = productImageUrl($p->image); @endphp

                <div class="rounded-2xl border border-black/10 bg-white overflow-hidden shadow-sm hover:shadow transition">
                    <a href="{{ route('admin.products.edit', $p) }}" class="block">
                        <div class="aspect-[4/3] bg-stone-100">
                            @if ($img)
                                <img src="{{ $img }}" class="w-full h-full object-cover" alt="">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-stone-400 text-sm">
                                    Nėra nuotraukos
                                </div>
                            @endif
                        </div>
                    </a>

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-stone-900 truncate">{{ $p->name }}</div>
                                <div class="text-xs text-stone-500 mt-1">
                                    {{ $p->category?->name ?? 'Be kategorijos' }}
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="text-sm font-semibold text-stone-900">{{ number_format((float)$p->price, 2) }} €</div>
                                <div class="text-xs {{ $p->stock > 0 ? 'text-stone-500' : 'text-red-600' }}">
                                    Likutis: {{ $p->stock }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-4 gap-2">
                            <div class="text-xs">
                                @if ($p->is_active)
                                    <span class="px-2 py-1 rounded-full bg-green-50 text-green-700 border border-green-200">Aktyvi</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-stone-100 text-stone-700 border border-stone-200">Paslėpta</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.products.edit', $p) }}"
                                   class="px-3 py-2 rounded-full border border-stone-300 bg-white text-sm hover:bg-stone-100">
                                    Redaguoti
                                </a>

                                <form method="POST" action="{{ route('admin.products.destroy', $p) }}"
                                      onsubmit="return confirm('Ar tikrai nori šalinti? Jei prekė buvo užsakymuose – ji bus paslėpta, o ne ištrinta.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-2 rounded-full border border-red-200 bg-red-50 text-sm text-red-700 hover:bg-red-100">
                                        Trinti
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if(!$p->is_active)
                            <div class="mt-3 text-xs text-stone-500">
                                Paslėpta prekė nebus rodoma parduotuvėje.
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
</x-admin-layout>