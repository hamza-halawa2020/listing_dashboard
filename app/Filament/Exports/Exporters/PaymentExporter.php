<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Payment;

class PaymentExporter extends BaseExporter
{
    protected static ?string $model = Payment::class;

    protected static array $excludedColumns = [
        'attachment',
    ];

    protected static array $relationshipColumns = [
        'user.name',
        'user.phone',
        'user.national_id',
        'subscription.membership_card_number',
    ];

    protected static array $columnLabels = [
        'user_id'               => 'User ID',
        'subscription_id'       => 'Subscription ID',
        'referral_id'           => 'Referral ID',
        'location_id'           => 'Location ID',
        'amount'                => 'Amount',
        'original_amount'       => 'Original Amount',
        'discount_amount'       => 'Discount',
        'payment_method'        => 'Payment Method',
        'transaction_reference' => 'Transaction Reference',
        'status'                => 'Status',
        'shipping_cost'         => 'Shipping Cost',
        'paid_at'               => 'Paid At',
        'user.name'             => 'User Name',
        'user.phone'            => 'User Phone',
        'user.national_id'      => 'National ID',
        'subscription.membership_card_number' => 'Membership Number',
    ];
}
