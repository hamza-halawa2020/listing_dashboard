<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreListingApplicationRequest;
use App\Models\ListingApplication;
use App\Services\ListingApplicationService;
use Illuminate\Http\JsonResponse;

class ListingApplicationController extends ApiController
{
    public function __construct(private ListingApplicationService $service) {}

    /**
     * Submit a new listing application
     */
    public function store(StoreListingApplicationRequest $request): JsonResponse
    {
        try {
            $application = $this->service->submitApplication($request->validated());

            return response()->json([
                'message' => __('Your application has been received successfully. It will be reviewed by our team.'),
                'data' => $application->load('listing', 'user'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('An error occurred while submitting your application.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve application
     */
    public function approve(ListingApplication $application): JsonResponse
    {
        try {
            if ($application->status !== 'pending') {
                return response()->json([
                    'message' => __('Cannot approve an application that is not in pending status.'),
                ], 400);
            }

            $application = $this->service->approveApplication($application);

            return response()->json([
                'message' => __('Application approved and listing has been activated successfully.'),
                'data' => $application,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('An error occurred while approving the application.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject application
     */
    public function reject(ListingApplication $application): JsonResponse
    {
        try {
            if ($application->status !== 'pending') {
                return response()->json([
                    'message' => __('Cannot reject an application that is not in pending status.'),
                ], 400);
            }

            $reason = request()->input('rejection_reason');
            $application = $this->service->rejectApplication($application, $reason);

            return response()->json([
                'message' => __('Application has been rejected.'),
                'data' => $application,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('An error occurred while rejecting the application.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
