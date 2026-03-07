<x-layout>

<div class="bg-gray-50 min-h-screen">
    

    <!-- Hero Section -->
    <div class="bg-indigo-600 text-white">
        <div class="max-w-7xl mx-auto px-6 py-20 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Welcome to Crash Blog
            </h1>
            <p class="text-indigo-100 text-lg">
                A simple Laravel + Tailwind project
            </p>
        </div>
    </div>

    <!-- Posts Section -->
    <div class="max-w-7xl mx-auto px-6 py-16">

        <h2 class="text-3xl font-bold text-gray-800 mb-10">
            Featured Posts
        </h2>

        <div class="grid md:grid-cols-3 gap-8">

            <!-- Card 1 -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    Laravel Basics
                </h3>
                <p class="text-gray-600 text-sm mb-6">
                    Learn the fundamentals of Laravel framework and how MVC works.
                </p>
                <button class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">
                    Read More
                </button>
            </div>

            <!-- Card 2 -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    Blade Templates
                </h3>
                <p class="text-gray-600 text-sm mb-6">
                    Understand how Blade components and layouts work in Laravel.
                </p>
                <button class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">
                    Read More
                </button>
            </div>

            <!-- Card 3 -->
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    Tailwind Styling
                </h3>
                <p class="text-gray-600 text-sm mb-6">
                    Style your Laravel project beautifully using Tailwind CSS.
                </p>
                <button class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">
                    Read More
                </button>
            </div>

        </div>

    </div>

</div>

</x-layout>