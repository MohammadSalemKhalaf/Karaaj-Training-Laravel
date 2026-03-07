<x-layout :title="$page_Title">
    @foreach ($tags as $tag)
    <h1 class="text-2xl">{{ $tag->title }}</h1>
    @endforeach
</x-layout>                         