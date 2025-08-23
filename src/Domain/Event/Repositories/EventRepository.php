<?php

namespace Domain\Event\Repositories;

use Application\Api\Business\Resources\BusinessResource;
use Application\Api\Event\Resources\EventResource;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Business\Models\Business;
use Domain\Event\Models\Event;
use Domain\Event\Repositories\Contracts\IEventRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Application\Api\Business\Requests\SearchBusinessRequest;
use Application\Api\Event\Resources\EventBoxResource;
use Domain\User\Services\TelegramNotificationService;

/**
 * Class EventRepository.
 */
class EventRepository implements IEventRepository
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
                'country:id,title',
                'city:id,title',
                'area:id,title',
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
     * Get the event.
     * @param Event $event
     * @return array
     */
    public function show(Event $event) :array
    {
        $event = Event::query()
                ->with([
                    'categories:id,title',
                    'services:id,title',
                    'tags:id,title',
                    'facilities:id,title',
                    'filters:id,title',
                    'files:id,path,type',
                    'user:id,nickname,profile_photo_path,rate',
                    'country:id,title',
                    'city:id,title',
                    'area:id,title',
                ])
                ->where('id', $event->id)
                ->first();

        $recommended = Event::query()
            ->with([
                'user:id,nickname,profile_photo_path,rate',
                'categories:id,title',
                'services:id,title',
                'tags:id,title',
                'facilities:id,title',
                'country:id,title',
                'city:id,title',
                'area:id,title',
            ])
            ->where('status', 1)
            ->inRandomOrder()
            ->limit(config('business.event_limit'))
            ->get()
            ->map(fn ($event) => new EventResource($event));

        return [
            'event' => new EventResource($event),
            'recommended'=> $recommended
        ];

    }

    /**
     * Get featured businesses by type with configurable limits.
     */
    public function getFeaturedEvents()
    {

        return Event::query()
            ->select('id', 'title', 'start_date', 'end_date', 'amount', 'summary', 'link', 'lat', 'long', 'image')
            ->where('status', 1)
            ->orderBy('priority', 'desc')
            ->limit(config('business.event_limit'))
            ->get()
            ->map(fn ($business) => new EventBoxResource($business));

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

}