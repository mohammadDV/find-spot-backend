<?php

namespace Application\Api\Review\Controllers;

use Application\Api\Review\Requests\ReviewRequest;
use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Business\Models\Business;
use Domain\Review\Models\Review;
use Domain\Review\Repositories\Contracts\IReviewRepository;
use Domain\User\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;


class ReviewController extends Controller
{

    /**
     * @param IReviewRepository $repository
     */
    public function __construct(protected IReviewRepository $repository)
    {

    }

    /**
     * Get my reviews with pagination
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function myReviews(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->myReviews($request), Response::HTTP_OK);
    }

    /**
     * Get the reviews per business pagination.
     * @param TableRequest $request
     * @param Business $business
     * @return JsonResponse
     */
    public function getReviewsPerBusiness(TableRequest $request, Business $business): JsonResponse
    {
        return response()->json($this->repository->getReviewsPerBusiness($request, $business), Response::HTTP_OK);
    }

    /**
     * Get the review.
     * @param
     * @return JsonResponse
     */
    public function show(Review $review) :JsonResponse
    {
        return response()->json($this->repository->show($review), Response::HTTP_OK);
    }

    /**
     * Store the review.
     * @param Business $business
     * @param ReviewRequest $request
     * @return JsonResponse
     */
    public function store(Business $business, ReviewRequest $request) :JsonResponse
    {
        return $this->repository->store($business, $request);
    }

    /**
     * Update the review.
     * @param ReviewRequest $request
     * @param Review $review
     * @return JsonResponse
     */
    public function update(ReviewRequest $request, Review $review) :JsonResponse
    {
        return $this->repository->update($request, $review);
    }

    /**
     * Delete the review.
     * @param Review $review
     * @return JsonResponse
     */
    public function destroy(Review $review) :JsonResponse
    {
        return $this->repository->destroy($review);
    }
}
