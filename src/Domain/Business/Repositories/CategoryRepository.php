<?php

namespace Domain\Business\Repositories;

use Application\Api\Business\Requests\BusinessCategoryRequest;
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
     * Store the businessCategory.
     * @param BusinessCategoryRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(BusinessCategoryRequest $request) :JsonResponse
    {
        $this->checkLevelAccess();

        $businessCategory = BusinessCategory::create([
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
     * @param BusinessCategory $businessCategory
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(BusinessCategoryRequest $request, BusinessCategory $businessCategory) :JsonResponse
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
    * @param BusinessCategory $businessCategory
    * @return JsonResponse
    */
   public function destroy(BusinessCategory $businessCategory) :JsonResponse
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
