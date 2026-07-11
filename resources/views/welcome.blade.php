<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ConnectDate') }} — Find Your Perfect Connection</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        body { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="bg-rose-50 text-gray-900 antialiased">
    <header class="bg-white/80 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">💕</span>
                    <span class="text-xl font-bold text-rose-600">ConnectDate</span>
                </div>
                @if (Route::has('login'))
                    <nav class="flex items-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-md hover:bg-rose-700 transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-md hover:bg-rose-700 transition">
                                    Sign up
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </div>
    </header>

    <main>
        <section class="relative overflow-hidden bg-gradient-to-br from-rose-500 via-rose-600 to-rose-700 text-white">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, white 1px, transparent 1px), radial-gradient(circle at 75% 75%, white 1px, transparent 1px); background-size: 60px 60px;"></div>
            </div>
            <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32 text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight">
                    Find Your Perfect Connection
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-rose-100 max-w-2xl mx-auto">
                    Meet authentic people near you. Start conversations that matter and build connections that last.
                </p>
                <div class="mt-10 flex items-center justify-center gap-4">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-3.5 bg-white text-rose-600 font-semibold rounded-md hover:bg-rose-50 transition text-base">
                            Get Started Free
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-3.5 border-2 border-white text-white font-semibold rounded-md hover:bg-white/10 transition text-base">
                            Log In
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <section class="bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">How It Works</h2>
                    <p class="mt-4 text-lg text-gray-600">Three simple steps to find your match</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto bg-rose-100 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-gray-900">Create Your Profile</h3>
                        <p class="mt-3 text-gray-600 leading-relaxed">Tell others about yourself. Share your interests, what you're looking for, and let your personality shine.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto bg-rose-100 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-gray-900">Browse Profiles</h3>
                        <p class="mt-3 text-gray-600 leading-relaxed">Explore profiles of people near you. Find someone who shares your interests and vibe.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto bg-rose-100 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                            </svg>
                        </div>
                        <h3 class="mt-6 text-xl font-semibold text-gray-900">Start Chatting</h3>
                        <p class="mt-3 text-gray-600 leading-relaxed">Send a message and start a conversation. Get to know each other and see where it goes.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-gradient-to-br from-rose-600 to-rose-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center text-white">
                <h2 class="text-3xl sm:text-4xl font-bold">Ready to Meet Someone?</h2>
                <p class="mt-4 text-lg text-rose-100 max-w-2xl mx-auto">
                    Join now and start connecting with people who are looking for the same thing you are.
                </p>
                <div class="mt-8">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-3.5 bg-white text-rose-600 font-semibold rounded-md hover:bg-rose-50 transition text-base">
                            Create Your Free Account
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-lg">💕</span>
                    <span class="text-sm font-semibold text-gray-700">ConnectDate</span>
                </div>
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} ConnectDate. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
