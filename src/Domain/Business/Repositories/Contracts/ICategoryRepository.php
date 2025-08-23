<?php

namespace Domain\Business\Repositories\Contracts;

use Application\Api\Business\Requests\BusinessCategoryRequest;
use Core\Http\Requests\TableRequest;
use Domain\Business\Models\BusinessCategory;
use Domain\Business\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface ICategoryRepository.
 */
interface ICategoryRepository
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
     * Get the businessCategories.
     */
    public function allCategories();

    /**
     * Get the children of a specific category.
     * @param Category $category
     * @return Collection
     */
    public function getCategoryChildren(Category $category);

    /**
     * Get all parent categories.
     * @return Collection
     */
    public function getParentCategories();

    /**
     * Get the Category.
     * @param Category $category
     * @return Category
     */
    public function show(Category $category);

    /**
     * Get the Category with parent hierarchy.
     * @param Category $category
     * @return Category
     */
    public function showWithParents(Category $category);

    /**
     * Get the filters associated with a specific category.
     * @param TableRequest $request
     * @param Category $category
     * @return LengthAwarePaginator
     */
    public function getCategoryFilters(TableRequest $request, Category $category) :LengthAwarePaginator;

    /**
     * Store the businessCategory.
     * @param BusinessCategoryRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    // public function store(BusinessCategoryRequest $request) :JsonResponse;

    /**
     * Update the businessCategory.
     * @param BusinessCategoryRequest $request
     * @param BusinessCategory $businessCategory
     * @return JsonResponse
     * @throws \Exception
     */
    // public function update(BusinessCategoryRequest $request, BusinessCategory $businessCategory) :JsonResponse;

    /**
    * Delete the businessCategory.
    * @param UpdatePasswordRequest $request
    * @param BusinessCategory $businessCategory
    * @return JsonResponse
    */
//    public function destroy(BusinessCategory $businessCategory) :JsonResponse;
}