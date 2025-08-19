<?php

namespace Application\Api\Business\Controllers;

use Application\Api\Business\Requests\BusinessRequest;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Business\Models\Business;
use Domain\Business\Models\Category;
use Domain\Business\Repositories\Contracts\IBusinessRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Application\Api\Business\Requests\SearchBusinessRequest;


class BusinessController extends Controller
{

    /**
     * @param IBusinessRepository $repository
     */
    public function __construct(protected IBusinessRepository $repository)
    {

    }

    /**
     * Get all of businesses with pagination
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function index(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->index($request), Response::HTTP_OK);
    }

    /**
     * Get the business.
     * @param Business $business
     * @return JsonResponse
     */
    public function show(Business $business) :JsonResponse
    {
        return response()->json($this->repository->show($business), Response::HTTP_OK);
    }

    /**
     * Edit the business.
     * @param Business $business
     * @return JsonResponse
     */
    public function edit(Business $business) :JsonResponse
    {
        return response()->json($this->repository->edit($business), Response::HTTP_OK);
    }

    /**
     * Store the business.
     * @param BusinessRequest $request
     * @return JsonResponse
     */
    public function store(BusinessRequest $request) :JsonResponse
    {
        return $this->repository->store($request);
    }

    /**
     * Update the business.
     * @param BusinessRequest $request
     * @param Business $business
     * @return JsonResponse
     */
    public function update(BusinessRequest $request, Business $business) :JsonResponse
    {
        return $this->repository->update($request, $business);
    }

    /**
     * Delete the business.
     * @param Business $business
     * @return JsonResponse
     */
    public function destroy(Business $business) :JsonResponse
    {
        return $this->repository->destroy($business);
    }

    /**
     * Get featured businesses by type.
     * @param Request $request
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        return response()->json([
            'status' => 1,
            'data' => $this->repository->getFeaturedBusinesses()
        ], Response::HTTP_OK);
    }

    /**
     * Search businesses with filters.
     * @param SearchBusinessRequest $request
     * @return JsonResponse
     */
    public function search(SearchBusinessRequest $request): JsonResponse
    {
        return response()->json($this->repository->search($request), Response::HTTP_OK);
    }
}
