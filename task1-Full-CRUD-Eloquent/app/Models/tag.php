<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;



class tag extends Model
{
         use HasFactory;
        use HasUuids;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
 protected $table="tag";
 protected $fillable=['title'];
 protected $guarded=['id'];

   public function posts(){
        return $this->belongsToMany(Post::class);
    }

}
