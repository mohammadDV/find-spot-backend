<?php

namespace Application\Api\Business\Controllers;

use Application\Api\Business\Requests\CategoryRequest;
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
     * Get the Category.
     * @param
     * @return JsonResponse
     */
    public function show(Category $category) :JsonResponse
    {
        return response()->json($this->repository->show($category), Response::HTTP_OK);
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
