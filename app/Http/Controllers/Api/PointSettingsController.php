<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PointSetting;
use App\Models\PointRateHistory;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PointSettingsController extends Controller
{
    public function index()
    {
        $settings = PointSetting::first();
        $history = PointRateHistory::with('changedByAdmin')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'settings' => $settings,
            'history' => $history,
            'current_rate' => $settings?->points_to_egp_rate ?? 0.1000,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'points_to_egp_rate' => 'required|numeric|min:0.0001|max:9999.9999',
            'notes' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|max:500',
        ]);

        $currentSetting = PointSetting::first();
        $oldRate = $currentSetting?->points_to_egp_rate ?? 0.1000;
        $newRate = (float) $validated['points_to_egp_rate'];

        // Prevent setting the same rate
        if (abs($oldRate - $newRate) < 0.0001) {
            throw ValidationException::withMessages([
                'points_to_egp_rate' => 'New rate must be different from current rate.',
            ]);
        }

        return DB::transaction(function () use ($validated, $oldRate, $newRate, $currentSetting) {
            // Record the change in history
            PointRateHistory::create([
                'old_rate' => $oldRate,
                'new_rate' => $newRate,
                'reason' => $validated['reason'] ?? 'Rate updated by admin',
                'changed_by_admin_id' => auth()->id(),
            ]);

            // Update or create settings
            $settings = PointSetting::updateOrCreate(['id' => 1], [
                'points_to_egp_rate' => $newRate,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'message' => 'Point settings updated successfully',
                'settings' => $settings,
                'old_rate' => $oldRate,
                'new_rate' => $newRate,
                'note' => 'Points for all plans will be calculated automatically based on the new rate',
            ]);
        });
    }

    public function calculatePoints(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:999999.99',
        ]);

        $points = PointSetting::calculatePointsNeeded((float) $validated['amount']);
        $rate = PointSetting::getCurrentRate();

        return response()->json([
            'amount' => $validated['amount'],
            'points_needed' => $points,
            'rate' => $rate,
            'formatted' => number_format($points) . ' points',
        ]);
    }

    public function calculateEgp(Request $request)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:0|max:999999',
        ]);

        $egpValue = PointSetting::calculateEgpValue((int) $validated['points']);
        $rate = PointSetting::getCurrentRate();

        return response()->json([
            'points' => $validated['points'],
            'egp_value' => $egpValue,
            'rate' => $rate,
            'formatted' => 'EGP ' . number_format($egpValue, 2),
        ]);
    }

    public function history()
    {
        $history = PointRateHistory::with('changedByAdmin')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($history);
    }
}
