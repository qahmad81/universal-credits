<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Top-up Credits') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-4 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-4 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($paymentMethods as $method)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col items-center">
                        <div class="mb-4 text-4xl">
                            @if($method->slug == 'manual_transfer') 🏦 @elseif($method->slug == 'stripe') 💳 @elseif($method->slug == 'crypto') ₿ @else 💰 @endif
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ $method->name }}</h3>
                        <p class="text-gray-600 text-sm mb-6 text-center">
                            @if($method->slug == 'manual_transfer')
                                Local bank transfer. Manual verification.
                            @elseif($method->slug == 'stripe')
                                Pay with Credit Card. Instant delivery.
                            @elseif($method->slug == 'crypto')
                                Pay with BTC/ETH/USDT.
                            @endif
                        </p>
                        <a href="{{ route('topup.' . ($method->slug == 'manual_transfer' ? 'manual' : ($method->slug == 'stripe' ? 'stripe' : 'crypto'))) }}" 
                           class="mt-auto w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Select
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
