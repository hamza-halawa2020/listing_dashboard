<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PointRateHistory extends Model
{
    protected $table = 'point_rate_history';

    protected $fillable = [
        'old_rate',
        'new_rate',
        'reason',
        'changed_by_admin_id',
    ];

    protected $casts = [
        'old_rate' => 'decimal:4',
        'new_rate' => 'decimal:4',
    ];

    /**
     * Get the admin who made the change
     */
    public function changedByAdmin()
    {
        return $this->belongsTo(User::class, 'changed_by_admin_id');
    }

    /**
     * Get formatted old rate
     */
    public function getFormattedOldRateAttribute(): string
    {
        return number_format($this->old_rate, 4) . ' EGP';
    }

    /**
     * Get formatted new rate
     */
    public function getFormattedNewRateAttribute(): string
    {
        return number_format($this->new_rate, 4) . ' EGP';
    }

    /**
     * Get rate change percentage
     */
    public function getChangePercentageAttribute(): float
    {
        if ($this->old_rate == 0) return 0;
        
        return (($this->new_rate - $this->old_rate) / $this->old_rate) * 100;
    }
}
