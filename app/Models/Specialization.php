<?php

namespace App\Models;

use App\Enums\FacilityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Specialization extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'name',
        'duration_months',
        'facility_type',
    ];

    protected function casts(): array
    {
        return [
            'duration_months' => 'integer',
            'facility_type' => FacilityType::class,
        ];
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'track_specialization')
            ->withPivot(['month_index'])
            ->withTimestamps();
    }

    public function trackSpecializations(): HasMany
    {
        return $this->hasMany(TrackSpecialization::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'facility_seats')
            ->withPivot(['month_index', 'max_seats'])
            ->withTimestamps();
    }
}
