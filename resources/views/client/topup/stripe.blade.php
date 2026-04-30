<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('topup.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Stripe (Credit Card)') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(!$keysConfigured)
                        <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg border border-yellow-100 mb-4 text-sm">
                        Stripe not configured. Please contact administrator or set up keys.
                        </div>
                    @endif

                    <div class="mb-6 text-center">
                        <div class="text-4xl mb-2">💳</div>
                        <h3 class="text-lg font-medium text-gray-900">Instant Top-up</h3>
                        <p class="text-sm text-gray-500">Pay securely with Stripe</p>
                    </div>

                    <form method="POST" action="{{ route('topup.stripe.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="amount" :value="__('Amount (UC)')" />
                            <x-text-input id="amount" name="amount" type="number" class="mt-1 block w-full" :value="old('amount', 10000)" required step="1000" />
                            <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                            <p class="mt-1 text-xs text-gray-500">10,000 UC = 1 USD. Minimum 5,000 UC.</p>
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex justify-between text-sm font-medium">
                                <span>You will pay:</span>
                                <span id="display-price">$1.00</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button class="w-full justify-center py-3" :disabled="!$keysConfigured">
                                {{ __('Proceed to Stripe') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const amountInput = document.getElementById('amount');
        const displayPrice = document.getElementById('display-price');
        
        amountInput.addEventListener('input', function() {
            const uc = parseFloat(this.value) || 0;
            const dollars = uc / 10000;
            displayPrice.textContent = '$' + dollars.toFixed(2);
        });
    </script>
</x-app-layout>
