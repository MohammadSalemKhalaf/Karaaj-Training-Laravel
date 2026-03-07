<?php

namespace App\Http\Controllers\api\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;



class PostApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data=Post::paginate(5);
        if($data->isEmpty()){
            return response()->json(['message' => 'No posts found'], 404);
        }
        else{
            return response()->json($data,200);
        }
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $data=Post::create($request->all());
       
       return response()->json(['message' => 'Post created successfully', 'data' => $data], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data=Post::find($id);
        if(!$data){
            return response()->json(['message' => 'Post not found'], 404);
        }
        else{
        return response()->json(['message' => 'Post retrieved successfully', 'data' => $data], 200);
    }
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
{
    $data = Post::find($id);

    if (!$data) {
        return response()->json(['message' => 'Post not found'], 404);
    }

    $data->update($request->all());

    return response()->json([
        'message' => 'Post updated successfully',
        'data' => $data
    ], 200);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       $data=Post::find($id);
       if(!$data){
        return response()->json(['message' => 'Post not found'], 404);
       }
       $data->delete();
         return response()->json(['message' => 'Post deleted successfully'], 200);
    }
}
