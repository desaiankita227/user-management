<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Api\User\ForgotPasswordRequest;
use Str;
use DB;
use Carbon\Carbon;
use Mail;
use App\Models\User;
use App\Commonhelper;

class ForgotPasswordController extends BaseController
{
    function forgotPassword(ForgotPasswordRequest $request){
        app()->setLocale($request->has('lang') ? $request->lang : 'en');
        $returnData = (object)[];
        $email      = $request->email;
        $user       = new User;

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $user           = $user->checkEmail($email);
            $errorMessage   = trans('messages.account_not_found_message');
        }

        if (!$user) {
            return $this->sendError($returnData, $errorMessage);
        }elseif ($user->status != 1) {
            return $this->sendError($returnData, trans('messages.inactive_account'),403);
        } else {
            try {
                $token = Str::random(64);
                DB::table('password_resets')->insert([
                    'email'         => $request->email,
                    'token'         => $token,
                    'created_at'    => Carbon::now()
                ]);
                Mail::send('emails.user.reset_api', ['token' => $token, 'lang' => $user->lang], function ($message) use ($request) {
                    $message->to($request->email);
                    $message->subject('Forgot Password');
                });
                return $this->sendResponse($returnData, trans('messages.password_reset_message'));
                
            } catch (\Exception $exception) {
                return $this->sendError($returnData, $exception->getMessage(),500);
            }
        }

    }
}
