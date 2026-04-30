<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('topup.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manual Bank Transfer') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bank Account Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Bank Name</p>
                            <p class="font-bold">{{ $config['bank_name'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Account Holder</p>
                            <p class="font-bold">{{ $config['account_holder'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Account Number</p>
                            <p class="font-bold">{{ $config['account_number'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Reference Instruction</p>
                            <p class="font-bold">{{ $config['reference_instructions'] ?? 'Use your username' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-blue-50 text-blue-700 text-xs rounded border border-blue-100">
                        <strong>Note:</strong> 10,000 UC = 1 USD. Please ensure you transfer the correct amount and provide the reference number below.
                    </div>
                </div>

                <form method="POST" action="{{ route('topup.manual.store') }}" class="p-6 space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="amount" :value="__('Amount (UC)')" />
                        <x-text-input id="amount" name="amount" type="number" class="mt-1 block w-full" :value="old('amount', 10000)" required step="100" />
                        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                        <p class="mt-1 text-xs text-gray-500">How many Universal Credits you've sent.</p>
                    </div>

                    <div>
                        <x-input-label for="reference" :value="__('Transaction Reference / ID')" />
                        <x-text-input id="reference" name="reference" type="text" class="mt-1 block w-full" :value="old('reference')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('reference')" />
                    </div>

                    <div>
                        <x-input-label for="notes" :value="__('Notes (Optional)')" />
                        <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Submit Payment Request') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
