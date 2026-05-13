@extends('layouts.app')

@section('title', 'Profilis')

@section('content')
<section class="py-12 bg-stone-50 min-h-[70vh]">
    <div class="max-w-4xl mx-auto px-4 lg:px-0">
        <div class="mb-6">
            <h1 class="text-3xl font-extrabold text-stone-900">Profilis</h1>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-6">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</section>
@endsection