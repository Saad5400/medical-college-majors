<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Major extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'name',
        'max_users',
    ];

    public function registrationRequests(): BelongsToMany
    {
        return $this->belongsToMany(RegistrationRequest::class)->withPivot('sort');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
