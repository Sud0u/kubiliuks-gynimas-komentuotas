<x-admin-layout :title="'Pridėti prekę'">
    @php
        $product = null;
    @endphp

    <div class="max-w-4xl">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Pridėti prekę</h1>
                <p class="text-slate-500 mt-1">Užpildyk informaciją ir išsaugok naują prekę kataloge.</p>
            </div>

            <a href="{{ route('admin.products.index') }}"
               class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Atgal į prekes
            </a>
        </div>

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            @include('admin.products.partials.form', [
                'product' => $product,
                'categories' => $categories
            ])

            <div class="mt-6 flex items-center justify-end gap-2">
                <a href="{{ route('admin.products.index') }}"
                   class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Atgal
                </a>

                <button type="submit"
                        class="inline-flex items-center rounded-2xl bg-[#2f5bff] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-95 transition">
                    Išsaugoti prekę
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>