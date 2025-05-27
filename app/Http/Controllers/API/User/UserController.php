<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Api\User\ProfileUpdateApiRequest;
use App\Http\Requests\Api\User\ChangePasswordApiRequest;
use App\Http\Resources\Api\UserDetailsApiResource;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\LoginApiResource;
use App\Models\PersonalAccessToken;

class UserController extends BaseController
{
    public function userDetails(Request $request)
    {
        $returnData = (object)[];
        $userData   = $request->user();
        app()->setLocale($request->has('lang') ? $request->lang : 'en');

        if ($userData && $userData->status == 1) {
            $returnData = new UserDetailsApiResource($userData);
        } else {
            $response = [
                'success' => false,
                'data'    => $returnData,
                'message' => trans('messages.unauthorized')
            ];
            return response()->json($response, 401);
        }
        return $this->sendResponse($returnData, 'User data');
    }

    public function updateUser(ProfileUpdateApiRequest $request)
    {
        $returnData = (object)[];
        $user       = $request->user();
        $userId     = $user->id;
        $locale     = $user ? $user->lang : ($request->has('lang') ? $request->lang : 'en');
        app()->setLocale($locale);
    
        if($user->status != 1){
            // The User status Not Active 

            return $this->sendError($returnData, trans('messages.inactive_account'),401);
        }
        try {
            $userRequestData = array(
                "full_name"     => $request->name,
                "email"         => $request->email,
            );
            User::where("id", $userId)->update($userRequestData);

            $loginUserData  = User::getUserDataById($request->user()->id);
            $returnData     = new UserDetailsApiResource($loginUserData);
            if($locale == 'ko'){
                return $this->sendResponse($returnData, "프로필이 성공적으로 업데이트 되었습니다.");
            }else{
                return $this->sendResponse($returnData, "Profile have been updated successfully.");
            }
        } catch (\Exception $exception) {
            return $this->sendError($returnData, $exception->getMessage(),500);
        }
    }

    public function changePassword(ChangePasswordApiRequest $request)
    {
        $returnData = (object)[];
        $userData = $request->user();
        
        app()->setLocale($request->has('lang') ? $request->lang : ($userData ? $userData->lang : 'en'));

        if (!(Hash::check($request->current_password, $userData->password))) {
            // The passwords matches
            return $this->sendError($returnData, trans('messages.invalid_current_password'));
        }

        if($userData->status != 1){
            // The User status Not Active 
            return $this->sendError($returnData, trans('messages.inactive_account'),401);
        }

        try {
            $data['password'] = \Hash::make($request->new_password);
            User::where('id', $userData->id)->update($data);
            if($userData->lang == 'ko'){
                return $this->sendResponse($returnData, "비밀번호가 성공적으로 업데이트 되었습니다.");
            }else{
                return $this->sendResponse($returnData, "Password successfully updated.");
            }
        } catch (\Exception $e) {
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }

    public function deleteUser(Request $request){
        try {
            $returnData = (object)[];
            $UserId = Auth::user()->id;

            $lang = ($request->has('lang')) ? $request->lang : Auth::user()->lang;
            if(isset($UserId) && $UserId != ""){

                User::find($UserId)->delete();
                if($lang == 'ko'){
                    return $this->sendResponse($returnData, '당신의 계정이 성공적으로 삭제되었습니다.');
                }else{
                    return $this->sendResponse($returnData, 'Your account successfully deleted.');
                }
            }else{
                if($lang == 'ko'){
                    return $this->sendError($returnData, '계정을 찾을 수 없습니다.',422);
                }else{
                    return $this->sendError($returnData, 'Your account not found.',422);
                }
            }
        } catch (\Exception $e) {
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }

    /**
     * Change the language.
     *
     * @param Request $request The HTTP request object.
     * @throws Some_Exception_Class Description of the exception.
     * @return void
     */
    public function changeLanguage(Request $request){
        $returnData = (object)[];
        $userId     = $request->user()->id;
        try {
            User::where('id', $userId)->update(['lang' => $request->lang]);

            $loginUserData  = User::getUserDataById($userId);
            $returnData     = new UserDetailsApiResource($loginUserData);
            if($request->lang == 'ko'){
                return $this->sendResponse($returnData, "언어가 성공적으로 업데이트 되었습니다.");
            }else{
                return $this->sendResponse($returnData, "Language have been updated successfully");
            }
        } catch (\Exception $exception) {
            return $this->sendError($returnData, $exception->getMessage(),500);
        }
    }

    public function refreshAccessToken(Request $request)
    {
        $returnData = (object)[];
        $user = $request->user();
        $current_token = $user->currentAccessToken();
        
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'device_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $firstErrorMessage = $errors->first();

                return response()->json([
                    'success' => false,
                    'message' => $firstErrorMessage,
                    'data' => $returnData,
                ], 422);
            }

            // Find the token by device token name
            $findDeviceToken = PersonalAccessToken::where('token', $current_token->token)->first();

            if (!$findDeviceToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session has expired. Please log in again.',
                    'data' => $returnData,
                ], 401);
            }

            // Retrieve the user associated with the token
            $userData = User::find($findDeviceToken->tokenable_id);

            // Delete the old token
            $findDeviceToken->delete();

            // Generate a new token
            $newToken = $userData->createToken($request->device_token, ['user-api'])->plainTextToken;

            // Add the new token to the user object for the response
            $userData['token'] = $newToken;

            // Prepare the response data
            $returnData = new LoginApiResource($userData);

            return $this->sendResponse($returnData, 'Access token refreshed successfully.');

        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            return $this->sendError($returnData, $exception->getMessage(), 500);
        }
    }
}
