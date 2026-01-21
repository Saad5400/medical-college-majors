<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FacilityWish extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'facility_registration_request_id',
        'priority',
        'facility_id',
        'specialization_id',
        'custom_facility_name',
        'custom_specialization_name',
        'is_custom',
        'is_competitive',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_custom' => 'boolean',
            'is_competitive' => 'boolean',
        ];
    }

    public function facilityRegistrationRequest(): BelongsTo
    {
        return $this->belongsTo(FacilityRegistrationRequest::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    /**
     * Get the display name for the facility (either registered or custom).
     */
    public function getFacilityDisplayName(): string
    {
        if ($this->is_custom && $this->custom_facility_name) {
            return $this->custom_facility_name.' (مخصص)';
        }

        return $this->facility?->name ?? 'غير محدد';
    }

    /**
     * Get the display name for the specialization (either registered or custom).
     */
    public function getSpecializationDisplayName(): ?string
    {
        if ($this->custom_specialization_name) {
            return $this->custom_specialization_name.' (مخصص)';
        }

        return $this->specialization?->name;
    }
}
