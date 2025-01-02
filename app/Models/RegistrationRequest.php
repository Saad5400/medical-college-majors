<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'user_gpa',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function majors(): BelongsToMany
    {
        return $this->belongsToMany(Major::class)->withPivot('sort');
    }
}
