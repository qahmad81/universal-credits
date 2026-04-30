<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <a href="{{ route('topup.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cryptocurrency') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <div class="text-4xl mb-4">₿</div>
                    <h3 class="text-xl font-bold mb-2">Coming Soon</h3>
                    <p class="text-gray-600">Cryptocurrency payments coming soon. Please use Bank Transfer or Stripe for now.</p>
                    
                    <div class="mt-8">
                        <a href="{{ route('topup.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                            &larr; Back to options
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
