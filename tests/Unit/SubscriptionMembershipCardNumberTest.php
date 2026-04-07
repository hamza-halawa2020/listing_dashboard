<?php

namespace Tests\Unit;

use App\Models\Subscription;
use App\Models\User;
use Tests\TestCase;

class SubscriptionMembershipCardNumberTest extends TestCase
{
    public function test_it_generates_membership_card_number_from_subscription_order_and_national_id_suffix(): void
    {
        $user = new User([
            'national_id' => '29501142201699',
        ]);

        $firstSubscription = new Subscription();
        $firstSubscription->id = 1;
        $firstSubscription->setRelation('user', $user);

        $secondSubscription = new Subscription();
        $secondSubscription->id = 2;
        $secondSubscription->setRelation('user', $user);

        $thousandthSubscription = new Subscription();
        $thousandthSubscription->id = 1000;
        $thousandthSubscription->setRelation('user', $user);

        $this->assertSame('000011699', $firstSubscription->generateMembershipCardNumber());
        $this->assertSame('000021699', $secondSubscription->generateMembershipCardNumber());
        $this->assertSame('010001699', $thousandthSubscription->generateMembershipCardNumber());
    }
}
