<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Resources\Api\UserNotifictionResource;
use App\Models\Notification;
use Auth;

class UserNotificationController extends BaseController
{
    /**
     * Retrieves the notifications for a user.
     *
     * @param Request $request The request object containing the user's data.
     * @return Response The response object containing the user's notifications data.
     */
    public function getNotifications(Request $request)
    {
        $returnData = (object)[];
        $userData   = $request->user();
        app()->setLocale($request->has('lang') ? $request->lang : 'en');

        if ($userData && $userData->status == 1) {
            $notifications = Auth::user()->notifications;

            $returnData = UserNotifictionResource::collection($notifications);
        } else {
            $response = [
                'success' => false,
                'data'    => $returnData,
                'message' => trans('messages.unauthorized')
            ];
            return response()->json($response, 401);
        }
        return $this->sendResponse($returnData, 'User notifications data');
    }

    /**
     * Delete a notification.
     *
     * @param int $id The ID of the notification to delete.
     * @throws \Exception If an error occurs while deleting the notification.
     * @return mixed The response data.
     */
    public function deleteNotifications($id){
        try {
            $returnData = (object)[];
            $UserId = Auth::user()->id;

            if(isset($UserId) && $UserId != "" ){
                $notificationId = $id;
                Notification::where('id', $notificationId)->where('user_id', $UserId)->delete();
                if(Auth::user()->lang == "ko"){
                    return $this->sendResponse($returnData, '알람이 삭제 되었습니다.');
                }else{
                    return $this->sendResponse($returnData, 'Notification deleted.');
                }
            }else{
                return $this->sendError($returnData, 'Your account not found.',422);
            }
        } catch (\Exception $e) {
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }

    public function clearAllNotifications(){
        try {
            $returnData = (object)[];
            $UserId = Auth::user()->id;

            $notifications = Notification::where('user_id', $UserId)->delete();
            if(Auth::user()->lang == "ko"){
                return $this->sendResponse($returnData, '알람이 삭제 되었습니다.');
            }else{
                return $this->sendResponse($returnData, 'Notification deleted.');
            }

        } catch (\Exception $e) {
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }
}
