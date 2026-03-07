<?php

namespace App\Http\Controllers\api\v1;
use App\models\Tag;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TagApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index()
    {
        $data=Tag::paginate(5);
        if($data->isEmpty()){
            return response()->json(['message' => 'No Tags found'], 404);
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
       $data=Tag::create($request->all());
       
       return response()->json(['message' => 'Tag created successfully', 'data' => $data], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data=Tag::find($id);
        if(!$data){
            return response()->json(['message' => 'Tag not found'], 404);
        }
        else{
        return response()->json(['message' => 'Tag retrieved successfully', 'data' => $data], 200);
    }
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
{
    $data = Tag::find($id);

    if (!$data) {
        return response()->json(['message' => 'Tag not found'], 404);
    }

    $data->update($request->all());

    return response()->json([
        'message' => 'Tag updated successfully',
        'data' => $data
    ], 200);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       $data=Tag::find($id);
       if(!$data){
        return response()->json(['message' => 'Tag not found'], 404);
       }
       $data->delete();
         return response()->json(['message' => 'Tag deleted successfully'], 200);
    }
}
