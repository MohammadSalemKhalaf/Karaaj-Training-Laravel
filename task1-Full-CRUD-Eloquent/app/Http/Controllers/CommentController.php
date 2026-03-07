<?php

namespace App\Http\Controllers;
use App\Models\comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
  
    public function index()
    {
 $data = comment::all();
    return view('comment.index',['comments'=>$data,'page_Title'=>'page comment']);
        
    }

    
    public function create()
    {
               return view('comment.create',['page_Title'=>'create comment']);

    }

    
    public function store(Request $request)
    {
        //
    }

   
    public function show(string $id)
    {
         $comment=Comment::findOrFail(id: $id);
    return view('comment.show',['comment'=>$comment,"page_Title"=>$comment->title]);
        //
    }

   
    public function edit(string $id)
    {
        //
    }

    
    public function update(Request $request, string $id)
    {
        //
    }

    
    public function destroy(string $id)
    {
        //
    }
}
