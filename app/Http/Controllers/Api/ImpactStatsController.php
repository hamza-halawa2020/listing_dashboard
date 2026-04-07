<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ImpactStatsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $activeListings = Listing::query()->where('is_active', true);

        $membersCount = User::query()
            ->where('role', 'member')
            ->count();

        $listingsCount = (clone $activeListings)->count();

        $governoratesCount = (clone $activeListings)
            ->join('locations', 'listings.location_id', '=', 'locations.id')
            ->selectRaw('COUNT(DISTINCT COALESCE(locations.parent_id, locations.id)) as aggregate')
            ->value('aggregate');

        $maxDiscountPercentage = Offer::query()
            ->join('listings', 'offers.listing_id', '=', 'listings.id')
            ->where('offers.is_active', true)
            ->where('listings.is_active', true)
            ->max('offers.discount_percentage');

        return response()->json([
            'data' => [
                'members_count' => $membersCount,
                'listings_count' => $listingsCount,
                'governorates_count' => (int) ($governoratesCount ?? 0),
                'max_discount_percentage' => $maxDiscountPercentage !== null
                    ? (float) $maxDiscountPercentage
                    : 0.0,
            ],
        ]);
    }
}
