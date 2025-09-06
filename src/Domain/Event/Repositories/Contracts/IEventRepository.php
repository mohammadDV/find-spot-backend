<?php

namespace Domain\Event\Repositories\Contracts;

use Application\Api\Business\Requests\SearchBusinessRequest;
use Application\Api\Event\Resources\EventResource;
use Core\Http\Requests\TableRequest;
use Domain\Event\Models\Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IEventRepository.
 */
interface IEventRepository
{
    /**
     * Get the businesses pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the businesses pagination.
     * @return array
     */
    public function sliders() :array;

    /**
     * Get the business.
     * @param Event $event
     * @return arrayEventResource
     */
    public function show(Event $event) :EventResource;

    /**
     * Get featured businesses by type with configurable limits.
     */
    public function getFeaturedEvents();

    /**
     * Favorite the event.
     * @param Event $event
     * @return JsonResponse
     */
    public function favorite(Event $event) :JsonResponse;

    /**
     * Get favorite events.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getFavoriteEvents(TableRequest $request): LengthAwarePaginator;

}
