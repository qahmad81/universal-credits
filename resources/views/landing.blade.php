<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $siteName }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="antialiased bg-gray-50">
        <header class="bg-white shadow-sm border-b border-gray-100">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-indigo-600 tracking-tight">{{ $siteName }}</a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/docs" class="text-sm font-medium text-gray-500 hover:text-gray-900">Docs</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">Login</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors">Register</a>
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            <!-- Hero Section -->
            <div class="relative bg-white overflow-hidden">
                <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <div class="prose prose-indigo prose-lg mx-auto">
                            {!! $landingHtml !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="py-24 bg-gray-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Unified Credits</h3>
                            <p class="text-gray-500 text-sm leading-relaxed">Spend credits anywhere in the ecosystem. One balance, infinite possibilities.</p>
                        </div>
                        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-6">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Microtransactions</h3>
                            <p class="text-gray-500 text-sm leading-relaxed">optimized for sub-cent transactions with zero gas fees and instant settlement.</p>
                        </div>
                        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Simple API</h3>
                            <p class="text-gray-500 text-sm leading-relaxed">Integrate with just a few lines of code. Support for PHP, Python, and Node.js.</p>
                        </div>
                        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-6">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Open Source</h3>
                            <p class="text-gray-500 text-sm leading-relaxed">Full transparency. Self-hostable and auditable core infrastructure.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How it works -->
            <div class="py-24 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-extrabold text-gray-900 text-center mb-16">Get started in minutes</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-600 text-white text-2xl font-bold mb-6 italic">1</div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Get API Key</h3>
                            <p class="text-gray-500">Sign up and create a vendor profile to get your API credentials.</p>
                        </div>
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-600 text-white text-2xl font-bold mb-6 italic">2</div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Integrate</h3>
                            <p class="text-gray-500">Add the reserve and confirm endpoints to your payment flow.</p>
                        </div>
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-600 text-white text-2xl font-bold mb-6 italic">3</div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Earn</h3>
                            <p class="text-gray-500">Receive credits instantly from users and withdraw to your bank.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-gray-900 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.
                </div>
                <div class="flex space-x-6 mt-6 md:mt-0">
                    <a href="https://github.com" class="text-gray-400 hover:text-white transition-colors">GitHub</a>
                    <a href="mailto:contact@universal-credits.io" class="text-gray-400 hover:text-white transition-colors">Contact</a>
                </div>
            </div>
        </footer>
    </body>
</html>
