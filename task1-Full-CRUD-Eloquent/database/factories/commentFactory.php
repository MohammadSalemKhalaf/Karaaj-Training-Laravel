<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Comment; 
use App\Models\Post; 


class CommentFactory extends Factory
{
  protected $model = Comment::class;
    public function definition(): array
    {
        return [

        'post_id' => Post::factory(),
        "content"=> $this->faker->sentence,
        'author'=>$this->faker->name,
        

            //
        ];
    }
}
