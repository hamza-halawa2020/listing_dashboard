<?php

namespace App\Support;

use Illuminate\Support\Facades\Blade;

/**
 * Provides a curated list of Heroicon outline icons suitable for
 * medical / healthcare category selection.
 */
class CategoryIcons
{
    /**
     * Raw icon => label mapping (100 icons).
     *
     * @return array<string, string>
     */
    public static function icons(): array
    {
        return [
            // ── Medical specialties ───────────────────────────────────
            'heroicon-o-heart'                     => 'Cardiology / Heart',
            'heroicon-o-eye'                       => 'Ophthalmology / Eye',
            'heroicon-o-face-smile'                => 'Dental / Dental Care',
            'heroicon-o-cpu-chip'                 => 'Neurology / Brain',
            'heroicon-o-hand-raised'              => 'Orthopedics / Physiotherapy',
            'heroicon-o-sparkles'                 => 'Beauty / Cosmetic',
            'heroicon-o-sun'                      => 'Dermatology / Skin',
            'heroicon-o-beaker'                   => 'Laboratory / Testing',
            'heroicon-o-scissors'                 => 'Surgery',
            'heroicon-o-bug-ant'                  => 'Infectious Diseases',
            'heroicon-o-puzzle-piece'             => 'Therapy / Integration',
            'heroicon-o-paint-brush'              => 'Art Therapy',
            'heroicon-o-musical-note'             => 'Music Therapy',
            'heroicon-o-moon'                     => 'Sleep Disorders',
            'heroicon-o-camera'                   => 'Radiology / Imaging',
            'heroicon-o-cube'                     => 'Medical Devices / Equipment',
            'heroicon-o-cube-transparent'          => '3D Imaging',
            'heroicon-o-user-group'               => 'Family Medicine',
            'heroicon-o-users'                    => 'General Practice',
            'heroicon-o-user'                     => 'Patient / Individual',
            'heroicon-o-user-circle'              => 'Patient Profile',
            'heroicon-o-briefcase'                => 'Specialist / Consultant',
            'heroicon-o-academic-cap'             => 'Medical Education',
            'heroicon-o-book-open'                => 'Medical Research',
            'heroicon-o-building-library'         => 'Medical Library',
            'heroicon-o-building-office-2'        => 'Hospital / Clinic',
            'heroicon-o-building-office'          => 'Medical Office',
            'heroicon-o-building-storefront'      => 'Pharmacy / Store',
            'heroicon-o-wrench'                   => 'Medical Tools',
            'heroicon-o-wrench-screwdriver'       => 'Repair / Maintenance',

            // ── Emergency & alerts ───────────────────────────────────
            'heroicon-o-bolt'                     => 'Emergency',
            'heroicon-o-lifebuoy'                 => 'Life-Saving / Rescue',
            'heroicon-o-fire'                     => 'Urgent / Critical',
            'heroicon-o-exclamation-triangle'    => 'Warning / Alert',
            'heroicon-o-exclamation-circle'      => 'Alert',
            'heroicon-o-shield-exclamation'      => 'Safety Warning',
            'heroicon-o-bell-alert'              => 'Urgent Notification',
            'heroicon-o-no-symbol'               => 'Restricted',
            'heroicon-o-x-circle'                => 'Cancelled / Error',

            // ── Records & documents ─────────────────────────────────
            'heroicon-o-clipboard-document-check'=> 'Medical Records',
            'heroicon-o-clipboard-document-list' => 'Records List',
            'heroicon-o-clipboard-document'      => 'Medical Document',
            'heroicon-o-clipboard'               => 'Notes / Clipboard',
            'heroicon-o-document-text'           => 'Prescription',
            'heroicon-o-document'                => 'Medical Document',
            'heroicon-o-document-check'          => 'Verified Document',
            'heroicon-o-document-plus'           => 'Add Record',
            'heroicon-o-document-magnifying-glass'=> 'Record Search',
            'heroicon-o-document-chart-bar'     => 'Medical Report',
            'heroicon-o-circle-stack'            => 'Database / Records',
            'heroicon-o-archive-box'             => 'Medical Archive',
            'heroicon-o-folder'                  => 'Medical Folder',
            'heroicon-o-folder-open'             => 'Open Records',

            // ── Verification & certification ─────────────────────────
            'heroicon-o-check-badge'             => 'Certified / Accredited',
            'heroicon-o-check-circle'            => 'Confirmed / Verified',
            'heroicon-o-check'                   => 'Verified',
            'heroicon-o-shield-check'            => 'Insurance / Protection',
            'heroicon-o-finger-print'            => 'Identity Verification',
            'heroicon-o-identification'          => 'ID / Identification',
            'heroicon-o-lock-closed'            => 'Private / Confidential',
            'heroicon-o-eye-slash'               => 'Hidden / Private',

            // ── Communication ────────────────────────────────────────
            'heroicon-o-phone'                   => 'Hotline / Contact',
            'heroicon-o-phone-arrow-down-left'   => 'Incoming Call',
            'heroicon-o-phone-arrow-up-right'    => 'Outgoing Call',
            'heroicon-o-chat-bubble-left'        => 'Message / Consultation',
            'heroicon-o-chat-bubble-left-right'  => 'Consultation / Chat',
            'heroicon-o-chat-bubble-oval-left'    => 'Chat Support',
            'heroicon-o-envelope'                => 'Email Contact',
            'heroicon-o-at-symbol'               => 'Email / Contact',
            'heroicon-o-megaphone'               => 'Announcements',
            'heroicon-o-bell'                    => 'Notifications',
            'heroicon-o-question-mark-circle'   => 'Help / FAQ',
            'heroicon-o-information-circle'     => 'Info / Details',
            'heroicon-o-light-bulb'             => 'Health Tips / Advice',

            // ─­ Scheduling & location ─────────────────────────────────
            'heroicon-o-clock'                   => '24-Hour Service',
            'heroicon-o-calendar'               => 'Appointment / Booking',
            'heroicon-o-calendar-days'          => 'Schedule',
            'heroicon-o-map-pin'                 => 'Location',
            'heroicon-o-map'                     => 'Service Area',
            'heroicon-o-queue-list'              => 'Queue / Waiting Room',
            'heroicon-o-home'                    => 'Home Care',
            'heroicon-o-home-modern'            => 'Home Healthcare',
            'heroicon-o-truck'                  => 'Medical Transport',

            // ── Payments & offers ────────────────────────────────────
            'heroicon-o-wallet'                  => 'Payment / Wallet',
            'heroicon-o-credit-card'            => 'Payment Card',
            'heroicon-o-banknotes'              => 'Cash Payment',
            'heroicon-o-receipt-percent'        => 'Receipt / Discount',
            'heroicon-o-percent-badge'          => 'Discount Rate',
            'heroicon-o-gift'                   => 'Offers / Discounts',
            'heroicon-o-gift-top'               => 'Special Offer',
            'heroicon-o-ticket'                => 'Coupon / Voucher',

            // ── Ratings & achievements ───────────────────────────────
            'heroicon-o-star'                   => 'Featured / Rating',
            'heroicon-o-trophy'                 => 'Award / Excellence',
            'heroicon-o-arrow-trending-up'      => 'Improving / Recovery',
            'heroicon-o-rocket-launch'          => 'New / Launch',
            'heroicon-o-swatch'                  => 'Color / Category',
            'heroicon-o-tag'                    => 'Tag / Label',
            'heroicon-o-hashtag'                => 'Topic / Tag',
            'heroicon-o-flag'                   => 'Report / Flag',
            'heroicon-o-newspaper'              => 'Health News / Articles',
        ];
    }

    /**
     * Returns the options array with rendered SVG icons suitable
     * for use in a Filament Select component with ->allowHtml().
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(static::icons())
            ->mapWithKeys(function (string $label, string $icon): array {
                return [$icon => Blade::render("@svg('{$icon}', 'w-5 h-5 inline') {$label}")];
            })
            ->toArray();
    }
}
