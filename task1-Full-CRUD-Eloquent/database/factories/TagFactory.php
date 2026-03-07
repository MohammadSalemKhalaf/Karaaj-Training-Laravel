<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\models\tag;



class TagFactory extends Factory
{
  protected $model = Tag::class;
    public function definition(): array
    {
        return [
        'id' => Str::uuid()->toString(),
        "title"=> $this->faker->sentence,

            //
        ];
    }
}
