<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Track extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'name',
        'max_users',
        'is_leader_only',
        'elective_months',
        'sort',
    ];

    protected $casts = [
        'is_leader_only' => 'boolean',
        'elective_months' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $track): void {
            if ($track->sort !== null) {
                return;
            }

            $maxSort = (int) (static::query()->max('sort') ?? 0);
            $track->sort = $maxSort + 1;
        });
    }

    public function registrationRequests(): BelongsToMany
    {
        return $this->belongsToMany(RegistrationRequest::class, 'track_registration_requests')
            ->withPivot('sort')
            ->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'track_specializations')
            ->withPivot(['month_index'])
            ->withTimestamps();
    }

    public function trackSpecializations(): HasMany
    {
        return $this->hasMany(TrackSpecialization::class)->orderBy('month_index');
    }

    /**
     * @return array<int, int>
     */
    public function getElectiveMonths(): array
    {
        return $this->normalizeMonths($this->elective_months ?? []);
    }

    /**
     * @return array<int, int>
     */
    public function getSpecializationMonths(?int $ignoreTrackSpecializationId = null): array
    {
        $query = $this->trackSpecializations()->with('specialization');

        if ($ignoreTrackSpecializationId) {
            $query->whereKeyNot($ignoreTrackSpecializationId);
        }

        $months = [];

        foreach ($query->get() as $trackSpecialization) {
            $durationMonths = $this->normalizeDuration($trackSpecialization->specialization?->duration_months);
            $startMonth = (int) $trackSpecialization->month_index;
            $endMonth = min(12, $startMonth + $durationMonths - 1);

            for ($month = $startMonth; $month <= $endMonth; $month++) {
                $months[$month] = true;
            }
        }

        $months = array_keys($months);
        sort($months);

        return $months;
    }

    /**
     * @param  array<int, int|string>  $electiveMonths
     * @return array<int, int>
     */
    public function getConflictingElectiveMonths(array $electiveMonths): array
    {
        $selectedMonths = $this->normalizeMonths($electiveMonths);
        $conflicts = array_intersect($selectedMonths, $this->getSpecializationMonths());

        $conflicts = array_values(array_unique($conflicts));
        sort($conflicts);

        return $conflicts;
    }

    private function normalizeDuration(?int $durationMonths): int
    {
        return max(1, (int) $durationMonths);
    }

    /**
     * @param  array<int, int|string>  $months
     * @return array<int, int>
     */
    private function normalizeMonths(array $months): array
    {
        $normalized = [];

        foreach ($months as $month) {
            $month = (int) $month;

            if ($month < 1 || $month > 12) {
                continue;
            }

            $normalized[$month] = true;
        }

        $months = array_keys($normalized);
        sort($months);

        return $months;
    }
}
