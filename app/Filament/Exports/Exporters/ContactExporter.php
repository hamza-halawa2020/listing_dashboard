<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Contact;

class ContactExporter extends BaseExporter
{
    protected static ?string $model = Contact::class;

    protected static array $columnLabels = [
        'name'    => 'Name',
        'phone'   => 'Phone',
        'email'   => 'Email',
        'message' => 'Message',
    ];
}
