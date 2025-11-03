<?php

namespace Tests\Feature;

use Application\Api\Business\Requests\BusinessRequest;
use Application\Api\Business\Requests\SearchBusinessRequest;
use Core\Http\Requests\TableRequest;
use Domain\Address\Models\Area;
use Domain\Address\Models\City;
use Domain\Address\Models\Country;
use Domain\Business\Models\Business;
use Domain\Business\Models\Category;
use Domain\Business\Models\Facility;
use Domain\Business\Models\Favorite;
use Domain\Business\Models\File;
use Domain\Business\Models\Filter;
use Domain\Business\Models\Service;
use Domain\Business\Models\ServiceVote;
use Domain\Business\Models\Tag;
use Domain\Business\Models\Weekend;
use Domain\Business\Repositories\BusinessRepository;
use Domain\Review\Models\Review;
use Domain\User\Models\User;
use Domain\User\Services\TelegramNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BusinessRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected BusinessRepository $repository;
    protected User $user;
    protected User $anotherUser;
    protected Country $country;
    protected City $city;
    protected Area $area;
    protected Category $category;
    protected Category $subCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock TelegramNotificationService
        $telegramService = Mockery::mock(TelegramNotificationService::class);
        $telegramService->shouldReceive('sendNotification')->andReturn(true);

        $this->repository = new BusinessRepository($telegramService);

        // Create test users
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'nickname' => 'testuser',
            'customer_number' => 'CUST001',
            'role_id' => 2,
            'status' => 1,
            'email' => 'test@example.com',
            'mobile' => '09123456789',
            'password' => bcrypt('password'),
            'level' => 0,
            'verified_at' => now(),
        ]);

        $this->anotherUser = User::create([
            'first_name' => 'Another',
            'last_name' => 'User',
            'nickname' => 'anotheruser',
            'customer_number' => 'CUST002',
            'role_id' => 2,
            'status' => 1,
            'email' => 'another@example.com',
            'mobile' => '09123456788',
            'password' => bcrypt('password'),
            'level' => 0,
            'verified_at' => now(),
        ]);

        // Create address data
        $this->country = Country::create(['title' => 'Test Country']);
        $this->city = City::create(['title' => 'Test City', 'country_id' => $this->country->id]);
        $this->area = Area::create(['title' => 'Test Area', 'city_id' => $this->city->id]);

        // Create categories
        $this->category = Category::create([
            'title' => 'Test Category',
            'status' => 1,
            'parent_id' => 0,
        ]);

        $this->subCategory = Category::create([
            'title' => 'Test Sub Category',
            'status' => 1,
            'parent_id' => $this->category->id,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_get_paginated_businesses_for_authenticated_user()
    {
        Auth::login($this->user);

        // Create businesses for the user
        Business::create($this->getBusinessData(['title' => 'Business 1', 'user_id' => $this->user->id]));
        Business::create($this->getBusinessData(['title' => 'Business 2', 'user_id' => $this->user->id]));
        Business::create($this->getBusinessData(['title' => 'Another Business', 'user_id' => $this->anotherUser->id]));

        $request = new TableRequest([
            'count' => 25,
            'column' => 'id',
            'sort' => 'desc'
        ]);

        $result = $this->repository->index($request);

        $this->assertCount(2, $result);
        $this->assertEquals('Business 2', $result[0]->title);
    }

    #[Test]
    public function it_can_search_businesses_by_title()
    {
        Auth::login($this->user);

        Business::create($this->getBusinessData(['title' => 'Restaurant ABC', 'user_id' => $this->user->id]));
        Business::create($this->getBusinessData(['title' => 'Hotel XYZ', 'user_id' => $this->user->id]));

        $request = new TableRequest([
            'query' => 'Restaurant',
            'count' => 25,
        ]);

        $result = $this->repository->index($request);

        $this->assertCount(1, $result);
        $this->assertStringContainsString('Restaurant', $result[0]->title);
    }

    #[Test]
    public function it_can_filter_businesses_by_status()
    {
        Auth::login($this->user);

        Business::create($this->getBusinessData([
            'title' => 'Pending Business',
            'user_id' => $this->user->id,
            'status' => Business::PENDING
        ]));

        Business::create($this->getBusinessData([
            'title' => 'Approved Business',
            'user_id' => $this->user->id,
            'status' => Business::APPROVED
        ]));

        $request = new TableRequest([
            'status' => Business::APPROVED,
            'count' => 25,
        ]);

        $result = $this->repository->index($request);

        $this->assertCount(1, $result);
        $this->assertEquals('Approved Business', $result[0]->title);
    }

    #[Test]
    public function it_can_show_business_with_relationships()
    {
        Auth::login($this->user);

        $business = Business::create($this->getBusinessData(['user_id' => $this->user->id]));
        $business->categories()->attach($this->category->id);
        $business->tags()->create(['title' => 'Tag 1', 'status' => 1]);
        $business->files()->create(['path' => 'test/image.jpg', 'type' => 'image', 'status' => 1]);

        $result = $this->repository->show($business);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('business', $result);
        $this->assertArrayHasKey('quality_services', $result);
        $this->assertArrayHasKey('reviews', $result);
        $this->assertArrayHasKey('is_favorite', $result);
        $this->assertFalse($result['is_favorite']);
    }

    #[Test]
    public function it_can_show_business_with_favorite_status()
    {
        // Create a token for authentication
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Set the bearer token in the request
        request()->headers->set('Authorization', 'Bearer ' . $token);

        $business = Business::create($this->getBusinessData(['user_id' => $this->user->id]));

        // Mark as favorite
        Favorite::create([
            'favoritable_type' => Business::class,
            'favoritable_id' => $business->id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->repository->show($business);

        $this->assertTrue($result['is_favorite']);
    }

    #[Test]
    public function it_can_edit_business_for_owner()
    {
        Auth::login($this->user);

        $business = Business::create($this->getBusinessData(['user_id' => $this->user->id]));
        $business->categories()->attach($this->category->id);

        $result = $this->repository->edit($business);

        $this->assertInstanceOf(Business::class, $result);
        $this->assertEquals($business->id, $result->id);
        $this->assertNotEmpty($result->categories);
    }

    #[Test]
    public function it_prevents_editing_business_for_non_owner()
    {
        $this->expectException(\Exception::class);

        Auth::login($this->anotherUser);

        $business = Business::create($this->getBusinessData(['user_id' => $this->user->id]));

        $this->repository->edit($business);
    }

    #[Test]
    public function it_can_store_new_business()
    {
        Auth::login($this->user);

        $filter1 = Filter::create(['title' => 'Filter 1', 'status' => 1]);
        $filter2 = Filter::create(['title' => 'Filter 2', 'status' => 1]);
        $facility = Facility::create(['title' => 'Facility 1', 'status' => 1]);

        $requestData = [
            'title' => 'New Business',
            'description' => 'A new business description',
            'lat' => '35.6892',
            'long' => '51.3890',
            'phone' => '09123456789',
            'email' => 'business@example.com',
            'address' => '123 Test Street',
            'start_amount' => 100,
            'amount_type' => 1,
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'categories' => [$this->category->id],
            'filters' => [$filter1->id, $filter2->id],
            'facilities' => [$facility->id],
            'tags' => ['Tag 1', 'Tag 2'],
            'files' => [
                ['path' => 'test/image1.jpg', 'type' => 'image', 'status' => 1],
                ['path' => 'test/image2.jpg', 'type' => 'image', 'status' => 1],
            ],
        ];

        $request = new BusinessRequest($requestData);
        $response = $this->repository->store($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals(1, $responseData['status']);

        $this->assertDatabaseHas('businesses', [
            'title' => 'New Business',
            'user_id' => $this->user->id,
            'status' => Business::PENDING,
        ]);

        $business = Business::where('title', 'New Business')->first();
        $this->assertCount(1, $business->categories);
        $this->assertCount(2, $business->filters);
        $this->assertCount(1, $business->facilities);
        $this->assertCount(2, $business->tags);
        $this->assertCount(2, $business->files);
    }

    #[Test]
    public function it_prevents_unverified_user_from_creating_business()
    {
        $unverifiedUser = User::create([
            'first_name' => 'Unverified',
            'last_name' => 'User',
            'nickname' => 'unverified',
            'customer_number' => 'CUST003',
            'role_id' => 2,
            'status' => 1,
            'email' => 'unverified@example.com',
            'mobile' => '09123456787',
            'password' => bcrypt('password'),
            'level' => 0,
            'verified_at' => null,
        ]);

        Auth::login($unverifiedUser);

        $requestData = [
            'title' => 'New Business',
            'lat' => '35.6892',
            'long' => '51.3890',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
        ];

        $request = new BusinessRequest($requestData);
        $response = $this->repository->store($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals(0, $responseData['status']);
    }

    #[Test]
    public function it_prevents_inactive_user_from_creating_business()
    {
        $inactiveUser = User::create([
            'first_name' => 'Inactive',
            'last_name' => 'User',
            'nickname' => 'inactive',
            'customer_number' => 'CUST004',
            'role_id' => 2,
            'status' => 0,
            'email' => 'inactive@example.com',
            'mobile' => '09123456786',
            'password' => bcrypt('password'),
            'level' => 0,
            'verified_at' => now(),
        ]);

        Auth::login($inactiveUser);

        $requestData = [
            'title' => 'New Business',
            'lat' => '35.6892',
            'long' => '51.3890',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
        ];

        $request = new BusinessRequest($requestData);
        $response = $this->repository->store($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals(0, $responseData['status']);
    }

    #[Test]
    public function it_can_update_existing_business()
    {
        Auth::login($this->user);

        $business = Business::create($this->getBusinessData(['user_id' => $this->user->id]));
        $business->categories()->attach($this->category->id);
        $business->tags()->create(['title' => 'Old Tag', 'status' => 1]);

        $requestData = [
            'title' => 'Updated Business',
            'description' => 'Updated description',
            'lat' => '35.6892',
            'long' => '51.3890',
            'phone' => '09123456789',
            'email' => 'updated@business.com',
            'address' => '456 Updated Street',
            'start_amount' => 200,
            'amount_type' => 2,
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'categories' => [$this->subCategory->id],
            'tags' => ['New Tag 1', 'New Tag 2'],
            'from_monday' => 9,
            'to_monday' => 21,
            'from_tuesday' => 9,
            'to_tuesday' => 21,
            'from_wednesday' => 9,
            'to_wednesday' => 21,
            'from_thursday' => 9,
            'to_thursday' => 21,
            'from_friday' => 9,
            'to_friday' => 21,
            'from_saturday' => 9,
            'to_saturday' => 21,
            'from_sunday' => 9,
            'to_sunday' => 21,
        ];

        $request = new BusinessRequest($requestData);
        $response = $this->repository->update($request, $business);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $business->refresh();
        $this->assertEquals('Updated Business', $business->title);
        $this->assertEquals('Updated description', $business->description);
        $this->assertEquals(Business::PENDING, $business->status);
        $this->assertCount(1, $business->categories);
        $this->assertCount(2, $business->tags);
    }

    #[Test]
    public function it_prevents_non_owner_from_updating_business()
    {
        $this->expectException(\Exception::class);

        Auth::login($this->anotherUser);

        $business = Business::create($this->getBusinessData(['user_id' => $this->user->id]));

        $requestData = [
            'title' => 'Updated Business',
            'lat' => '35.6892',
            'long' => '51.3890',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
        ];

        $request = new BusinessRequest($requestData);
        $this->repository->update($request, $business);
    }

    #[Test]
    public function it_can_toggle_favorite_status()
    {
        Auth::login($this->user);

        $business = Business::create($this->getBusinessData(['user_id' => $this->anotherUser->id]));

        // First time - add to favorites
        $response = $this->repository->favorite($business);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertEquals(1, $responseData['favorite']);

        $this->assertDatabaseHas('favorites', [
            'favoritable_type' => Business::class,
            'favoritable_id' => $business->id,
            'user_id' => $this->user->id,
        ]);

        // Second time - remove from favorites
        $response = $this->repository->favorite($business);
        $responseData = $response->getData(true);
        $this->assertEquals(0, $responseData['favorite']);

        $this->assertDatabaseMissing('favorites', [
            'favoritable_type' => Business::class,
            'favoritable_id' => $business->id,
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_can_get_favorite_businesses()
    {
        Auth::login($this->user);

        $business1 = Business::create($this->getBusinessData(['title' => 'Favorite 1', 'user_id' => $this->anotherUser->id]));
        $business2 = Business::create($this->getBusinessData(['title' => 'Favorite 2', 'user_id' => $this->anotherUser->id]));
        $business3 = Business::create($this->getBusinessData(['title' => 'Not Favorite', 'user_id' => $this->anotherUser->id]));

        // Add to favorites
        Favorite::create([
            'favoritable_type' => Business::class,
            'favoritable_id' => $business1->id,
            'user_id' => $this->user->id,
        ]);

        Favorite::create([
            'favoritable_type' => Business::class,
            'favoritable_id' => $business2->id,
            'user_id' => $this->user->id,
        ]);

        $request = new TableRequest(['count' => 25]);
        $result = $this->repository->getFavoriteBusinesses($request);

        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_can_get_similar_businesses()
    {
        $business = Business::create($this->getBusinessData([
            'title' => 'Main Business',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));
        $business->categories()->attach($this->category->id);

        $similar1 = Business::create($this->getBusinessData([
            'title' => 'Similar 1',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));
        $similar1->categories()->attach($this->category->id);

        $similar2 = Business::create($this->getBusinessData([
            'title' => 'Similar 2',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));
        $similar2->categories()->attach($this->category->id);

        $different = Business::create($this->getBusinessData([
            'title' => 'Different',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));
        $different->categories()->attach($this->subCategory->id);

        $result = $this->repository->similarBusinesses($business);

        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_can_get_featured_businesses()
    {
        Business::create($this->getBusinessData([
            'title' => 'Featured 1',
            'status' => Business::APPROVED,
            'active' => 1,
            'priority' => 10,
        ]));

        Business::create($this->getBusinessData([
            'title' => 'Featured 2',
            'status' => Business::APPROVED,
            'active' => 1,
            'priority' => 5,
        ]));

        $result = $this->repository->getFeaturedBusinesses();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('offers', $result);
        $this->assertArrayHasKey('weekends', $result);
        $this->assertNotEmpty($result['offers']);
    }

    #[Test]
    public function it_can_get_weekends_with_businesses()
    {
        $weekend = Weekend::create([
            'title' => 'Summer Weekend',
            'status' => 1,
        ]);

        $business = Business::create($this->getBusinessData([
            'title' => 'Weekend Business',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));

        $weekend->businesses()->attach($business->id);

        $result = $this->repository->getWeekends();

        $this->assertIsArray($result);
        $this->assertArrayHasKey($weekend->id, $result);
        $this->assertEquals('Summer Weekend', $result[$weekend->id]['title']);
    }

    #[Test]
    public function it_can_search_suggestions()
    {
        Category::create([
            'title' => 'Restaurant Category',
            'status' => 1,
        ]);

        Business::create($this->getBusinessData([
            'title' => 'Restaurant ABC',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));

        $request = new TableRequest(['query' => 'Restaurant']);
        $result = $this->repository->searchSuggestions($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('businesses', $result);
        $this->assertArrayHasKey('categories', $result);
    }

    #[Test]
    public function it_can_search_businesses_with_filters()
    {
        $filter1 = Filter::create(['title' => 'WiFi', 'status' => 1]);
        $filter2 = Filter::create(['title' => 'Parking', 'status' => 1]);

        $business1 = Business::create($this->getBusinessData([
            'title' => 'Business with WiFi and Parking',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));
        $business1->filters()->attach([$filter1->id, $filter2->id]);

        $business2 = Business::create($this->getBusinessData([
            'title' => 'Business with only WiFi',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));
        $business2->filters()->attach([$filter1->id]);

        $request = new SearchBusinessRequest([
            'filters' => [$filter1->id, $filter2->id],
            'count' => 25,
        ]);

        $result = $this->repository->search($request);

        $this->assertCount(1, $result);
        $this->assertEquals('Business with WiFi and Parking', $result[0]->title);
    }

    #[Test]
    public function it_can_search_businesses_by_category()
    {
        $business1 = Business::create($this->getBusinessData([
            'title' => 'Business in Category',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));
        $business1->categories()->attach($this->category->id);

        $business2 = Business::create($this->getBusinessData([
            'title' => 'Business in SubCategory',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));
        $business2->categories()->attach($this->subCategory->id);

        $business3 = Business::create($this->getBusinessData([
            'title' => 'Business without Category',
            'status' => Business::APPROVED,
            'active' => 1,
        ]));

        $request = new SearchBusinessRequest([
            'category' => $this->category->id,
            'count' => 25,
        ]);

        $result = $this->repository->search($request);

        $this->assertCount(2, $result); // Should include both parent and child category
    }

    #[Test]
    public function it_can_search_businesses_by_amount_type()
    {
        Business::create($this->getBusinessData([
            'title' => 'Budget Business',
            'status' => Business::APPROVED,
            'active' => 1,
            'amount_type' => 1,
        ]));

        Business::create($this->getBusinessData([
            'title' => 'Premium Business',
            'status' => Business::APPROVED,
            'active' => 1,
            'amount_type' => 3,
        ]));

        $request = new SearchBusinessRequest([
            'amount_type' => 1,
            'count' => 25,
        ]);

        $result = $this->repository->search($request);

        $this->assertCount(1, $result);
        $this->assertEquals('Budget Business', $result[0]->title);
    }

    #[Test]
    public function it_can_search_businesses_that_are_currently_open()
    {
        $currentHour = intval(now()->setTimezone(new \DateTimeZone('Asia/Istanbul'))->format('H'));
        $dayOfWeek = strtolower(now()->format('l'));

        Business::create($this->getBusinessData([
            'title' => 'Open Now',
            'status' => Business::APPROVED,
            'active' => 1,
            'from_' . $dayOfWeek => $currentHour - 1,
            'to_' . $dayOfWeek => $currentHour + 2,
        ]));

        Business::create($this->getBusinessData([
            'title' => 'Closed Now',
            'status' => Business::APPROVED,
            'active' => 1,
            'from_' . $dayOfWeek => $currentHour + 3,
            'to_' . $dayOfWeek => $currentHour + 5,
        ]));

        $request = new SearchBusinessRequest([
            'now' => 1,
            'count' => 25,
        ]);

        $result = $this->repository->search($request);

        $this->assertCount(1, $result);
        $this->assertEquals('Open Now', $result[0]->title);
    }

    #[Test]
    public function it_can_search_businesses_near_location()
    {
        // Business near the search location
        Business::create($this->getBusinessData([
            'title' => 'Nearby Business',
            'status' => Business::APPROVED,
            'active' => 1,
            'lat' => '35.6892',
            'long' => '51.3890',
        ]));

        // Business far from search location
        Business::create($this->getBusinessData([
            'title' => 'Far Business',
            'status' => Business::APPROVED,
            'active' => 1,
            'lat' => '40.7128',
            'long' => '74.0060',
        ]));

        $request = new SearchBusinessRequest([
            'lat' => '35.6892',
            'long' => '51.3890',
            'count' => 25,
        ]);

        $result = $this->repository->search($request);

        $this->assertCount(1, $result);
        $this->assertEquals('Nearby Business', $result[0]->title);
    }

    #[Test]
    public function it_can_search_businesses_by_area()
    {
        $anotherArea = Area::create(['title' => 'Another Area', 'city_id' => $this->city->id]);

        Business::create($this->getBusinessData([
            'title' => 'Business in Test Area',
            'status' => Business::APPROVED,
            'active' => 1,
            'area_id' => $this->area->id,
        ]));

        Business::create($this->getBusinessData([
            'title' => 'Business in Another Area',
            'status' => Business::APPROVED,
            'active' => 1,
            'area_id' => $anotherArea->id,
        ]));

        $request = new SearchBusinessRequest([
            'area_id' => $this->area->id,
            'count' => 25,
        ]);

        $result = $this->repository->search($request);

        $this->assertCount(1, $result);
        $this->assertEquals('Business in Test Area', $result[0]->title);
    }

    #[Test]
    public function it_can_get_reviews_by_rate()
    {
        $business = Business::create($this->getBusinessData([
            'user_id' => $this->user->id,
            'status' => Business::APPROVED,
        ]));

        // Create reviews with different rates
        Review::create([
            'business_id' => $business->id,
            'user_id' => $this->anotherUser->id,
            'comment' => 'Excellent',
            'rate' => 5,
            'active' => 1,
            'status' => Review::APPROVED,
        ]);

        Review::create([
            'business_id' => $business->id,
            'user_id' => $this->anotherUser->id,
            'comment' => 'Good',
            'rate' => 4,
            'active' => 1,
            'status' => Review::APPROVED,
        ]);

        Review::create([
            'business_id' => $business->id,
            'user_id' => $this->anotherUser->id,
            'comment' => 'Excellent again',
            'rate' => 5,
            'active' => 1,
            'status' => Review::APPROVED,
        ]);

        $result = $this->repository->getReviewsByRate($business->id);

        $this->assertCount(2, $result); // 2 different rates (5 and 4)
        $this->assertEquals(5, $result[0]['rate']);
        $this->assertEquals(2, $result[0]['count']);
        $this->assertEquals(4, $result[1]['rate']);
        $this->assertEquals(1, $result[1]['count']);
    }

    #[Test]
    public function it_can_get_service_votes()
    {
        $business = Business::create($this->getBusinessData([
            'user_id' => $this->user->id,
            'status' => Business::APPROVED,
        ]));

        $service1 = Service::create([
            'title' => 'Service Quality',
            'status' => 1,
            'category_id' => $this->category->id,
        ]);

        $service2 = Service::create([
            'title' => 'Customer Support',
            'status' => 1,
            'category_id' => $this->category->id,
        ]);

        // Create reviews first (service votes are linked to reviews)
        $review1 = Review::create([
            'business_id' => $business->id,
            'user_id' => $this->anotherUser->id,
            'comment' => 'Great service',
            'rate' => 5,
            'active' => 1,
            'status' => Review::APPROVED,
        ]);

        $review2 = Review::create([
            'business_id' => $business->id,
            'user_id' => $this->user->id,
            'comment' => 'Good quality',
            'rate' => 4,
            'active' => 1,
            'status' => Review::APPROVED,
        ]);

        $review3 = Review::create([
            'business_id' => $business->id,
            'user_id' => $this->anotherUser->id,
            'comment' => 'Nice support',
            'rate' => 4,
            'active' => 1,
            'status' => Review::APPROVED,
        ]);

        // Create service votes
        ServiceVote::create([
            'business_id' => $business->id,
            'service_id' => $service1->id,
            'review_id' => $review1->id,
        ]);

        ServiceVote::create([
            'business_id' => $business->id,
            'service_id' => $service1->id,
            'review_id' => $review2->id,
        ]);

        ServiceVote::create([
            'business_id' => $business->id,
            'service_id' => $service2->id,
            'review_id' => $review3->id,
        ]);

        $result = $this->repository->getServiceVotes($business->id);

        $this->assertCount(2, $result);
        $this->assertEquals('Service Quality', $result[0]['title']);
        $this->assertEquals(2, $result[0]['count']);
        $this->assertEquals('Customer Support', $result[1]['title']);
        $this->assertEquals(1, $result[1]['count']);
    }

    /**
     * Helper method to get default business data
     */
    protected function getBusinessData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Business',
            'description' => 'Test Description',
            'lat' => '35.6892',
            'long' => '51.3890',
            'phone' => '09123456789',
            'email' => 'test@business.com',
            'address' => '123 Test Street',
            'start_amount' => 100,
            'amount_type' => 1,
            'active' => 1,
            'status' => Business::PENDING,
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'user_id' => $this->user->id,
            'from_monday' => 8,
            'to_monday' => 20,
            'from_tuesday' => 8,
            'to_tuesday' => 20,
            'from_wednesday' => 8,
            'to_wednesday' => 20,
            'from_thursday' => 8,
            'to_thursday' => 20,
            'from_friday' => 8,
            'to_friday' => 20,
            'from_saturday' => 8,
            'to_saturday' => 20,
            'from_sunday' => 8,
            'to_sunday' => 20,
        ], $overrides);
    }
}
