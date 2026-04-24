<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'listing_id',
        'notes',
        'status',
        'rejection_reason',
        'approved_by_admin_id',
        'approved_at',
        'visited_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'visited_at'  => 'datetime',
    ];

    public static function getVisitPoints(): int
    {
        return (int) Setting::getValue('visit_points_reward', 10);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function attachments()
    {
        return $this->hasMany(VisitAttachment::class);
    }

    public function approvedByAdmin()
    {
        return $this->belongsTo(User::class, 'approved_by_admin_id');
    }

    public function pointTransaction()
    {
        return $this->hasOne(PointTransaction::class, 'note', 'id')
            ->where('type', 'visit_bonus');
    }
}
