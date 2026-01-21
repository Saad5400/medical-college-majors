<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FacilityRegistrationRequest extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'user_id',
        'month_index',
        'assigned_facility_id',
    ];

    protected function casts(): array
    {
        return [
            'month_index' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedFacility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'assigned_facility_id');
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(FacilityWish::class)->orderBy('priority');
    }

    public function competitiveWishes(): HasMany
    {
        return $this->hasMany(FacilityWish::class)
            ->where('is_competitive', true)
            ->orderBy('priority');
    }
}
