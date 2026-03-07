<x-layout-simple :title="$page_Title">

<div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-purple-50 px-4 py-12">
  
  <div class="w-full max-w-md">
    
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
      
      <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6 text-center">
        <!-- Logo -->
        <div class="flex justify-center mb-3">
          <a href="/">
            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-2xl hover:bg-white/30 transition-all duration-300">
              <img 
                src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" 
                alt="Logo" 
                class="h-8 w-auto brightness-0 invert"
              />
            </div>
          </a>
        </div>

        <!-- Title -->
        <h2 class="text-2xl font-bold text-white">
          Welcome back!
        </h2>
        <p class="text-indigo-100 text-sm mt-1">
          Sign in to your account
        </p>
      </div>

      <div class="p-8">
        
        @if (session('status'))
          <div class="mb-4 bg-green-50 border border-green-200 text-green-600 text-sm rounded-lg p-3 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('status') }}
          </div>
        @endif

        <form action="/login" method="POST" class="space-y-6">
          @csrf

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
                   autofocus
                   placeholder="you@example.com"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition shadow-sm">
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="block text-sm font-semibold text-gray-700">
                <span class="flex items-center">
                  <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                  Password
                </span>
              </label>
              <a href="/forgot-password" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium hover:underline transition">
                Forgot?
              </a>
            </div>

            <input type="password"
                   name="password"
                   required
                   placeholder="••••••••"
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition shadow-sm">
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input type="checkbox" 
                     id="remember" 
                     name="remember" 
                     class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded transition">
              <label for="remember" class="ml-2 block text-sm text-gray-700">
                Remember me
              </label>
            </div>
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

          <button type="submit"
                  class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all transform hover:scale-[1.02] shadow-md">
            <span class="flex items-center justify-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              Sign in
            </span>
          </button>
        </form>

        <!-- Sign Up Link -->
        <p class="mt-8 text-center text-sm text-gray-600">
          Not a member yet?
          <a href="/signup" class="text-indigo-600 font-semibold hover:text-indigo-500 hover:underline transition ml-1">
            Create account
          </a>
        </p>

      </div>
    </div>

    <!-- Footer -->
    <p class="text-center text-xs text-gray-500 mt-6">
      &copy; {{ date('Y') }} Your Company. All rights reserved.
    </p>
  </div>

</div>

</x-layout-simple>