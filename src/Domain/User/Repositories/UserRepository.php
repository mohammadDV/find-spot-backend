<?php

namespace Domain\User\Repositories;

use Application\Api\Business\Resources\BusinessResource;
use Application\Api\User\Requests\ChangePasswordRequest;
use Application\Api\User\Requests\UpdateUserRequest;
use Application\Api\User\Resources\UserResource;
use Core\Http\traits\GlobalFunc;
use Domain\Notification\Services\NotificationService;
use Domain\Business\Models\Business;
use Domain\Ticket\Models\Ticket;
use Domain\User\Models\User;
use Domain\User\Repositories\Contracts\IUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserRepository.
 */
class UserRepository implements IUserRepository
{

    use GlobalFunc;

    /**
     * Get the users
     *
     * @return JsonResponse The seller object
     */
    public function index() :JsonResponse
    {
        return response()->json([]);
    }

    /**
     * Get the user info.
     * @param User $user
     * @return array
     */
    public function getUserInfo(User $user) :array
    {

        $senderQuery = Business::query()
            ->with([
                'area'
            ])
            ->where('user_id', $user->id)
            ->where('active', 1)
            ->orderBy('id', 'desc');

        $senderBusinesses = $senderQuery
            ->limit(4)
            ->get()
            ->map(fn ($business) => new BusinessResource($business));

        $senderBusinessesCount = $senderQuery->count();

        return [
            'user' => new UserResource($user),
            'businesses' => $senderBusinesses,
            'businesses_count' => $senderBusinessesCount,
        ];
    }

    /**
     * Get the user info.
     * @return array
     */
    public function show() :array
    {

        return [
            'user' => [
                'id' => Auth::user()->id,
                'first_name' => Auth::user()->first_name,
                'last_name' => Auth::user()->last_name,
                'email' => Auth::user()->email,
                'nickname' => Auth::user()->nickname,
                'address' => Auth::user()->address,
                'country_id' => Auth::user()->country_id,
                'city_id' => Auth::user()->city_id,
                'area_id' => Auth::user()->area_id,
                'mobile' => Auth::user()->mobile,
                'biography' => Auth::user()->biography,
                'profile_photo_path' => Auth::user()->profile_photo_path,
                'bg_photo_path' => Auth::user()->bg_photo_path,
                'rate' => Auth::user()->rate,
                'point' => Auth::user()->point,
            ]
        ];
    }

    /**
     * Get verification of the user
     * @return array
     */
    public function checkVerification() :array
    {
        return [
            'verify_email' => !empty(Auth::user()->email_verified_at),
            'verify_access' => !empty(Auth::user()->verified_at),
            'user' => new UserResource(Auth::user()),
            'customer_number' => Auth::user()->customer_number
        ];
    }

    /**
     * Get the dashboard info
     *
     * @return array The seller object
     */
    public function getDashboardInfo() :array
    {
        $businessCount = Business::query()
                ->where('user_id', Auth::user()->id)
                ->count();

        $ticketCount = Ticket::query()
                ->where('user_id', Auth::user()->id)
                ->where('status', Ticket::STATUS_ACTIVE)
                ->count();

        // $messageCount = ChatMessage::query()
        //         ->whereHas('chat', function ($query) {
        //             return $query->where('user_id', Auth::user()->id)
        //                 ->orWhere('target_id', Auth::user()->id);
        //         })
        //         ->where('user_id', '!=', Auth::user()->id)
        //         ->where('created_at', '>', Carbon::now()->subMonth())
        //         ->count();

        return [
            'business_count' => $businessCount,
            'tickets' => $ticketCount,
            // 'messages' => $messageCount,
        ];
    }

     /**
     * Update the user.
     * @param UpdateUserRequest $request
     * @param User $user
     * @return array
     */
    public function update(UpdateUserRequest $request) :array
    {

        $user = Auth::user();

        if (!$this->checkNickname($request->input('nickname'), $user->id)) {
            return [
                'status' => 0,
                'message' => __('site.The Nickname is invalid')
            ];
        }

        $update = $user->update([
            'first_name'            => $request->input('first_name'),
            'last_name'             => $request->input('last_name'),
            'nickname'              => $request->input('nickname'),
            'address'               => $request->input('address'),
            'country_id'            => $request->input('country_id'),
            'city_id'               => $request->input('city_id'),
            'mobile'                => $request->input('mobile'),
            'biography'             => $request->input('biography'),
            'profile_photo_path'    => $request->input('profile_photo_path', config('image.default-profile-image')),
            'bg_photo_path'         => $request->input('bg_photo_path', config('image.default-background-image')),
        ]);

        if ($update) {

            // $this->service->sendNotification(
            //     config('telegram.chat_id'),
            //     'ویرایش اطلاعات برای کاربر' . PHP_EOL .
            //     'first_name ' . $request->input('first_name') . PHP_EOL .
            //     'last_name ' . $request->input('last_name'),
            //     'nickname ' . $request->input('nickname'),
            //     'mobile ' . $request->input('mobile'),
            //     'national_code ' . $request->input('national_code'),
            //     'biography ' . $request->input('biography'),
            //     'profile_photo_path ' . $request->input('profile_photo_path', config('image.default-profile-image')),
            //     'bg_photo_path ' . $request->input('bg_photo_path', config('image.default-background-image')),
            // );

            return [
                'status' => 1,
                'message' => __('site.The data has been updated'),
                'user' => new UserResource($user)
            ];
        }

        throw new \Exception();
    }

    /**
     * Change the user password.
     * @param ChangePasswordRequest $request
     * @param User $user
     * @return array
     */
    public function changePassword(ChangePasswordRequest $request) :array
    {
        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return [
                'status' => 0,
                'message' => __('site.Current password is incorrect')
            ];
        }

        // Update password
        $update = $user->update([
            'password' => Hash::make($request->input('password'))
        ]);

        NotificationService::create([
            'title' => __('site.password_changed_title'),
            'content' => __('site.password_changed_content'),
            'id' => $user->id,
            'type' => NotificationService::PROFILE,
        ], $user);

        if ($update) {
            return [
                'status' => 1,
                'message' => __('site.Password has been changed successfully')
            ];
        }

        throw new \Exception();
    }
}