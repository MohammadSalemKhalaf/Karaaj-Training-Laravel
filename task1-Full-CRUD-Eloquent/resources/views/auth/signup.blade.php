<x-layout-simple :title="$page_Title">

<div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4 py-12">
  
  <div class="w-full max-w-md">
    
    <!-- Card with enhanced design -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
      
      <!-- Header with gradient -->
      <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6 text-center">
        <!-- Logo -->
        <div class="flex justify-center mb-3">
          <div class="bg-white/20 backdrop-blur-sm p-3 rounded-2xl">
            <a href="/">
            <img 
              src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" 
              alt="Logo" 
              class="h-8 w-auto brightness-0 invert"
            />
            </a>
          </div>
        </div>

        <!-- Title -->
        <h2 class="text-2xl font-bold text-white">
          Create your account
        </h2>
        <p class="text-indigo-100 text-sm mt-1">
          Join our community today
        </p>
      </div>
      <div class="p-8">
        @if (session('success'))
          <div class="mb-4 bg-green-50 border border-green-200 text-green-600 text-sm rounded-lg p-3 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
          </div>
        @endif

        <form action="/signup" method="POST" class="space-y-5">
          @csrf

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Full Name
              </span>
            </label>
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   placeholder="John Doe"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition shadow-sm">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Email address
              </span>
            </label>
            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   placeholder="you@example.com"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition shadow-sm">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Password
              </span>
            </label>
            <input type="password"
                   name="password"
                   required
                   placeholder="••••••••"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition shadow-sm">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <span class="flex items-center">
                <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Confirm Password
              </span>
            </label>
            <input type="password"
                   name="password_confirmation"
                   required
                   placeholder="••••••••"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition shadow-sm">
          </div>

                  @if ($errors->any())
              <div class="bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded-lg p-4 space-y-1">
                  <div class="font-semibold mb-1">Please fix the following errors:</div>
                  @foreach ($errors->all() as $error)
                      <div class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $error }}</span>
                      </div>
                  @endforeach
              </div>
          @endif

          <!-- Button with gradient -->
          <button type="submit"
                  class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all transform hover:scale-[1.02] shadow-md">
            Create Account
          </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">Or continue with</span>
          </div>
        </div>

        <!-- Login Link -->
        <p class="mt-6 text-center text-sm text-gray-600">
          Already have an account?
          <a href="/login" class="text-indigo-600 font-semibold hover:text-indigo-500 hover:underline transition">
            Sign in here
          </a>
        </p>
      </div>
    </div>

    <p class="text-center text-xs text-gray-500 mt-6">
      &copy; {{ date('Y') }} Your Company. All rights reserved.
    </p>
  </div>

</div>

</x-layout-simple>