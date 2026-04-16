<?php

return [
    'navigation_group' => 'Referral & Rewards',
    'model_label' => 'Point Setting',
    'plural_model_label' => 'Point Settings',
    'units' => [
        'point' => 'point',
        'points' => 'points',
        'egp' => 'EGP',
    ],
    'defaults' => [
        'reason' => 'Updated from admin panel',
        'initial_notes' => 'Initial setup: 1 point = 10 piasters',
    ],
    'page' => [
        'title' => 'Point Settings',
        'subheading' => 'Manage the point value, preview conversions quickly, and review change history easily. Current rate: :rate EGP per point.',
    ],
    'sections' => [
        'rate' => [
            'title' => 'Point Conversion Rate',
            'description' => 'Update the point value in Egyptian Pounds and preview common conversions before saving.',
        ],
        'notes' => [
            'title' => 'Update Notes',
            'description' => 'Add the reason and notes so the history stays clear for anyone reviewing later.',
        ],
    ],
    'fields' => [
        'points_to_egp_rate' => [
            'label' => 'Point Value in EGP',
            'helper' => 'Example: 0.1000 means one point equals 10 piasters.',
            'suffix' => 'per point',
        ],
        'reason_visible' => [
            'label' => 'Reason for Change',
            'helper' => 'This short reason is stored in the history log.',
        ],
        'notes' => [
            'label' => 'Additional Notes',
            'helper' => 'Any extra details that explain the update for your team.',
        ],
    ],
    'placeholders' => [
        'summary' => [
            'label' => 'Quick Summary',
            'invalid_rate' => 'Enter a valid rate to preview conversions.',
            'content' => '1 point = :rate EGP | 100 EGP = :points points | 1000 points = :egp EGP',
        ],
        'example_100_egp' => ['label' => 'How many points equal 100 EGP?'],
        'example_1000_egp' => ['label' => 'How many points equal 1000 EGP?'],
        'example_100_points' => ['label' => 'How much is 100 points in EGP?'],
        'example_1000_points' => ['label' => 'How much is 1000 points in EGP?'],
    ],
    'table' => [
        'current_rate' => 'Current Rate',
        'rate_format_suffix' => 'EGP / point',
        'current_rate_description' => '100 EGP = :points points',
        'quick_preview' => 'Quick Preview',
        'quick_preview_description' => 'Value of 1000 points',
        'latest_notes' => 'Latest Notes',
        'no_notes' => 'No notes available',
        'last_updated' => 'Last Updated',
    ],
    'actions' => [
        'edit_rate' => 'Edit Rate',
        'edit_modal_heading' => 'Update Point Conversion Rate',
        'edit_modal_description' => 'Adjust the point value, then review the quick previews before saving.',
        'save_changes' => 'Save Changes',
        'edit_success' => 'Point rate updated successfully',
    ],
    'header_actions' => [
        'history' => 'Rate History',
        'history_tooltip' => 'View all point rate changes and who made them.',
        'calculator' => 'Conversion Calculator',
        'calculator_tooltip' => 'Test EGP and point conversions before saving.',
    ],
    'calculator' => [
        'heading' => 'Point Conversion Calculator',
        'description' => 'Enter any values to preview the conversion using the current rate.',
        'submit' => 'Show Result',
        'amount_egp' => 'Amount in EGP',
        'amount_egp_helper' => 'Example amount you want to convert into points.',
        'amount_points' => 'Points Amount',
        'amount_points_helper' => 'Example points amount you want to convert into EGP.',
        'result_title' => 'Conversion Result',
        'result_body' => "Current rate: :rate EGP per point\n:egp :egp_word = :egp_points :point_word\n:points :point_word = :points_egp :egp_word",
    ],
    'history' => [
        'title' => 'Point Rate History',
        'subheading' => 'Review every point rate update, including the reason and who made it.',
        'back' => 'Back to Settings',
        'hero_eyebrow' => 'Point Conversion Management',
        'hero_title' => 'Track changes and review every update easily',
        'hero_description' => 'This page brings together the current rate, number of changes, and latest update, with a clear table for reviewing the full history quickly.',
        'current_rate_card' => 'Current Rate',
        'current_rate_suffix' => 'EGP per point',
        'timeline_title' => 'Change History',
        'timeline_description' => 'Review the old and new rates, change percentage, reason, and who made each update.',
        'cards' => [
            'current_rate' => [
                'title' => 'Current Rate',
                'suffix' => 'EGP / point',
                'description' => '100 EGP equals about :points points.',
            ],
            'total_changes' => [
                'title' => 'Total Changes',
                'description' => 'Every saved rate update is recorded automatically for review and tracking.',
            ],
            'last_change' => [
                'title' => 'Last Change',
                'none' => 'None',
                'reason' => 'Reason: :reason',
                'empty' => 'No rate updates have been recorded yet.',
            ],
        ],
        'table' => [
            'old_rate' => 'Old Rate',
            'new_rate' => 'New Rate',
            'rate_suffix' => 'EGP/point',
            'change' => 'Change %',
            'reason' => 'Reason',
            'undefined' => 'Undefined',
            'changed_by' => 'Changed By',
            'system' => 'System',
            'changed_at' => 'Changed At',
        ],
    ],
];
