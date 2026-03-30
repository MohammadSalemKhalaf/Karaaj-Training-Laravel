<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'employee_id',
        'amount',
        'bonuses',
        'deductions',
        'effective_date',
        'notes',
    ];

    protected $appends = [
        'net_salary',
    ];

    protected static function booted(): void
    {
        static::saving(function (Salary $salary): void {
            $salary->setAttribute('net_salary', $salary->calculateNetSalary());
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'bonuses' => 'decimal:2',
            'deductions' => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    public function getNetSalaryAttribute(): float
    {
        return round($this->calculateNetSalary(), 2);
    }

    private function calculateNetSalary(): float
    {
        $amount = is_numeric($this->amount) ? (float) $this->amount : 0.0;
        $bonuses = is_numeric($this->bonuses) ? (float) $this->bonuses : 0.0;
        $deductions = is_numeric($this->deductions) ? (float) $this->deductions : 0.0;

        return $amount + $bonuses - $deductions;
    }
}
