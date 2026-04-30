<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Client Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                    @if(str_contains(session('success'), 'Raw token'))
                        <div class="mt-2 p-2 bg-white border border-green-200 font-mono break-all text-sm">
                            {{ explode(': ', session('success'))[1] }}
                        </div>
                        <p class="text-xs mt-1 text-red-600 font-bold">Copy this token now. It will not be shown again.</p>
                    @endif
                </div>
            @endif

            <!-- Balance Section -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Your Balance</h3>
                    <div class="text-4xl font-bold text-indigo-600 mt-2">
                        {{ number_format($displayBalance) }} UC
                    </div>
                </div>
                <div>
                    <a href="/topup" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Top-up
                    </a>
                </div>
            </div>

            <!-- Tokens Section -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Your Tokens</h3>
                    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-token')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Create Token
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Token (Masked)</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Limit (UC)</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used (UC)</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($tokens as $token)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $token->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $token->masked_token }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($token->display_limit) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($token->display_used) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $token->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $token->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($token->is_active)
                                            <form action="{{ route('tokens.deactivate', $token->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to deactivate this token?')">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900">Deactivate</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 italic">No tokens found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Transaction History (Last 20)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (UC)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($transactions as $tx)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($tx->type) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $tx->display_amount < 0 ? 'text-red-600' : 'text-green-600' }} font-bold">
                                        {{ $tx->display_amount > 0 ? '+' : '' }}{{ number_format($tx->display_amount) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 italic">No transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Token Modal -->
    <x-modal name="create-token" :show="false" focusable>
        <form method="post" action="{{ route('tokens.create') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">Create New Token</h2>
            <p class="mt-1 text-sm text-gray-600">Give your token a name and set a limit for its usage.</p>

            <div class="mt-6">
                <x-input-label for="name" value="Token Name" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="e.g. Production App" required />
            </div>

            <div class="mt-6">
                <x-input-label for="limit" value="Usage Limit (UC Integer)" />
                <x-text-input id="limit" name="limit" type="number" class="mt-1 block w-full" placeholder="10000" required />
                <p class="mt-1 text-sm text-gray-500 italic">Every 10,000 UC = 1 USD</p>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-primary-button class="ms-3">
                    {{ __('Create Token') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
