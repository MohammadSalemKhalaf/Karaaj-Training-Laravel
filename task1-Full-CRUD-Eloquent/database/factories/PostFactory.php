<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Post; 


class PostFactory extends Factory
{
  protected $model = Post::class;
    public function definition(): array
    {
        return [

        'id' => Str::uuid()->toString(),
        "title"=> $this->faker->sentence,
        "body"=> $this->faker->paragraph,
        'author'=>$this->faker->name,
        'published'=>$this->faker->boolean,

            //
        ];
    }
}
