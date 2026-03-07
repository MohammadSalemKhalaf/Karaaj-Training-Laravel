<x-layout>
    <h1 class="text-5xl">{{ $comment->title }}</h1>
    <h3>{{ $comment->author }}</h3>
    <p>{{ $comment->content }}</p>

    <ul>
        @foreach ($comment->post->comments as $comment)
            <li>{{ $comment->author }} : {{ $comment->content }}</li>
        @endforeach
    </ul>
</x-layout>
