<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Visit;
use App\Models\VisitAttachment;
use App\Services\ReferralService;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function __construct(
        private readonly ReferralService $referralService,
        private readonly SystemNotificationService $notifications,
    ) {}

    /**
     * List the authenticated user's visits grouped by listing
     */
    public function index(Request $request)
    {
        $visits = Visit::query()
            ->where('user_id', $request->user()->id)
            ->with(['listing:id,name,address', 'attachments'])
            ->latest('visited_at')
            ->get();

        // Group by listing
        $grouped = $visits
            ->groupBy('listing_id')
            ->map(function ($listingVisits) {
                $listing  = $listingVisits->first()->listing;
                $approved = $listingVisits->where('status', Visit::STATUS_APPROVED)->count();
                $pending  = $listingVisits->where('status', Visit::STATUS_PENDING)->count();
                $rejected = $listingVisits->where('status', Visit::STATUS_REJECTED)->count();

                return [
                    'listing_id'      => $listing?->id,
                    'listing_name'    => $listing?->name,
                    'listing_address' => $listing?->address,
                    'total_visits'    => $listingVisits->count(),
                    'approved'        => $approved,
                    'pending'         => $pending,
                    'rejected'        => $rejected,
                    'last_visit_at'   => $listingVisits->first()->visited_at?->format('Y-m-d H:i:s'),
                ];
            })
            ->values();

        return response()->json(['data' => $grouped]);
    }

    /**
     * List visits for a specific listing
     */
    public function byListing(Request $request, int $listingId)
    {
        $visits = Visit::query()
            ->where('user_id', $request->user()->id)
            ->where('listing_id', $listingId)
            ->with(['listing:id,name,address', 'attachments'])
            ->latest('visited_at')
            ->get();

        if ($visits->isEmpty()) {
            abort(404);
        }

        return response()->json([
            'listing' => [
                'id'      => $visits->first()->listing?->id,
                'name'    => $visits->first()->listing?->name,
                'address' => $visits->first()->listing?->address,
            ],
            'visits' => $visits->map(fn ($v) => $this->formatVisit($v))->values(),
        ]);
    }

    /**
     * Submit a new visit
     */
    public function store(Request $request)
    {
        $request->validate([
            'listing_id'   => 'required|exists:listings,id',
            'visited_at'   => 'required|date|before_or_equal:now',
            'notes'        => 'nullable|string|max:500',
            'attachments'  => 'required|array|min:1|max:5',
            'attachments.*'=> 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $visit = DB::transaction(function () use ($request): Visit {
            $visit = Visit::create([
                'user_id'    => $request->user()->id,
                'listing_id' => $request->listing_id,
                'notes'      => $request->notes,
                'visited_at' => $request->visited_at,
                'status'     => Visit::STATUS_PENDING,
            ]);

            foreach ($request->file('attachments') as $file) {
                $path = $file->store("visits/{$visit->id}", 'public');
                VisitAttachment::create([
                    'visit_id'  => $visit->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }

            return $visit->load(['listing:id,name,address', 'attachments']);
        });

        // Notify the user
        $this->notifications->notifyUser(
            $visit->user,
            __('Visit submitted successfully'),
            __('Your visit to :listing has been submitted and is pending review. You will earn :points points upon approval.', [
                'listing' => $visit->listing?->name,
                'points'  => Visit::getVisitPoints(),
            ]),
            'info',
        );

        // Notify admins
        $this->notifications->notifyAdmins(
            __('New visit submitted'),
            __(':user submitted a visit to :listing and is awaiting approval.', [
                'user'    => $visit->user?->name,
                'listing' => $visit->listing?->name,
            ]),
            'warning',
        );

        return response()->json([
            'message' => __('Visit submitted successfully. Points will be added after admin approval.'),
            'visit'   => $this->formatVisit($visit),
        ], 201);
    }
    public function show(Request $request, int $id)
    {
        $visit = Visit::query()
            ->where('user_id', $request->user()->id)
            ->with(['listing:id,name,address', 'attachments'])
            ->findOrFail($id);

        return response()->json(['visit' => $this->formatVisit($visit)]);
    }

    private function formatVisit(Visit $visit): array
    {
        return [
            'id'               => $visit->id,
            'listing'          => [
                'id'      => $visit->listing?->id,
                'name'    => $visit->listing?->name,
                'address' => $visit->listing?->address,
            ],
            'notes'            => $visit->notes,
            'status'           => $visit->status,
            'rejection_reason' => $visit->rejection_reason,
            'points_reward'    => Visit::getVisitPoints(),
            'visited_at'       => $visit->visited_at?->format('Y-m-d H:i:s'),
            'approved_at'      => $visit->approved_at?->format('Y-m-d H:i:s'),
            'created_at'       => $visit->created_at->format('Y-m-d H:i:s'),
            'attachments'      => $visit->attachments->map(fn ($a) => [
                'id'        => $a->id,
                'file_name' => $a->file_name,
                'url'       => asset('storage/' . $a->file_path),
                'mime_type' => $a->mime_type,
            ]),
        ];
    }
}
