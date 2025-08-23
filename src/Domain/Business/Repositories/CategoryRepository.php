<?php

namespace Domain\Business\Repositories;

use Application\Api\Business\Requests\BusinessCategoryRequest;
use Application\Api\Business\Resources\CategoryResource;
use Application\Api\Business\Resources\FilterResource;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Business\Models\Category;
use Domain\Business\Repositories\Contracts\ICategoryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class CategoryRepository.
 */
class CategoryRepository implements ICategoryRepository
{
    use GlobalFunc;

    /**
     * Get the businessCategories pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return Category::query()
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the businessCategories.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function allCategories() {
        $categories = Category::query()
            ->select('id', 'title', 'image', 'status')
            ->with('children')
            ->where('parent_id', 0)
            ->where('status', 1)
            ->orderBy('priority', 'desc')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Get the children of a specific category.
     * @param Category $category
     * @return Collection
     */
    public function getCategoryChildren(Category $category): Collection
    {
        return Category::query()
            ->select('id', 'title', 'image', 'status')
            ->where('parent_id', $category->id)
            ->where('status', 1)
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get all parent categories.
     * @return Collection
     */
    public function getParentCategories(): Collection
    {
        return Category::query()
            ->select('id', 'title', 'image', 'status')
            ->where('parent_id', 0)
            ->where('status', 1)
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get the businessCategories.
     * @return Collection
     */
    public function activeBusinessCategories() :Collection
    {
        return Category::query()
            ->select('id', 'title', 'image')
            ->where('parent_id', 0)
            ->where('status', 1)
            ->orderBy('priority', 'desc')
            ->limit(config('business.category_limit'))
            ->get();
    }

    /**
     * Get the Category.
     * @param Category $category
     * @return Category
     */
    public function show(Category $category) :Category
    {
        return Category::query()
                ->where('id', $category->id)
                ->first();
    }

    /**
     * Get the Category with parent hierarchy.
     * @param Category $category
     * @return Category
     */
    public function showWithParents(Category $category) :Category
    {
        return Category::query()
                ->with(['parent' => function ($query) {
                    $query->select('id', 'title', 'image', 'status', 'parent_id');
                }])
                ->where('id', $category->id)
                ->first();
    }

    /**
     * Get the filters associated with a specific category.
     * @param TableRequest $request
     * @param Category $category
     * @return LengthAwarePaginator
     */
    public function getCategoryFilters(TableRequest $request, Category $category) :LengthAwarePaginator
    {
        $search = $request->get('query');
        $filters = $category->filters()
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->where('status', 1)
            ->orderBy($request->get('column', 'priority'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));

        return $filters->through(fn ($filter) => new FilterResource($filter));

    }

    /**
     * Store the businessCategory.
     * @param BusinessCategoryRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(BusinessCategoryRequest $request) :JsonResponse
    {
        $this->checkLevelAccess();

        $businessCategory = Category::create([
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'user_id' => Auth::user()->id,
        ]);

        if ($businessCategory) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_CREATED);
        }

        throw new \Exception();
    }

    /**
     * Update the businessCategory.
     * @param BusinessCategoryRequest $request
     * @param Category $businessCategory
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(BusinessCategoryRequest $request, Category $businessCategory) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->id == $businessCategory->user_id);

        $businessCategory = $businessCategory->update([
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'user_id' => Auth::user()->id,
        ]);

        if ($businessCategory) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

    /**
    * Delete the businessCategory.
    * @param UpdatePasswordRequest $request
    * @param Category $businessCategory
    * @return JsonResponse
    */
   public function destroy(Category $businessCategory) :JsonResponse
   {
        $this->checkLevelAccess(Auth::user()->id == $businessCategory->user_id);

        $businessCategory->delete();

        if ($businessCategory) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
   }
}