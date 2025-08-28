<?php

namespace Application\Api\Event\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Event\Repositories\Contracts\IEventRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Domain\Event\Models\Event;

class EventController extends Controller
{

    /**
     * @param IEventRepository $repository
     */
    public function __construct(protected IEventRepository $repository)
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
     * Get the event.
     * @param Event $event
     * @return JsonResponse
     */
    public function show(Event $event) :JsonResponse
    {
        return response()->json($this->repository->show($event), Response::HTTP_OK);
    }

    /**
     * Get featured events by type.
     * @param Request $request
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        return response()->json([
            'status' => 1,
            'data' => $this->repository->getFeaturedEvents()
        ], Response::HTTP_OK);
    }

    /**
     * Favorite the event.
     * @param Event $event
     * @return JsonResponse
     */
    public function favorite(Event $event) :JsonResponse
    {
        return $this->repository->favorite($event);
    }

    /**
     * Get favorite events.
     * @param TableRequest $request
     * @return JsonResponse
     */
    public function getFavoriteEvents(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->getFavoriteEvents($request), Response::HTTP_OK);
    }

}
