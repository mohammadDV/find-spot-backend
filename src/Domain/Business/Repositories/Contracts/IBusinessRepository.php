<?php

namespace Domain\Business\Repositories\Contracts;

use Application\Api\Business\Requests\BusinessRequest;
use Application\Api\Business\Requests\SearchBusinessRequest;
use Core\Http\Requests\TableRequest;
use Domain\Business\Models\Business;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IBusinessRepository.
 */
interface IBusinessRepository
{
    /**
     * Get the businesses pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Edit the business.
     * @param Business $business
     * @return Business
     */
    public function edit(Business $business) :Business;

    /**
     * Get the business.
     * @param Business $business
     * @return array
     */
    public function show(Business $business) :array;

    /**
     * Store the business.
     * @param BusinessRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(BusinessRequest $request) :JsonResponse;

    /**
     * Update the business.
     * @param BusinessRequest $request
     * @param Business $business
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(BusinessRequest $request, Business $business) :JsonResponse;

    /**
     * Get featured businesses by type with configurable limits.
     * @return array{sender: Collection, passenger: Collection}
     */
    public function getFeaturedBusinesses(): array;

    /**
     * Search businesses with filters and pagination.
     * @param SearchBusinessRequest $request
     * @return LengthAwarePaginator
     */
    public function search(SearchBusinessRequest $request): LengthAwarePaginator;

    /**
     * Search suggestions with filters and pagination.
     * @param TableRequest $request
     */
    public function searchSuggestions(TableRequest $request);
}
