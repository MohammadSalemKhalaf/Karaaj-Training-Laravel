<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Throwable;

class AttendanceRecord extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'notes',
    ];

    protected $appends = [
        'worked_hours',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_time' => 'datetime',
            'check_out_time' => 'datetime',
        ];
    }

    public function getWorkedHoursAttribute(): ?float
    {
        $checkIn = $this->resolveCarbonDateTime($this->check_in_time);
        $checkOut = $this->resolveCarbonDateTime($this->check_out_time);

        if (! $checkIn || ! $checkOut) {
            return null;
        }

        $minutes = $checkIn->diffInMinutes($checkOut, false);

        if ($minutes < 0) {
            return null;
        }

        return round($minutes / 60, 2);
    }

    private function resolveCarbonDateTime(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
