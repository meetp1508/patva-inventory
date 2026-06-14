<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Edit Attribute — ' . $attribute->name" :back="route('attributes.index')" />
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <x-flash />

        <form action="{{ route('attributes.update', $attribute) }}" method="POST">
            @csrf
            @method('PUT')
            @include('attributes._form', ['attribute' => $attribute])
        </form>
    </div>
</x-app-layout>
