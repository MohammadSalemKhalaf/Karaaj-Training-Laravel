<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title ?? "laravel" }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        .nav-link {
            transition: all 0.2s ease;
        }
        .nav-link:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-full">
        <nav class="bg-gradient-to-r from-gray-900 to-gray-800 shadow-lg border-b border-gray-700">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <div class="bg-indigo-600 p-2 rounded-lg shadow-md">
                                <img src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" 
                                     alt="Your Company" 
                                     class="size-5 brightness-0 invert" />
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-1">
                                <a href="/" 
                                   aria-current="page" 
                                   class="nav-link rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-md">
                                   Home
                                </a>
                                <a href="/about" 
                                   class="nav-link rounded-lg px-4 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white hover:shadow-md">
                                   About
                                </a>
                                <a href="/contact" 
                                   class="nav-link rounded-lg px-4 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white hover:shadow-md">
                                   Contact
                                </a>
                                <a href="/post" 
                                   class="nav-link rounded-lg px-4 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white hover:shadow-md">
                                   Blog
                                </a>
                                <a href="/comment" 
                                   class="nav-link rounded-lg px-4 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white hover:shadow-md">
                                   Comment
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Desktop Right Section -->
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6 space-x-3">
                            @auth
                                <div class="flex items-center space-x-3 bg-gray-700/50 rounded-full pl-4 pr-1 py-1">
                                    <span class="text-sm font-medium text-white">{{ Auth::user()->name }}</span>
                                    <form action="/logout" method="POST" class="inline">

                                        @csrf
                                        <button type="submit" 
                                                class="text-sm text-gray-300 hover:text-white bg-gray-800 hover:bg-gray-900 px-3 py-1.5 rounded-full transition-all duration-200">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="flex items-center space-x-2">
                                    <a href="/signup" 
                                       class="text-sm text-gray-300 hover:text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-all duration-200">
                                       Sign Up
                                    </a>
                                    <a href="/login" 
                                       class="text-sm text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg shadow-md transition-all duration-200">
                                       Login
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>
                    
                    <!-- Mobile menu button -->
                    <div class="-mr-2 flex md:hidden">
                        <button type="button" 
                                onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                                class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="absolute -inset-0.5"></span>
                            <span class="sr-only">Open main menu</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-6 menu-icon-open">
                                <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-6 hidden menu-icon-close">
                                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden">
                <div class="space-y-1 px-4 pt-2 pb-3 border-t border-gray-700">
                    <a href="/" 
                       class="block rounded-lg bg-indigo-600 px-3 py-2 text-base font-medium text-white shadow-md">
                       Home
                    </a>
                    <a href="/about" 
                       class="block rounded-lg px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                       About
                    </a>
                    <a href="/contact" 
                       class="block rounded-lg px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                       Contact
                    </a>
                    <a href="/post" 
                       class="block rounded-lg px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                       Blog
                    </a>
                    <a href="/comment" 
                       class="block rounded-lg px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                       Comment
                    </a>
                </div>
                
                @auth
                <div class="border-t border-gray-700 bg-gray-800/50 pt-4 pb-3">
                    <div class="flex items-center px-5">
                        <div class="shrink-0">
                            <div class="size-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-white">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-gray-400">{{ Auth::user()->email ?? '' }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1 px-2">
                        <form action="/logout" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="block w-full text-left rounded-lg px-3 py-2 text-base font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <div class="border-t border-gray-700 bg-gray-800/50 pt-4 pb-3 px-5">
                    <div class="flex space-x-2">
                        <a href="/signup" 
                           class="flex-1 text-center text-sm text-white bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg transition-all duration-200">
                           Sign Up
                        </a>
                        <a href="/login" 
                           class="flex-1 text-center text-sm text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg shadow-md transition-all duration-200">
                           Login
                        </a>
                    </div>
                </div>
                @endauth
            </div>
        </nav>
        
        <main>
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    <script>
        document.querySelector('button[onclick*="mobile-menu"]')?.addEventListener('click', function() {
            const openIcon = this.querySelector('.menu-icon-open');
            const closeIcon = this.querySelector('.menu-icon-close');
            openIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });
    </script>
</body>
</html>