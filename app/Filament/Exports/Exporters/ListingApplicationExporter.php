<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\ListingApplication;

class ListingApplicationExporter extends BaseExporter
{
    protected static ?string $model = ListingApplication::class;

    protected static array $relationshipColumns = [
        'listing.name',
        'user.name',
        'user.phone',
        'reviewer.name',
    ];

    protected static array $columnLabels = [
        'listing_id'       => 'Listing ID',
        'user_id'           => 'User ID',
        'contact_name'      => 'Contact Name',
        'contact_email'     => 'Contact Email',
        'contact_phone'     => 'Contact Phone',
        'status'            => 'Status',
        'rejection_reason'  => 'Rejection Reason',
        'admin_notes'       => 'Admin Notes',
        'reviewed_by'       => 'Reviewed By',
        'reviewed_at'       => 'Reviewed At',
        'listing.name'      => 'Business Name',
        'user.name'         => 'User Name',
        'user.phone'        => 'User Phone',
        'reviewer.name'     => 'Reviewer',
    ];
}
