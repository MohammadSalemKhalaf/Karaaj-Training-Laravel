<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Post::latest()->cursorPaginate( 5);

    return view('post.index',['posts'=>$data,'page_Title'=>'page post']);
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('post.create',['page_Title'=>'create post']);
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request)
    {
     $post=new Post();
     $post->title=$request->input('title');
     $post->body=$request->input('body');
     $post->published=$request->input('published');
     $post->user_id=auth()->id();
     $post->save();

     return redirect('post')->with('success','Post created successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
    return view('post.show',['post'=>$post,"page_Title"=>$post->title]);
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        // ##Using Gates
        return view('post.edit',["post"=>$post,"page_Title"=>$post->title]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $request, Post $post)
    {
     $post->title=$request->input('title');
     $post->body=$request->input('body');
     $post->published=$request->has('published');
     $post->user_id=auth()->id();
     $post->save();

     return redirect('post')->with('success','Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect('post')->with('success','Post deleted successfully');
    }
}
