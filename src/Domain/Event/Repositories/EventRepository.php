<?php

namespace Domain\Event\Repositories;

use Application\Api\Business\Resources\BusinessResource;
use Application\Api\Event\Resources\EventResource;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Business\Models\Business;
use Domain\Business\Models\Favorite;
use Domain\Event\Models\Event;
use Domain\Event\Repositories\Contracts\IEventRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Application\Api\Business\Requests\SearchBusinessRequest;
use Application\Api\Event\Resources\EventBoxResource;
use Domain\User\Services\TelegramNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
        $events = Event::query()
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->where('status', 1)
            ->where(function($query) {
                $query->where('end_date', '>=', now()->startOfDay())
                    ->orWhereNull('end_date');
            })
            ->orderBy($request->get('column', 'priority'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 24));

        return $events->through(fn ($event) => new EventBoxResource($event));
    }

    /**
     * Get the businesses pagination.
     * @return array
     */
    public function sliders() :array
    {
        $sliders = Event::query()
            ->where('status', 1)
            ->where('vip', 1)
            ->orderBy('priority', 'desc')
            ->where(function($query) {
                $query->where('end_date', '>=', now()->startOfDay())
                    ->orWhereNull('end_date');
            })
            ->limit(10)
            ->get()
            ->map(fn ($event) => new EventBoxResource($event));

        $recommended = Event::query()
            ->where('status', 1)
            ->where('start_date', '<=', now()->addDays(2)->startOfDay())
            ->where('start_date', '>=', now()->yesterday()->startOfDay())
            ->where(function($query) {
                $query->where('end_date', '>=', now()->startOfDay())
                    ->orWhereNull('end_date');
            })
            ->inRandomOrder()
            ->limit(20)
            ->get()
            ->map(fn ($event) => new EventBoxResource($event));

        return [
            'sliders' => $sliders,
            'recommended'=> $recommended
        ];
    }

    /**
     * Get the event.
     * @param Event $event
     * @return EventResource
     */
    public function show(Event $event) :EventResource
    {
        $event = Event::query()
                ->where('status', 1)
                ->where('id', $event->id)
                ->first();

        return new EventResource($event);

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
     * Favorite the event.
     * @param Event $event
     * @return JsonResponse
     */
    public function favorite(Event $event) :JsonResponse
    {
        $favorite = Favorite::where('favoritable_id', $event->id)
            ->where('favoritable_type', Event::class)
            ->where('user_id', Auth::user()->id)
            ->first();

        $active = 0;

        if ($favorite) {
            $favorite->delete();
        } else {
            $favorite = Favorite::create([
                'favoritable_id' => $event->id,
                'favoritable_type' => Event::class,
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
     * Get favorite events.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getFavoriteEvents(TableRequest $request): LengthAwarePaginator
    {
        $search = $request->get('query');
        $events = Event::query()
            ->whereHas('favorites', function ($query) {
                $query->where('favorites.user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));

        return $events->through(fn ($event) => new EventBoxResource($event));
    }

    /**
     * Get similar events.
     * @param Event $event
     */
    public function similarEvents(Event $event)
    {
        return Event::query()
            ->where('status', 1)
            ->where('id', '!=', $event->id)
            ->where(function($query) {
                $query->where('end_date', '>=', now()->startOfDay())
                    ->orWhereNull('end_date');
            })
            ->inRandomOrder()
            ->limit(4)
            ->get()
            ->map(fn ($event) => new EventBoxResource($event));
    }
}
