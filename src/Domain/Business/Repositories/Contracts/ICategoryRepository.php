<?php

namespace Domain\Business\Repositories\Contracts;

use Application\Api\Business\Requests\BusinessCategoryRequest;
use Core\Http\Requests\TableRequest;
use Domain\Business\Models\BusinessCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IBusinessCategoryRepository.
 */
interface IBusinessCategoryRepository
{
    /**
     * Get the businessCategories pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the businessCategories.
     * @return Collection
     */
    public function activeBusinessCategories() :Collection;

    /**
     * Get the businessCategory.
     * @param BusinessCategory $businessCategory
     * @return BusinessCategory
     */
    public function show(BusinessCategory $businessCategory) :BusinessCategory;

    /**
     * Store the businessCategory.
     * @param BusinessCategoryRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(BusinessCategoryRequest $request) :JsonResponse;

    /**
     * Update the businessCategory.
     * @param BusinessCategoryRequest $request
     * @param BusinessCategory $businessCategory
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(BusinessCategoryRequest $request, BusinessCategory $businessCategory) :JsonResponse;

    /**
    * Delete the businessCategory.
    * @param UpdatePasswordRequest $request
    * @param BusinessCategory $businessCategory
    * @return JsonResponse
    */
   public function destroy(BusinessCategory $businessCategory) :JsonResponse;
}