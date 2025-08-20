<?php

namespace Domain\Event\Repositories\Contracts;

use Application\Api\Business\Requests\SearchBusinessRequest;
use Core\Http\Requests\TableRequest;
use Domain\Event\Models\Event;
use Illuminate\Database\Eloquent\Collection;
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
     * Get the business.
     * @param Event $event
     * @return array
     */
    public function show(Event $event) :array;

    /**
     * Get featured businesses by type with configurable limits.
     */
    public function getFeaturedEvents();

    /**
     * Search businesses with filters and pagination.
     * @param SearchBusinessRequest $request
     * @return LengthAwarePaginator
     */
    public function search(SearchBusinessRequest $request): LengthAwarePaginator;
}
