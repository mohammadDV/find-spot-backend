<?php

namespace Domain\Business\Repositories;

use Application\Api\Business\Requests\BusinessRequest;
use Application\Api\Business\Resources\BusinessResource;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Business\Models\Business;
use Domain\Business\Repositories\Contracts\IBusinessRepository;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Application\Api\Business\Requests\SearchBusinessRequest;
use Application\Api\Business\Resources\BusinessBoxResource;
use DateTimeZone;
use Domain\Business\Models\Category;
use Domain\Business\Models\Favorite;
use Domain\Business\Models\ServiceVote;
use Domain\Business\Models\Weekend;
use Domain\Notification\Services\NotificationService;
use Domain\Review\Models\Review;
use Domain\User\Services\TelegramNotificationService;
use Illuminate\Support\Facades\DB;

/**
 * Class BusinessRepository.
 */
class BusinessRepository implements IBusinessRepository
{
    use GlobalFunc;

    public function __construct(protected TelegramNotificationService $service)
    {
        //
    }

    /**
     * Get the businesses pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        $status = $request->get('status');
        $businesses = Business::query()
            ->with(['area', 'tags'])
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->when(!empty($status), function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));

        return $businesses->through(fn ($business) => new BusinessBoxResource($business));
    }

    /**
     * Get the business.
     * @param Business $business
     * @return array
     */
    public function show(Business $business) :array
    {
        $business = Business::query()
                ->with([
                    'categories:id,title',
                    'country:id,title',
                    'city:id,title',
                    'area.city.country',
                    'tags',
                    'facilities:id,title',
                    'filters:id,title',
                    'files',
                ])
                ->where('id', $business->id)
                ->first();


        // votes and quantity services
        $services = $this->getServiceVotes($business?->id);

        $reviews = $this->getReviewsByRate($business->id);

        $user = $this->getAuthenticatedUser();

        // is favorite or not
        $isFavorite = Favorite::query()
                ->where('favoritable_type', Business::class)
                ->where('favoritable_id', $business->id)
                ->where('user_id', $user?->id)
                ->exists();

        return [
            'business' => new BusinessResource($business),
            'quality_services' => $services,
            'reviews' => $reviews,
            'is_favorite' => $isFavorite,
        ];

    }

    /**
     * Edit the business.
     * @param Business $business
     * @return Business
     */
    public function edit(Business $business) :Business
    {
        $this->checkLevelAccess(Auth::user()->id == $business->user_id);

        return Business::query()
                ->with([
                    'categories:id,title',
                    'area.city.country',
                    'tags',
                    'facilities:id,title',
                    'filters:id,title',
                    'files',
                ])
                ->where('id', $business->id)
                ->first();

    }

    /**
     * Store the business.
     * @param BusinessRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(BusinessRequest $request) :JsonResponse
    {
        // if (empty(Auth::user()->status)) {
        //     return response()->json([
        //         'status' => 0,
        //         'message' => __('site.Your account is not active yet. Please send a message to the admin from ticket section.'),
        //     ], Response::HTTP_BAD_REQUEST);
        // }

        // if (empty(Auth::user()->verified_at)) {
        //     return response()->json([
        //         'status' => 0,
        //         'message' => __('site.You must verify your account to create a business'),
        //     ], Response::HTTP_BAD_REQUEST);
        // }

        try {
            DB::beginTransaction();

            $business = Business::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'lat' => $request->input('lat'),
                'long' => $request->input('long'),
                'website' => $request->input('website'),
                'facebook' => $request->input('facebook'),
                'instagram' => $request->input('instagram'),
                'youtube' => $request->input('youtube'),
                'tiktok' => $request->input('tiktok'),
                'whatsapp' => $request->input('whatsapp'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'address' => $request->input('address'),
                'start_amount' => $request->input('start_amount'),
                'amount_type' => $request->input('amount_type', 0),
                'image' => $request->input('image'),
                'slider_image' => $request->input('slider_image'),
                'menu_image' => $request->input('menu_image'),
                'video' => $request->input('video'),
                'from_monday' => $request->input('from_monday', 0),
                'from_tuesday' => $request->input('from_tuesday', 0),
                'from_wednesday' => $request->input('from_wednesday', 0),
                'from_thursday' => $request->input('from_thursday', 0),
                'from_friday' => $request->input('from_friday', 0),
                'from_saturday' => $request->input('from_saturday', 0),
                'from_sunday' => $request->input('from_sunday', 0),
                'to_monday' => $request->input('to_monday', 0),
                'to_tuesday' => $request->input('to_tuesday', 0),
                'to_wednesday' => $request->input('to_wednesday', 0),
                'to_thursday' => $request->input('to_thursday', 1),
                'to_friday' => $request->input('to_friday', 0),
                'to_saturday' => $request->input('to_saturday', 0),
                'to_sunday' => $request->input('to_sunday', 0),
                'active' => 1,
                'status' => Business::PENDING,
                'country_id' => $request->input('country_id'),
                'city_id' => $request->input('city_id'),
                'area_id' => $request->input('area_id'),
                'user_id' => Auth::user()->id,
            ]);

            if ($business) {
                // Attach categories if provided
                if ($request->has('categories')) {
                    $business->categories()->attach($request->input('categories'));
                }

                // Create tags if provided
                if ($request->has('tags')) {
                    foreach ($request->input('tags') as $tagData) {
                        $business->tags()->create(['title' => $tagData, 'status' => 1]);
                    }
                }

                // Attach facilities if provided
                if ($request->has('facilities')) {
                    $business->facilities()->attach($request->input('facilities'));
                }

                // Attach filters if provided
                if ($request->has('filters')) {
                    $business->filters()->attach($request->input('filters'));
                }

                // Create files if provided
                if ($request->has('files')) {
                    foreach ($request->input('files') as $fileData) {
                        $business->files()->create($fileData);
                    }
                }

                NotificationService::create([
                    'title' => __('site.business_created_title'),
                    'content' => __('site.business_created_content', ['business_title' => $business->title]),
                    'id' => $business->id,
                    'type' => NotificationService::BUSINESS,
                ], $business->user);

                // $this->service->sendNotification(
                //     config('telegram.chat_id'),
                //     'ساخت آگهی جدید' . PHP_EOL .
                //     'id ' . Auth::user()->id . PHP_EOL .
                //     'nickname ' . Auth::user()->nickname . PHP_EOL .
                //     'title ' . $business->title . PHP_EOL .
                //     'time ' . now()
                // );


                DB::commit();

                return response()->json([
                    'status' => 1,
                    'message' => __('site.The operation has been successfully'),
                    'data' => new BusinessResource($business)
                ], Response::HTTP_CREATED);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        throw new \Exception();
    }

    /**
     * Update the business.
     * @param BusinessRequest $request
     * @param Business $business
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(BusinessRequest $request, Business $business) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->id == $business->user_id);

        if (Auth::user()->level != 3 && $business->status != Business::PENDING) {
            throw New \Exception('Unauthorized', 403);
        }

        $updated = $business->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'lat' => $request->input('lat'),
            'long' => $request->input('long'),
            'website' => $request->input('website'),
            'facebook' => $request->input('facebook'),
            'instagram' => $request->input('instagram'),
            'youtube' => $request->input('youtube'),
            'tiktok' => $request->input('tiktok'),
            'whatsapp' => $request->input('whatsapp'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'start_amount' => $request->input('start_amount'),
            'amount_type' => $request->input('amount_type'),
            'image' => $request->input('image'),
            'slider_image' => $request->input('slider_image'),
            'menu_image' => $request->input('menu_image'),
            'video' => $request->input('video'),
            'from_monday' => $request->input('from_monday'),
            'from_tuesday' => $request->input('from_tuesday'),
            'from_wednesday' => $request->input('from_wednesday'),
            'from_thursday' => $request->input('from_thursday'),
            'from_friday' => $request->input('from_friday'),
            'from_saturday' => $request->input('from_saturday'),
            'from_sunday' => $request->input('from_sunday'),
            'to_monday' => $request->input('to_monday'),
            'to_tuesday' => $request->input('to_tuesday'),
            'to_wednesday' => $request->input('to_wednesday'),
            'to_thursday' => $request->input('to_thursday'),
            'to_friday' => $request->input('to_friday'),
            'to_saturday' => $request->input('to_saturday'),
            'to_sunday' => $request->input('to_sunday'),
            'country_id' => $request->input('country_id'),
            'city_id' => $request->input('city_id'),
            'area_id' => $request->input('area_id'),
        ]);

        if ($updated) {
            // Sync categories if provided
            if ($request->has('categories')) {
                $business->categories()->sync($request->input('categories'));
            }

            // Handle tags if provided (one-to-many relationship)
            if ($request->has('tags')) {
                $this->updateBusinessTags($business, $request->input('tags'));
            } else {
                // If tags not provided, keep existing tags (don't delete them)
            }

            // Handle files if provided (one-to-many relationship)
            if ($request->has('files')) {
                $this->updateBusinessFiles($business, $request->input('files'));
            } else {
                // If files not provided, keep existing files (don't delete them)
            }

            // Sync facilities if provided
            if ($request->has('facilities')) {
                $business->facilities()->sync($request->input('facilities'));
            }

            // Sync filters if provided
            if ($request->has('filters')) {
                $business->filters()->sync($request->input('filters'));
            }

            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully'),
                'data' => new BusinessResource($business)
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

    /**
     * Favorite the business.
     * @param Business $business
     * @return JsonResponse
     */
    public function favorite(Business $business) :JsonResponse
    {
        $favorite = Favorite::query()
            ->where('favoritable_id', $business->id)
            ->where('favoritable_type', Business::class)
            ->where('user_id', Auth::user()->id)
            ->first();

        $active = 0;

        if ($favorite) {
            $favorite->delete();
        } else {
            $favorite = Favorite::create([
                'favoritable_id' => $business->id,
                'favoritable_type' => Business::class,
                'user_id' => Auth::user()->id,
            ]);
            $active = 1;
        }

        return response()->json([
            'status' => 1,
            'message' => __('site.The operation has been successfully'),
            'favorite' => $active,
        ], Response::HTTP_OK);
    }

    /**
     * Get favorite businesses.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getFavoriteBusinesses(TableRequest $request): LengthAwarePaginator
    {
        $search = $request->get('query');
        $businesses = Business::query()
            ->with(['area', 'tags'])
            ->whereHas('favorites', function ($query) {
                $query->where('favorites.user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));

        return $businesses->through(fn ($business) => new BusinessBoxResource($business));
    }

    /**
     * Get similar businesses.
     * @param Business $business
     */
    public function similarBusinesses(Business $business)
    {
        $similarBusinesses = Business::query()
            ->with(['area', 'tags'])
            ->where('active', 1)
            ->where('status', Business::APPROVED)
            ->whereHas('categories', function ($query) use ($business) {
                $query->whereIn('categories.id', $business->categories->pluck('id'));
            })
            ->where('id', '!=', $business->id)
            ->limit(4)
            ->get();

        return $similarBusinesses->map(fn ($business) => new BusinessBoxResource($business));
    }

    /**
     * Get featured businesses by type with configurable limits.
     * @return array{sender: Collection, passenger: Collection}
     */
    public function getFeaturedBusinesses(): array
    {

        $businesses = Business::query()
            ->select('id', 'title', 'amount_type', 'start_amount', 'rate', 'lat', 'long', 'image', 'area_id')
            ->with(['area', 'tags'])
            ->where('active', 1)
            ->where('status', Business::APPROVED)
            ->orderBy('priority', 'desc')
            ->limit(config('business.limit'))
            ->get()
            ->map(fn ($business) => new BusinessBoxResource($business));

        $weekends = Business::query()
            ->select('id', 'title', 'amount_type', 'start_amount', 'rate', 'lat', 'long', 'image', 'area_id')
            ->with(['area', 'tags'])
            ->whereHas('weekends', function ($query) {
                $query->where('weekends.status', 1);
            })
            ->where('active', 1)
            ->where('status', Business::APPROVED)
            ->inRandomOrder() // Replace orderBy with this
            ->limit(config('business.weekend_limit'))
            ->get()
            ->map(fn ($business) => new BusinessBoxResource($business));

        return [
                'offers' => $businesses,
                'weekends' => $weekends
        ];
    }

    /**
     * Get weekends.
     * @return array
     */
    public function getWeekends(): array
    {

        $weekends = Weekend::query()->where('status', 1)->get();

        $result = [];

        foreach ($weekends as $weekend) {
            $result[$weekend->id]['title'] = $weekend->title;
            $result[$weekend->id]['businesses'] = $weekend->businesses()->with(['area', 'tags'])->where('status', Business::APPROVED)->get()->map(fn ($business) => new BusinessBoxResource($business));
        }

        return $result;
    }

    /**
     * Search suggestions with filters and pagination.
     * @param TableRequest $request
     */
    public function searchSuggestions(TableRequest $request)
    {

        $search = $request->get('query');

        $categories = Category::query()
            ->with(['filters' => function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            }])
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('filters', function ($q) use ($search) {
                        $q->where('title', 'like', '%' . $search . '%');
                    });
            })
            ->where('status', 1)
            ->limit(10)
            ->get();

        $queryBusiness = Business::query()
            ->with(['area', 'tags'])
            ->where('active', 1)
            ->where('status', Business::APPROVED)
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('filters', function ($q) use ($search) {
                        $q->where('title', 'like', '%' . $search . '%');
                    });
            });

        $businesses = $queryBusiness->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->limit(5)
            ->get();


        return [
            'businesses' => $businesses->map(fn ($business) => new BusinessBoxResource($business)),
            'categories' => $categories,
        ];

    }

    /**
     * Search businesses with filters and pagination.
     * @param SearchBusinessRequest $request
     * @return LengthAwarePaginator
     */
    public function search(SearchBusinessRequest $request): LengthAwarePaginator
    {

        $search = $request->get('query');
        $catId = $request->get('category');
        $filters = $request->get('filters');
        $amountType = $request->get('amount_type');
        $now = $request->get('now');
        $lat = $request->get('lat');
        $long = $request->get('long');
        $areaId = $request->get('area_id');

        // Get current hour and day of week for business open filtering
        $currentDateTime = now();
        $currentHour = intval($currentDateTime->setTimezone(new DateTimeZone('Asia/Istanbul'))->format('H'));
        $currentDayOfWeek = $currentDateTime->format('l'); // Returns English day name (Monday, Tuesday, etc.)

        // Generate a unique cache key based on all search parameters
        $cacheKey = 'business_search_' . md5(json_encode([
            'query' => $search,
            'category' => $catId,
            'filters' => $filters,
            'amount_type' => $amountType,
            'now' => $now,
            'lat' => $lat,
            'long' => $long,
            'area_id' => $areaId,
            'page' => $request->input('page', 1),
        ]));

        // Try to get results from cache first
        // return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($request, $today) {
            $query = Business::query()
                ->with(['area', 'tags'])
                ->where('active', 1)
                ->where('status', Business::APPROVED);

            // Apply title
            if (!empty($search)) {
                $query->where('title','like', '%' . $search . '%');
            }

            // amount type
            if (!empty($amountType)) {
                $query->where('amount_type', $amountType);
            }

            // category
            if (!empty($catId)) {
                $query->whereHas('categories', function ($q) use ($catId) {
                    $q->where('categories.id', $catId)
                      ->orWhere('categories.parent_id', $catId);
                });
            }

            // filters
            if (!empty($filters)) {
                $query->whereHas('filters', function ($q) use ($filters) {
                    $q->whereIn('filter_id', $filters);
                }, '=', count($filters));
            }

            // now
            if (!empty($now)) {
                $query->where('from_' . strtolower($currentDayOfWeek), '<=', $currentHour)
                    ->where('to_' . strtolower($currentDayOfWeek), '>=', $currentHour);
            }

            // near me
            if (!empty($lat) && !empty($long)) {
                // Filter businesses within 5 kilometers radius using a simpler approach
                $radius = 2; // kilometers

                // Use a bounding box approach for better performance and compatibility
                $latMin = $lat - ($radius / 111.32); // 1 degree = ~111.32 km
                $latMax = $lat + ($radius / 111.32);
                $longMin = $long - ($radius / (111.32 * cos(deg2rad($lat))));
                $longMax = $long + ($radius / (111.32 * cos(deg2rad($lat))));

                $query->whereBetween('lat', [$latMin, $latMax])
                      ->whereBetween('long', [$longMin, $longMax]);
            }

            // area
            if (!empty($areaId)) {
                $query->where('area_id', $areaId);
            }


            $businesses = $query->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
                ->paginate($request->get('count', 25));

            return $businesses->through(fn ($business) => new BusinessBoxResource($business));
        // });
    }

    /**
     * Update business tags intelligently
     * @param Business $business
     * @param array $tagsData
     */
    private function updateBusinessTags(Business $business, array $tagsData): void
    {
        // Get existing tags
        $existingTags = $business->tags()->pluck('id', 'title')->toArray();

        // Process new tags
        foreach ($tagsData as $tagData) {
            $title = $tagData;

            if (isset($existingTags[$title])) {
                // Tag exists, update it if needed
                $tagId = $existingTags[$title];
                $business->tags()->where('id', $tagId)->update([
                    'status' => $tagData['status'] ?? 1
                ]);
                // Remove from existing tags so we don't delete it
                unset($existingTags[$title]);
            } else {
                // Create new tag
                $business->tags()->create(['title' => $title, 'status' => 1]);
            }
        }

        // Delete tags that are no longer in the request
        if (!empty($existingTags)) {
            $business->tags()->whereIn('id', array_values($existingTags))->delete();
        }
    }

    /**
     * Update business files intelligently
     * @param Business $business
     * @param array $filesData
     */
    private function updateBusinessFiles(Business $business, array $filesData): void
    {
        // Get existing files
        $existingFiles = $business->files()->pluck('id', 'path')->toArray();

        // Process new files
        foreach ($filesData as $fileData) {
            $path = $fileData['path'];

            if (isset($existingFiles[$path])) {
                // File exists, update it if needed
                $fileId = $existingFiles[$path];
                $business->files()->where('id', $fileId)->update([
                    'type' => $fileData['type'] ?? 'image',
                    'status' => $fileData['status'] ?? 1
                ]);
                // Remove from existing files so we don't delete it
                unset($existingFiles[$path]);
            } else {
                // Create new file
                $business->files()->create($fileData);
            }
        }

        // Delete files that are no longer in the request
        if (!empty($existingFiles)) {
            $business->files()->whereIn('id', array_values($existingFiles))->delete();
        }
    }

    /**
     * Get reviews grouped by rate with counts for businesses
     * @param int|null $businessId Optional business ID to filter by specific business
     * @return Collection
     */
    public function getReviewsByRate(?int $businessId = null): Collection
    {
        $query = Review::query()
            ->select('rate', DB::raw('COUNT(*) as count'))
            ->where('active', 1) // Only approved reviews
            ->where('status', Review::APPROVED); // Only approved reviews

        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        $titles = [
            1 => __('site.very_bad'),
            2 => __('site.bad'),
            3 => __('site.average'),
            4 => __('site.good'),
            5 => __('site.excellent')
        ];

        return $query->groupBy('rate')
            ->orderBy('rate', 'desc')
            ->get()
            ->map(function ($item) use ($titles) {
                return [
                    'title' => $titles[$item->rate],
                    'rate' => $item->rate,
                    'count' => $item->count,
                    'percentage' => 0 // Will be calculated below
                ];
            })
            ->map(function ($item, $index) use ($businessId) {
                // Calculate percentage based on total reviews
                $totalReviews = Review::query()
                    ->where('status', 1)
                    ->when($businessId ?? false, function ($query) use ($businessId) {
                        return $query->where('business_id', $businessId);
                    })
                    ->count();

                $item['percentage'] = $totalReviews > 0 ? round(($item['count'] / $totalReviews) * 100, 2) : 0;
                return $item;
            });
    }

    public function getServiceVotes(?int $businessId = null): Collection
    {
        $query = ServiceVote::query()
            ->where('business_id', $businessId)
            ->select('service_id', DB::raw('COUNT(*) as count'));

        return $query->groupBy('service_id')
            ->orderBy('service_id', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'title' => $item->service->title,
                    'count' => $item->count,
                ];
            });
    }
}