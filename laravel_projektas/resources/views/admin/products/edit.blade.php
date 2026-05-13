<x-admin-layout :title="'Redaguoti prekę'">
    <div class="max-w-4xl">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Redaguoti prekę</h1>
            </div>

            <a href="{{ route('admin.products.index') }}"
               class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Grįžti
            </a>
        </div>

        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('admin.products.partials.form', [
                'product' => $product,
                'categories' => $categories
            ])

            <div class="mt-6 flex items-center justify-end gap-2">
                <a href="{{ route('admin.products.index') }}"
                   class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Atšaukti
                </a>

                <button type="submit"
                        class="inline-flex items-center rounded-2xl bg-[#b45309] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-95 transition">
                    Išsaugoti pakeitimus
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>