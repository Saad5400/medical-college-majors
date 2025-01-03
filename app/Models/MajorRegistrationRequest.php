<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MajorRegistrationRequest extends Pivot
{
    protected $table = 'major_registration_request';

    protected $fillable = [
        'major_id',
        'registration_request_id',
        'sort',
    ];

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function registrationRequest(): BelongsTo
    {
        return $this->belongsTo(RegistrationRequest::class);
    }
}
