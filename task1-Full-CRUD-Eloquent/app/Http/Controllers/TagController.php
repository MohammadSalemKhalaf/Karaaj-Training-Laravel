<?php

namespace App\Http\Controllers;
use App\models\tag;

use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $data = Tag::all();
    return view('tag.index',['tags'=>$data,'page_Title'=>'page tag']);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tag.create',['page_Title'=>'create tag']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
$tag = Tag::with('posts.comments')->findOrFail($id);

    return view('tag.show', ['tag' => $tag,'page_Title' => $tag->title]);    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
