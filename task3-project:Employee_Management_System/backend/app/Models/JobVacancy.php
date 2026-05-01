<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class JobVacancy extends Model
{
    use HasFactory, Notifiable,HasUuids,SoftDeletes;

    protected $table = "job_vacancies";
      protected $keyType="string";
    public $incrementing = false;


    protected $fillable = [
        'title',
        'description',
        'location',
        'salary',
        'type',
        'view_count',
        'category_id',
        'company_id'
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

    public function jobCategory()
    {
        return $this->belongsTo(JobCategory::class,'category_id','id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class,'company_id','id');
    }

    public function jobApplications()
{
    return $this->hasMany(JobApplication::class, 'job_vacancy_id', 'id');
}
}
