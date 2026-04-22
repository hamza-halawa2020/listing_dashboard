<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationRewardSetting extends Model
{
    protected $fillable = [
        'points',
        'notes',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public static function getPoints(): int
    {
        return max((int) (static::query()->first()?->points ?? 0), 0);
    }

    public static function getOrCreateDefault(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'points' => 0,
        ]);
    }
}
