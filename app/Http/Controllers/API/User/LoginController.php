<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Api\User\LoginRequest;
use App\Http\Resources\Api\LoginApiResource;
use App\Models\User;
use Auth;
use App\Models\PersonalAccessToken;

class LoginController extends BaseController
{
    public function login(LoginRequest $request){

        $returnData = (object)[];

        $user= User::where('email', $request->email)->first();
        app()->setLocale($request->has('lang') ? $request->lang : ($user ? $user->lang : 'en'));

        if (!$user || !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {

            return response([
                    'success'=>false,
                    'data' => $returnData,
                    'message' => trans('messages.credentials_mismatch'),
                ], 400);
        }elseif($user && $user->status != 1){
            // The User status Not Active 
            return $this->sendError($returnData, trans('messages.inactive_account') ,401);
        }else{
    
            if(!Auth::attempt($request->only(['email', 'password']))){
                return $this->sendError($returnData, trans('messages.email_not_match'),400);
            }
            PersonalAccessToken::where('name', $request->device_token)->delete();
            $user['token'] = $user->createToken($request->device_token, ['user-api'])->plainTextToken;
            $returnData = new LoginApiResource($user);

            return $this->sendResponse($returnData, trans('messages.login_success_message'));
        }
    }

    public function logout(Request $request){
        app()->setLocale($request->has('lang') ? $request->lang : 'en');

        $returnData = (object)[];
        $loginUserData  = $request->user();
        if (!empty($loginUserData)) {
            $request->user()->currentAccessToken()->delete();
            return $this->sendResponse($returnData, trans('messages.logout_success_message'));
        } else {
            return $this->sendError($returnData, trans('messages.unauthorized'), 401);
        }
    }
}
