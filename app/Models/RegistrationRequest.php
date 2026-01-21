<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RegistrationRequest extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'track_registration_requests')
            ->withPivot('sort')
            ->withTimestamps();
    }

    public function trackRegistrationRequests(): HasMany
    {
        return $this->hasMany(TrackRegistrationRequest::class)->orderBy('sort');
    }
}
