<x-layout :title="$page_Title">

   <form method="POST" action="/post/{{ $post->id }}">
    @csrf
    @method('PATCH')

    <input type="hidden" name="id" value="{{ $post->id }}">

    <div class="space-y-12">

        <div class="border-b border-gray-300 pb-12">
            <h2 class="text-base font-semibold text-gray-900">
                Edit post: {{ $post->title }}</h2>

            <!-- GRID START -->
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-6 gap-6">

                <!-- Title -->
                <div class="sm:col-span-full">
                    <label for="title" class="block text-sm font-medium text-gray-900">
                        Title
                    </label>
                    <div class="mt-2">
                        <input id="title" type="text" name="title" value="{{ old('title', $post->title) }}" class="block w-full rounded-md border {{ $errors->has('title') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-600"/>
                    </div>
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Body -->
                <div class="sm:col-span-6">
                    <label for="body" class="block text-sm font-medium text-gray-900">
                        Body
                    </label>
                    <div class="mt-2">
                        <textarea id="body" name="body" rows="4" class="block w-full rounded-md border {{ $errors->has('body') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-600" >
                        {{ old('body', $post->body) }}</textarea>
                    </div>
                    @error('body')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
            <!-- GRID END -->
        </div>

       <!-- Published -->
<div class="flex items-center gap-3">
    <input id="published" type="checkbox"  name="published" value="1" {{ old('published') || (!old() && $post->published) ? 'checked' : '' }}
        class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
    >
    
    <label for="published" class="text-sm font-medium text-gray-900">
        Published
    </label>
</div>


        <!-- Buttons -->
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <a href="/post" class="text-sm/6 font-semibold text-gray-900">
                Cancel
            </a>
            <button type="submit"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                Save
            </button>
        </div>

    </div>

</form>
</x-layout>                         

