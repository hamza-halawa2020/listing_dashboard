<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\PriceRequest;

class PriceRequestExporter extends BaseExporter
{
    protected static ?string $model = PriceRequest::class;

    protected static array $columnLabels = [
        'company_name'           => 'Company Name',
        'company_type'           => 'Company Type',
        'contact_person'         => 'Contact Person',
        'phone'                  => 'Phone',
        'email'                  => 'Email',
        'services_needed'        => 'Services Needed',
        'additional_requirements' => 'Additional Requirements',
        'budget_range'           => 'Budget Range',
        'timeline'               => 'Timeline',
        'status'                => 'Status',
        'response_notes'         => 'Response Notes',
    ];
}
