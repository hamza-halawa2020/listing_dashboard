<?php

return [
    'filters' => [
        'title' => 'Analytics Filters',
        'description' => 'Select a single day or a custom range for every dashboard widget.',
        'from' => 'From',
        'to' => 'To',
    ],
    'range' => [
        'separator' => 'to',
    ],
    'overview' => [
        'heading' => 'System Snapshot',
        'description' => 'Key numbers for :range',
        'new_users' => 'New Users',
        'service_providers' => 'Service Providers',
        'new_listings' => 'New Listings',
        'active_offers' => 'Active Offers',
        'new_subscriptions' => 'New Subscriptions',
        'completed_payments' => 'Completed Payments',
        'completed_revenue' => 'Completed Revenue',
        'contact_messages' => 'Contact Messages',
    ],
    'growth' => [
        'heading' => 'Growth Trend',
        'description' => 'Daily records for :range',
        'users' => 'Users',
        'listings' => 'Listings',
        'subscriptions' => 'Subscriptions',
        'contacts' => 'Contacts',
    ],
    'payments' => [
        'heading' => 'Payments Breakdown',
        'description' => 'Completed revenue for :range: :amount EGP',
        'dataset' => 'Payments',
        'statuses' => [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ],
    ],
    'content' => [
        'heading' => 'Content and Community Activity',
        'description' => 'Published content and approvals for :range',
        'published_posts' => 'Published Posts',
        'approved_reviews' => 'Approved Reviews',
        'family_members' => 'Family Members',
    ],
    'languages' => [
        'english' => 'English',
        'arabic' => 'العربية',
    ],
];
