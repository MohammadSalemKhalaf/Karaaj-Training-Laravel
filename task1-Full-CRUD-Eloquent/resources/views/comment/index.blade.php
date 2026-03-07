<x-layout :title="$page_Title">
        <h2>Comments test</h2>
    @foreach ($comments as $comment)
    <h3 class="text-2xl">{{ $comment->content }}</h3>
        <h4>{{ $comment->author }}</h4>
                <!-- <a href="/blog/{{ $comment->post_id }}">{{ $comment->post->title }}</a> -->
    @endforeach
</x-layout>                         