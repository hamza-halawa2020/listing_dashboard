<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitPointRewardHistory extends Model
{
    protected $table = 'visit_point_reward_history';

    protected $fillable = [
        'old_points',
        'new_points',
        'reason',
        'changed_by_admin_id',
    ];

    protected $casts = [
        'old_points' => 'integer',
        'new_points' => 'integer',
    ];

    public function changedByAdmin()
    {
        return $this->belongsTo(User::class, 'changed_by_admin_id');
    }
}
