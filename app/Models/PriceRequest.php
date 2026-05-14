<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceRequest extends Model
{
    protected $table = 'price_requests';

    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'company_type',
        'employee_count',
        'services_needed',
        'additional_requirements',
        'budget_range',
        'timeline',
        'status',
        'responded_by',
        'responded_at',
        'response_notes',
        'created_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'employee_count' => 'integer',
        'responded_at' => 'datetime',
    ];

    protected function companyTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => match ($this->company_type) {
                'individual' => __('Individual'),
                'company' => __('Company'),
                'organization' => __('Organization'),
                default => $this->company_type,
            },
        );
    }

    protected function budgetRangeLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => match ($this->budget_range) {
                'under_1000' => __('Under 1000 EGP'),
                '1000_5000' => __('1000 - 5000 EGP'),
                '5000_10000' => __('5000 - 10000 EGP'),
                '10000_25000' => __('10000 - 25000 EGP'),
                'over_25000' => __('Over 25000 EGP'),
                default => $this->budget_range,
            },
        );
    }

    protected function timelineLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => match ($this->timeline) {
                'urgent' => __('Urgent (1 week)'),
                'week' => __('1 Week'),
                'month' => __('1 Month'),
                'quarter' => __('3 Months'),
                'flexible' => __('Flexible'),
                default => $this->timeline,
            },
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', false);
    }

    public function scopeResponded($query)
    {
        return $query->where('status', true);
    }
}

