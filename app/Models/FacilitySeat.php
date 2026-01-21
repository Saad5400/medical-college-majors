<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FacilitySeat extends Model
{
    use LogsActivity, HasFactory;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'facility_id',
        'specialization_id',
        'month_index',
        'max_seats',
    ];

    protected function casts(): array
    {
        return [
            'month_index' => 'integer',
            'max_seats' => 'integer',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }
}
