<?php

namespace Domain\Review\Repositories\Contracts;

use Application\Api\Review\Requests\ReviewRequest;
use Application\Api\Review\Resources\ReviewResource;
use Core\Http\Requests\TableRequest;
use Domain\Business\Models\Business;
use Domain\Review\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IReviewRepository.
 */
interface IReviewRepository
{
    /**
     * Get my reviews with pagination
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function myReviews(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the review.
     * @param Review $review
     * @return ReviewResource
     */
    public function show(Review $review) :ReviewResource;

    /**
     * Get the review per business.
     * @param TableRequest $request
     * @param Business $business
     * @return LengthAwarePaginator
     */
    public function getReviewsPerBusiness(TableRequest $request, Business $business) :LengthAwarePaginator;

    /**
     * Store the review.
     * @param Business $business
     * @param ReviewRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Business $business, ReviewRequest $request) :JsonResponse;

    /**
     * Update the review.
     * @param ReviewRequest $request
     * @param Review $review
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(ReviewRequest $request, Review $review) :JsonResponse;
}
