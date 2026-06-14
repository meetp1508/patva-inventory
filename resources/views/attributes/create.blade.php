<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Add New Attribute" :back="route('attributes.index')" />
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <form action="{{ route('attributes.store') }}" method="POST">
            @csrf
            @include('attributes._form')
        </form>
    </div>
</x-app-layout>
