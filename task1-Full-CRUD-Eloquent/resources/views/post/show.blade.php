<x-layout>
    <h1 class="text-5xl">{{ $post->title }}</h1>
    <h3>{{ $post->user->name }}</h3>
    
    <div class="container">   
        <p>{{ $post->body }}</p>
    </div>

    <ul>
        @foreach ($post->comments as $comment)
            <li>{{ $comment->author }} : {{ $comment->content }}</li>
        @endforeach
    </ul>
</x-layout>
