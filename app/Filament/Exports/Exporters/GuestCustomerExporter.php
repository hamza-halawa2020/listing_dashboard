<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Contact;

class GuestCustomerExporter extends BaseExporter
{
    protected static ?string $model = Contact::class;

    protected static array $excludedColumns = [
        'password',
        'remember_token',
        'email_verified_at',
        'referred_by_user_id',
        'source',
    ];

    protected static array $columnLabels = [
        'name'              => 'Name',
        'phone'             => 'Phone',
        'message'           => 'Latest Comment',
        'comment_count'     => 'Comments',
        'last_commented_at' => 'Last Commented At',
    ];
}
