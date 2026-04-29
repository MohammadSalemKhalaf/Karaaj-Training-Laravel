<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;


class Company extends Model
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $table = "companies";

    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        'name',
        'address',
        'industry',
        'website',
        'owner_id'
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function jobVacancies()
    {
        return $this->hasMany(JobVacancy::class, 'company_id', 'id');
    }

    public function jobApplications(): HasManyThrough
    {
        return $this->hasManyThrough(
            JobApplication::class,
            JobVacancy::class,
            'company_id',
            'job_vacancy_id',
            'id',
            'id'
        );
    }
}
