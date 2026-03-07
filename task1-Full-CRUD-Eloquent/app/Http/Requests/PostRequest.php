<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
 

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->input('id');
        return [
             'title'=>"bail|required|unique:post,title,{$this ->input ('id')}",
            'body'=>'required',
        ];
    }

     public function messages()
    {
        return[
            'title.required'=>'Field is required',
            'body.required'=>'Field is required',

        ];
    }

}
