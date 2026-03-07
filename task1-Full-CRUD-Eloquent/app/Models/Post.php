<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use hasfactory;
class Post extends Model
{
     use HasFactory;
    use HasUuids;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'post';
    protected $fillable = ['title','body','published','user_id'];//can modefied
    protected $guarded = ['id'];//read only


    public function comments(){
        return $this->hasMany(Comment::class);
    }

     public function tags(){
        return $this->belongsToMany(tag::class);
    }

         public function user(){
        return $this->belongsTo(User::class);
    }

    //
}
