<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FacilityRegistrationRequest extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'user_id',
        'month_index',
        'assigned_facility_id',
        'assigned_specialization_id',
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

    public function assignedSpecialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class, 'assigned_specialization_id');
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(FacilityWish::class)->orderBy('priority');
    }

    /**
     * @return Collection<int, FacilityWish>
     */
    public function getCompetitiveWishesAttribute(): Collection
    {
        $wishes = $this->relationLoaded('wishes')
            ? $this->wishes
            : $this->wishes()->get();

        $competitiveWishes = collect();

        foreach ($wishes->sortBy('priority') as $wish) {
            if ($wish->is_custom) {
                break;
            }

            $competitiveWishes->push($wish);
        }

        return $competitiveWishes->values();
    }
}
