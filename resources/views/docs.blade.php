<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>API Documentation - Universal Credits</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            pre { background-color: #1e293b; color: #f8fafc; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; }
            code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
        </style>
    </head>
    <body class="antialiased bg-slate-50 text-slate-900">
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-64 bg-white border-r border-slate-200 hidden lg:block sticky top-0 h-screen overflow-y-auto">
                <div class="p-6">
                    <a href="/" class="text-xl font-bold text-indigo-600">Universal Credits</a>
                </div>
                <nav class="px-4 pb-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 px-2">Introduction</p>
                    <a href="#auth" class="block py-2 px-2 text-sm text-slate-600 hover:bg-slate-50 rounded">Authentication</a>
                    <a href="#system" class="block py-2 px-2 text-sm text-slate-600 hover:bg-slate-50 rounded">Number System</a>
                    
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mt-8 mb-4 px-2">Endpoints</p>
                    <a href="#reserve" class="block py-2 px-2 text-sm text-slate-600 hover:bg-slate-50 rounded">POST /reserve</a>
                    <a href="#confirm" class="block py-2 px-2 text-sm text-slate-600 hover:bg-slate-50 rounded">POST /confirm</a>
                    <a href="#cancel" class="block py-2 px-2 text-sm text-slate-600 hover:bg-slate-50 rounded">POST /cancel</a>

                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mt-8 mb-4 px-2">Examples</p>
                    <a href="#php" class="block py-2 px-2 text-sm text-slate-600 hover:bg-slate-50 rounded">PHP Example</a>
                    <a href="#python" class="block py-2 px-2 text-sm text-slate-600 hover:bg-slate-50 rounded">Python Example</a>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 max-w-4xl px-6 py-12 lg:px-12">
                <h1 class="text-4xl font-extrabold tracking-tight mb-4">API Documentation</h1>
                <p class="text-lg text-slate-600 mb-12">Integrate Universal Credits into your application to enable seamless, low-cost microtransactions.</p>

                <!-- Authentication -->
                <section id="auth" class="mb-16 scroll-mt-12">
                    <h2 class="text-2xl font-bold mb-4">Authentication</h2>
                    <p class="mb-4">All vendor API requests must include your API key in the <code>Authorization</code> header:</p>
                    <pre><code>Authorization: Bearer YOUR_VENDOR_KEY</code></pre>
                    <p class="mt-4">Additionally, client-side requests must include the <code>client_token</code> representing the user's wallet.</p>
                </section>

                <!-- Number System -->
                <section id="system" class="mb-16 scroll-mt-12">
                    <h2 class="text-2xl font-bold mb-4">Number System</h2>
                    <p class="mb-4">Universal Credits (UC) use a high-precision decimal system internally. For display, we use integers where 10,000 UC = $1.00 USD.</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li><strong>1 UC</strong> = $0.0001 USD</li>
                        <li><strong>10,000 UC</strong> = $1.00 USD</li>
                        <li><strong>1,000,000 UC</strong> = $100.00 USD</li>
                    </ul>
                </section>

                <!-- Reserve -->
                <section id="reserve" class="mb-16 scroll-mt-12">
                    <h2 class="text-2xl font-bold mb-4">POST /api/v1/reserve</h2>
                    <p class="mb-4 text-slate-600">Reserve a portion of the user's balance. This ensures the funds are available before service delivery.</p>
                    <div class="bg-slate-100 p-4 rounded mb-4">
                        <p class="font-mono text-sm"><strong>Body:</strong> { client_token, amount, description? }</p>
                    </div>
                    <p class="mb-4"><strong>Response:</strong></p>
                    <pre><code>{
  "reservation_id": "res_abc123",
  "amount_reserved": 500,
  "expires_at": "2026-05-01T12:00:00Z"
}</code></pre>
                </section>

                <!-- Confirm -->
                <section id="confirm" class="mb-16 scroll-mt-12">
                    <h2 class="text-2xl font-bold mb-4">POST /api/v1/confirm</h2>
                    <p class="mb-4 text-slate-600">Settle a previous reservation. If the actual amount is less than reserved, the difference is automatically refunded.</p>
                    <div class="bg-slate-100 p-4 rounded mb-4">
                        <p class="font-mono text-sm"><strong>Body:</strong> { reservation_id, actual_amount }</p>
                    </div>
                    <p class="mb-4"><strong>Response:</strong></p>
                    <pre><code>{
  "success": true,
  "amount_charged": 450,
  "refunded": 50
}</code></pre>
                </section>

                <!-- Cancel -->
                <section id="cancel" class="mb-16 scroll-mt-12">
                    <h2 class="text-2xl font-bold mb-4">POST /api/v1/cancel</h2>
                    <p class="mb-4 text-slate-600">Cancel a reservation and release all held funds back to the user.</p>
                    <div class="bg-slate-100 p-4 rounded mb-4">
                        <p class="font-mono text-sm"><strong>Body:</strong> { reservation_id }</p>
                    </div>
                </section>

                <!-- Errors -->
                <section id="errors" class="mb-16 scroll-mt-12">
                    <h2 class="text-2xl font-bold mb-4">HTTP Status Codes</h2>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex border-b border-slate-200 pb-2">
                            <span class="w-16 font-bold">401</span>
                            <span class="text-slate-600">Unauthorized - Invalid API key</span>
                        </div>
                        <div class="flex border-b border-slate-200 pb-2">
                            <span class="w-16 font-bold">402</span>
                            <span class="text-slate-600">Payment Required - Insufficient balance</span>
                        </div>
                        <div class="flex border-b border-slate-200 pb-2">
                            <span class="w-16 font-bold">404</span>
                            <span class="text-slate-600">Not Found - Reservation does not exist</span>
                        </div>
                        <div class="flex border-b border-slate-200 pb-2">
                            <span class="w-16 font-bold">409</span>
                            <span class="text-slate-600">Conflict - Reservation already settled or cancelled</span>
                        </div>
                        <div class="flex">
                            <span class="w-16 font-bold">429</span>
                            <span class="text-slate-600">Too Many Requests - Rate limit exceeded</span>
                        </div>
                    </div>
                </section>

                <!-- PHP Example -->
                <section id="php" class="mb-16 scroll-mt-12">
                    <h2 class="text-2xl font-bold mb-4">PHP Example</h2>
                    <pre><code>$client = new \GuzzleHttp\Client();
$response = $client->post('https://api.universal-credits.io/api/v1/reserve', [
    'headers' => ['Authorization' => 'Bearer ' . $apiKey],
    'json' => [
        'client_token' => 'user_token_here',
        'amount' => 1000,
        'description' => 'Premium Content Access'
    ]
]);
$data = json_decode($response->getBody(), true);</code></pre>
                </section>

                <!-- Python Example -->
                <section id="python" class="mb-16 scroll-mt-12">
                    <h2 class="text-2xl font-bold mb-4">Python Example</h2>
                    <pre><code>import requests

headers = {"Authorization": f"Bearer {api_key}"}
payload = {
    "client_token": "user_token_here",
    "amount": 1000,
    "description": "API Call"
}

r = requests.post("https://api.universal-credits.io/api/v1/reserve", 
                  headers=headers, json=payload)
print(r.json())</code></pre>
                </section>
            </main>
        </div>
    </body>
</html>
