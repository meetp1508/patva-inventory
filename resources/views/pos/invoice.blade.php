<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Invoice ' . $invoice->invoice_number" :back="route('pos.index')">
            <x-slot name="action">
                <div class="flex gap-2">
                    <a href="{{ route('pos.invoice.pdf', $invoice) }}" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-900">Download PDF</a>
                    <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Print</button>
                    @if ($invoice->customer)
                        <form action="{{ route('whatsapp.send', $invoice) }}" method="POST">
                            @csrf
                            <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Send on WhatsApp</button>
                        </form>
                    @endif
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash />

    <div class="max-w-3xl mx-auto bg-white shadow-sm border border-gray-100 rounded-2xl p-8 print:shadow-none print:border-0">
        @include('pos.partials.invoice-body', ['invoice' => $invoice])
    </div>
</x-app-layout>
