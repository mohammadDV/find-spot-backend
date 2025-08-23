<?php

namespace Application\Api\Business\Controllers;

use Application\Api\Business\Requests\CategoryRequest;
use Application\Api\Business\Resources\CategoryWithParentsResource;
use Application\Api\Business\Resources\FilterResource;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Business\Models\Category;
use Domain\Business\Repositories\Contracts\ICategoryRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;


class CategoryController extends Controller
{

    /**
     * @param ICategoryRepository $repository
     */
    public function __construct(protected ICategoryRepository $repository)
    {

    }

    /**
     * Get all of BusinessCategories with pagination
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function index(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->index($request), Response::HTTP_OK);
    }

    /**
     * Get all of BusinessCategories
     * @return JsonResponse
     */
    public function activeBusinessCategories(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->activeBusinessCategories($request), Response::HTTP_OK);
    }

    /**
     * Get all of BusinessCategories
     * @return JsonResponse
     */
    public function allCategories(): JsonResponse
    {
        return response()->json($this->repository->allCategories(), Response::HTTP_OK);
    }

    /**
     * Get the children of a specific category.
     * @param Category $category
     * @return JsonResponse
     */
    public function getCategoryChildren(Category $category) :JsonResponse
    {
        return response()->json($this->repository->getCategoryChildren($category), Response::HTTP_OK);
    }

    /**
     * Get all parent categories.
     * @return JsonResponse
     */
    public function getParentCategories() :JsonResponse
    {
        return response()->json($this->repository->getParentCategories(), Response::HTTP_OK);
    }

    /**
     * Get the Category.
     * @param Category $category
     * @return JsonResponse
     */
    public function show(Category $category) :JsonResponse
    {
        return response()->json(new CategoryWithParentsResource($this->repository->show($category)), Response::HTTP_OK);
    }

    /**
     * Get the filters associated with a specific category.
     * @param TableRequest $request
     * @param Category $category
     * @return JsonResponse
     */
    public function getCategoryFilters(TableRequest $request, Category $category): JsonResponse
    {
        return response()->json($this->repository->getCategoryFilters($request, $category), Response::HTTP_OK);
    }

    /**
     * Store the category.
     * @param CategoryRequest $request
     * @return JsonResponse
     */
    // public function store(CategoryRequest $request) :JsonResponse
    // {
    //     return $this->repository->store($request);
    // }

    /**
     * Update the category.
     * @param categoryRequest $request
     * @param Category $category
     * @return JsonResponse
     */
    // public function update(CategoryRequest $request, Category $category) :JsonResponse
    // {
    //     return $this->repository->update($request, $category);
    // }

    /**
     * Delete the category.
     * @param Category $category
     * @return JsonResponse
     */
    // public function destroy(Category $Category) :JsonResponse
    // {
    //     return $this->repository->destroy($Category);
    // }
}
