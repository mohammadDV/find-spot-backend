<?php

use Application\Api\Address\Controllers\AddressController;
use Application\Api\Chat\Controllers\ChatController;
use Application\Api\File\Controllers\FileController;
use Application\Api\IdentityRecord\Controllers\IdentityRecordController;
use Application\Api\Notification\Controllers\NotificationController;
use Application\Api\Post\Controllers\PostController;
use Application\Api\Business\Controllers\CategoryController;
use Application\Api\Business\Controllers\BusinessController;
use Application\Api\Business\Controllers\FilterController;
use Application\Api\Event\Controllers\EventController;
use Application\Api\Review\Controllers\ReviewController;
use Application\Api\Ticket\Controllers\TicketController;
use Application\Api\Ticket\Controllers\TicketSubjectController;
use Application\Api\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [AddressController::class, 'activeCountries'])->name('active-countries');
Route::get('/areas/{city}', [AddressController::class, 'activeAreas'])->name('active-areas');
Route::get('/cities/{country}', [AddressController::class, 'activeCities'])->name('active-cities');
Route::get('/cities/{city}/details', [AddressController::class, 'getCityDetails'])->name('city-details');
Route::get('/areas/{city}/search', [AddressController::class, 'getAreasPaginate'])->name('area-search');

// Plan
Route::get('/active-categories', [CategoryController::class, 'activeBusinessCategories'])->name('active-business-categories');
Route::get('/all-categories', [CategoryController::class, 'allCategories'])->name('all-categories');
Route::get('/parent-categories', [CategoryController::class, 'getParentCategories'])->name('parent-categories');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/categories/{category}/children', [CategoryController::class, 'getCategoryChildren'])->name('category-children');
Route::get('/categories/{category}/filters', [CategoryController::class, 'getCategoryFilters'])->name('category.filters');


// Businesses
Route::prefix('businesses')->group(function () {
    Route::get('featured', [BusinessController::class, 'featured']);
    Route::get('search', [BusinessController::class, 'search']);
    Route::get('search-suggestions', [BusinessController::class, 'searchSuggestions']);
    Route::get('{business}', [BusinessController::class, 'show']);
    Route::get('{business}/reviews', [ReviewController::class, 'getReviewsPerBusiness'])->name('business.reviews.get');
    Route::get('{business}/similar', [BusinessController::class, 'similarBusinesses'])->name('business.similar');
});

// Events
Route::prefix('events')->group(function () {
    Route::get('featured', [EventController::class, 'featured'])->name('events.featured');
    Route::get('sliders', [EventController::class, 'sliders'])->name('events.vip');
    Route::get('/', [EventController::class, 'index'])->name('events.index');
    Route::get('{event}', [EventController::class, 'show'])->name('events.show');
});


// Route::get('/filters', [FilterController::class, 'index'])->name('filters.index');
// Route::get('/filters/{filter}', [FilterController::class, 'show'])->name('filters.show');
// Route::get('/business/{business}', [BusinessController::class, 'show'])->name('business.show');
// Route::get('/user-info/{user}', [UserController::class, 'getUserInfo'])->name('user.show');
// Route::get('/active-subjects', [TicketSubjectController::class, 'activeSubjects'])->name('active-subjects');
// Route::get('/user/{user}/reviews', [ReviewController::class, 'getReviewsPerUser']);


Route::get('/posts', [PostController::class, 'getPosts'])->name('site.posts.index');
Route::get('/posts/popular', [PostController::class, 'getPopularPosts'])->name('site.posts.popular');
Route::get('/posts/latest', [PostController::class, 'getLatestPosts'])->name('site.posts.latest');
Route::get('/post/{post}', [PostController::class, 'getPostInfo'])->name('site.post.info');

Route::middleware(['auth:sanctum', 'auth', 'throttle:200,1'])->prefix('profile')->name('profile.')->group(function() {

    // business
    Route::get('my-businesses', [BusinessController::class, 'index'])->name('businesses.index');
    Route::post('businesses', [BusinessController::class, 'store'])->name('businesses.store');
    Route::get('businesses/{business}/edit', [BusinessController::class, 'edit'])->name('businesses.edit');
    Route::patch('businesses/{business}', [BusinessController::class, 'update'])->name('businesses.update');
    Route::get('businesses/{business}/favorite', [BusinessController::class, 'favorite'])->name('businesses.favorite');
    Route::get('businesses/favorite', [BusinessController::class, 'getFavoriteBusinesses'])->name('businesses.favorite.index');


    // event
    Route::get('events/{event}/favorite', [EventController::class, 'favorite'])->name('events.favorite');
    Route::get('events/favorite', [EventController::class, 'getFavoriteEvents'])->name('events.favorite.index');


    Route::get('my-reviews', [ReviewController::class, 'myReviews'])->name('reviews.index');
    Route::post('reviews/{business}', [ReviewController::class, 'store'])->name('reviews.store');
    Route::patch('reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');


    // Route::get('/check-verification', [UserController::class, 'checkVerification'])->name('user.check.verification');

    // // update user
    // Route::patch('/users/{user}', [UserController::class, 'update'])->name('user.update');
    // Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    // Route::patch('/users/{user}/change-password', [UserController::class, 'changePassword'])->name('user.change-password');


    // Route::resource('notifications', NotificationController::class);
    // Route::get('/notifications-unread', [NotificationController::class, 'unread'])->name('unread-notifications');
    // Route::get('/notifications-read-all', [NotificationController::class, 'readAll'])->name('read-all-notifications');

    // Route::resource('identity-records', IdentityRecordController::class);
    // Route::get('identity-records-info/{user}', [IdentityRecordController::class, 'getIdentityInfo']);
    // Route::post('identity-records/{identityRecord}/change-status', [IdentityRecordController::class, 'changeStatus']);
    // Route::resource('business-categories', CategoryController::class);
    // Route::resource('businesses', BusinessController::class);
    // Route::post('/businesses/{business}/edit', [BusinessController::class, 'edit'])->name('business.edit');
    // Route::resource('tickets', TicketController::class);
    // Route::post('tickets/{ticket}/message', [TicketController::class, 'storeMessage'])->name('profile.ticket.message.store');
    // Route::post('/ticket-status/{ticket}', [TicketController::class, 'changeStatus'])->name('profile.ticket.change-status');
    // Route::resource('ticket-subjects', TicketSubjectController::class);



    // // activity count
    // Route::get('/activity-count', [UserController::class, 'getActivityCount'])->name('profile.activity.count');
    // Route::get('/dashboard-info', [UserController::class, 'getDashboardInfo'])->name('profile.dashboard.info');

    // // review
    // Route::post('/reviews/{business}', [ReviewController::class, 'store']);
    // Route::get('/business/{business}/reviews', [ReviewController::class, 'getReviewsPerBusiness']);
    // // just for admin
    // Route::patch('/reviews/{review}', [ReviewController::class, 'update']);
    // Route::get('/reviews', [ReviewController::class, 'index']);

    // // chats
    // Route::prefix('chats')->group(function () {
    //     Route::post('/', [ChatController::class, 'indexPaginate'])->name('profile.chat.index');
    //     Route::get('/{chat}', [ChatController::class, 'show'])->name('profile.chat.show');
    //     Route::get('/info/{chat}', [ChatController::class, 'chatInfo'])->name('profile.chat.info');
    //     Route::post('/{user}', [ChatController::class, 'store'])->name('profile.chat.store');
    //     Route::get('/businesses/{business}', [ChatController::class, 'getChatID'])->name('profile.chat.business');
    // });

});

// upload files
Route::middleware(['auth:sanctum', 'auth', 'throttle:10,1'])->group(function() {
    Route::post('/upload-image', [FileController::class, 'uploadImage'])->name('site.upload-image');
    Route::post('/upload-video', [FileController::class, 'uploadVideo'])->name('site.upload-video');
    Route::post('/upload-file', [FileController::class, 'uploadFile'])->name('site.upload-file');
});