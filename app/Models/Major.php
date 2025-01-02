<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Major extends Model
{
    protected $fillable = [
        'name',
        'max_users',
    ];

    public function registrationRequests(): BelongsToMany
    {
        return $this->belongsToMany(RegistrationRequest::class)->withPivot('sort');
    }
}
