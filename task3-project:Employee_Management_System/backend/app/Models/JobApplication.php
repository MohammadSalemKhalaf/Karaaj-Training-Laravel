<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class JobApplication extends Model
{
     use HasFactory, Notifiable,HasUuids,SoftDeletes;
    protected $table='job_applications';
        protected $keyType="string";
    public $incrementing = false;

    protected $fillable = [
        'status',
        'aiGeneratedScore',
        'aiGeneratedFeedback',
        'user_id',
        'resume_id',
        'job_vacancy_id'
    ];

      protected $dates = [
        'deleted_at'
    ];

        protected function casts(): array
    {
        return [
            'deleted_at'=> 'datetime',
        ];
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function resume(){
        return $this->belongsTo(Resume::class,'resume_id','id');

    }

    public function jobVacancy(){
        return $this->belongsTo(JobVacancy::class,'job_vacancy_id','id');
    }

}
