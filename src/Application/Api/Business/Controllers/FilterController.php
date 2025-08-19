<?php

namespace Application\Api\Business\Controllers;

use Application\Api\Business\Resources\FilterResource;
use Core\Http\Controllers\Controller;
use Domain\Business\Models\Filter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FilterController extends Controller
{
    /**
     * Get all active filters.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = Filter::where('status', 1)
            ->orderBy('title')
            ->get();

        return FilterResource::collection($filters);
    }

    /**
     * Get a specific filter.
     *
     * @param Filter $filter
     * @return FilterResource
     */
    public function show(Filter $filter): FilterResource
    {
        return new FilterResource($filter);
    }
}
