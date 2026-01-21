<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Track extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'name',
        'max_users',
        'is_leader_only',
        'elective_months',
    ];

    protected function casts(): array
    {
        return [
            'is_leader_only' => 'boolean',
            'elective_months' => 'array',
        ];
    }

    public function registrationRequests(): BelongsToMany
    {
        return $this->belongsToMany(RegistrationRequest::class)->withPivot('sort');
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
}
