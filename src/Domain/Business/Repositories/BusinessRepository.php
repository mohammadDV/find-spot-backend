<?php

namespace Domain\Business\Repositories;

use Application\Api\Business\Requests\BusinessRequest;
use Application\Api\Business\Resources\BusinessResource;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Business\Models\Business;
use Domain\Business\Repositories\Contracts\IBusinessRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Application\Api\Business\Requests\SearchBusinessRequest;
use Application\Api\Business\Resources\BusinessBoxResource;
use Domain\Notification\Services\NotificationService;
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
            ->with([
                'user:id,nickname,profile_photo_path,rate',
                'country:id,name',
                'city:id,name',
                'area:id,name',
                'categories:id,title',
                'services:id,title',
                'tags:id,title',
                'facilities:id,title',
                'filters:id,title',
                'files:id,path,type'
            ])
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

        return $businesses->through(fn ($business) => new BusinessResource($business));
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
                    'services:id,title',
                    'tags:id,title',
                    'facilities:id,title',
                    'filters:id,title',
                    'files:id,path,type',
                    'user:id,nickname,profile_photo_path,rate',
                    'country:id,name',
                    'city:id,name',
                    'area:id,name',
                ])
                ->where('id', $business->id)
                ->first();

        $recommended = Business::query()
            ->with([
                'user:id,nickname,profile_photo_path,rate',
                'categories:id,title',
                'services:id,title',
                'tags:id,title',
                'facilities:id,title',
                'country:id,name',
                'city:id,name',
                'area:id,name',
            ])
            ->where('active', 1)
            ->where('status', Business::APPROVED)
            ->inRandomOrder()
            ->limit(config('business.limit'))
            ->get()
            ->map(fn ($business) => new BusinessResource($business));

        return [
            'business' => new BusinessResource($business),
            'recommended'=> $recommended
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
                    'services:id,title',
                    'tags:id,title',
                    'facilities:id,title',
                    'filters:id,title',
                    'files:id,path,type',
                    'user:id,nickname,profile_photo_path,rate',
                    'country:id,name',
                    'city:id,name',
                    'area:id,name',
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
                        $business->tags()->create($tagData);
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
     * Delete the business.
     * @param Business $business
     * @return JsonResponse
     */
    public function destroy(Business $business) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->id == $business->user_id);

        $deleted = $business->delete();

        if ($deleted) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

    /**
     * Get featured businesses by type with configurable limits.
     * @return array{sender: Collection, passenger: Collection}
     */
    public function getFeaturedBusinesses(): array
    {

        $businesses = Business::query()
            ->select('id', 'title', 'amount_type', 'start_amount', 'rate', 'lat', 'long', 'image', 'area_id')
            ->with([
                'area:id,title',
            ])
            ->where('active', 1)
            ->where('status', Business::APPROVED)
            ->orderBy('priority', 'desc')
            ->limit(config('business.limit'))
            ->get()
            ->map(fn ($business) => new BusinessBoxResource($business));

        $weekends = Business::query()
            ->select('id', 'title', 'amount_type', 'start_amount', 'rate', 'lat', 'long', 'image', 'area_id')
            ->with([
                'area:id,title',
            ])
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
     * Search businesses with filters and pagination.
     * @param SearchBusinessRequest $request
     * @return LengthAwarePaginator
     */
    public function search(SearchBusinessRequest $request): LengthAwarePaginator
    {

        // Generate a unique cache key based on all search parameters
        $cacheKey = 'business_search_' . md5(json_encode([
            'type' => $request->input('type'),
            'o_city_id' => $request->input('o_city_id'),
            'd_city_id' => $request->input('d_city_id'),
            'o_province_id' => $request->input('o_province_id'),
            'd_province_id' => $request->input('d_province_id'),
            'o_country_id' => $request->input('o_country_id'),
            'd_country_id' => $request->input('d_country_id'),
            'send_date' => $request->input('send_date'),
            'receive_date' => $request->input('receive_date'),
            'path_type' => $request->input('path_type'),
            'categories' => $request->input('categories'),
            'min_weight' => $request->input('min_weight'),
            'max_weight' => $request->input('max_weight'),
            'page' => $request->input('page', 1),
        ]));

        // Try to get results from cache first
        // return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($request, $today) {
            $query = Business::query()
                ->with([
                    'categories:id,title',
                    'user:id,nickname,profile_photo_path,rate',
                    'oCountry',
                    'oProvince',
                    'oCity',
                    'dCountry',
                    'dProvince',
                    'dCity',
                ])
                ->where('active', 1)
                ->where('status', Business::APPROVED)
                ->where('send_date', '>=', now()->startOfDay())
                ->where('type', $request->input('type'));

            // Apply filters
            if ($request->has('o_city_id')) {
                $query->where('o_city_id', $request->input('o_city_id'));
            }

            if ($request->has('d_city_id')) {
                $query->where('d_city_id', $request->input('d_city_id'));
            }

            if ($request->has('o_province_id')) {
                $query->where('o_province_id', $request->input('o_province_id'));
            }

            if ($request->has('d_province_id')) {
                $query->where('d_province_id', $request->input('d_province_id'));
            }

            if ($request->has('o_country_id')) {
                $query->where('o_country_id', $request->input('o_country_id'));
            }

            if ($request->has('d_country_id')) {
                $query->where('d_country_id', $request->input('d_country_id'));
            }

            if ($request->has('send_date')) {
                $query->where('send_date', '=', $request->input('send_date'));
            }

            if ($request->has('receive_date')) {
                $query->where('receive_date', '>=', $request->input('receive_date'));
            }

            if ($request->has('path_type')) {
                $query->where('path_type', $request->input('path_type'));
            }

            // Apply weight range filter
            if ($request->has('min_weight')) {
                $query->where('weight', '>=', $request->input('min_weight'));
            }

            if ($request->has('max_weight')) {
                $query->where('weight', '<=', $request->input('max_weight'));
            }

            if ($request->has('categories')) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->whereIn('categories.id', $request->input('categories'));
                });
            }

            $businesses = $query->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
                ->paginate($request->get('count', 25));

            return $businesses->through(fn ($business) => new BusinessResource($business));
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
            $title = $tagData['title'];

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
                $business->tags()->create($tagData);
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
}
