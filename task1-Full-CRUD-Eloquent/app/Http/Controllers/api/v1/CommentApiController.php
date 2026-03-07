<?php

namespace App\Http\Controllers\api\v1;
use App\Models\Comment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommentApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        $data=Comment::paginate(5);
        if($data->isEmpty()){
            return response()->json(['message' => 'No Comments found'], 404);
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
       $data=Comment::create($request->all());
       
       return response()->json(['message' => 'Comment created successfully', 'data' => $data], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data=Comment::find($id);
        if(!$data){
            return response()->json(['message' => 'Comment not found'], 404);
        }
        else{
        return response()->json(['message' => 'Comment retrieved successfully', 'data' => $data], 200);
    }
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
{
    $data = Comment::find($id);

    if (!$data) {
        return response()->json(['message' => 'Comment not found'], 404);
    }

    $data->update($request->all());

    return response()->json([
        'message' => 'Comment updated successfully',
        'data' => $data
    ], 200);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       $data=Comment::find($id);
       if(!$data){
        return response()->json(['message' => 'Comment not found'], 404);
       }
       $data->delete();
         return response()->json(['message' => 'Comment deleted successfully'], 200);
    }
}
