<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Api\User\RegisterRequest;
use App\Models\User;
use App\Http\Resources\Api\RegisterApiResource;
use App\Models\UserEpisodeAccessSystem;
use App\Models\UserEpisodeToolTestAccessSystem;
use App\Models\WeeklyEpisode;
use Auth;
use Mail;

class RegisterController extends BaseController
{
    function register(RegisterRequest $request){
        $returnData = (object)[];
        $userData = [
            "full_name"=>$request->name,
            "email"=>$request->email,
            "password"=>bcrypt($request->password),
            "email_verified_at" => date("Y-m-d H:i:s"),
            "lang" => $request->has('lang') ? $request->lang : 'en',
        ];
    
        try{
            $userData = User::create($userData);
            $userToken = $userData->createToken($request->device_token, ['user-api'])->plainTextToken;
            
            $get_user = User::where('email',$userData->email)->first();
            $get_user['token'] = $userToken;
            $returnData = new RegisterApiResource($get_user);

            Mail::send('emails.user.welcome_api', ['name' => $get_user['full_name']], function ($message) use ($request) {
                        $message->to($request->email);
                        $message->subject('Welcome');
                    });

            $weeklyEpisode = WeeklyEpisode::with('episodeTest', 'episodeTool')->get();

            if($weeklyEpisode){
                foreach($weeklyEpisode as $key => $weekly){
                    $weeklyAccess = new UserEpisodeAccessSystem();
                    $weeklyAccess->user_id = $get_user->id;
                    $weeklyAccess->weekly_episode_id = $weekly->id;
                    $weeklyAccess->is_weekly_episode_locked = ($key == 0 ? 0 : 1);
                    $weeklyAccess->is_weekly_episode_completed = 0;
                    $weeklyAccess->save();

                    $weeklyEpisodeTool = $weekly->episodeTool;
                    foreach($weeklyEpisodeTool as $episode){
                        $episodeAccess = new UserEpisodeToolTestAccessSystem();
                        $episodeAccess->user_episode_access_system_id = $weeklyAccess->id;
                        $episodeAccess->user_id = $get_user->id;
                        $episodeAccess->weekly_episode_id = $weekly->id;
                        $episodeAccess->weekly_episode_tool_id = $episode->id;
                        $episodeAccess->tool_id = $episode->tool_id;
                        $episodeAccess->is_weekly_tool_locked = ($key == 0 ? 0 : 1);
                        $episodeAccess->save();
                    }

                    $weeklyEpisodeTest = $weekly->episodeTest;
                    foreach($weeklyEpisodeTest as $episode){
                        $episodeAccess = new UserEpisodeToolTestAccessSystem();
                        $episodeAccess->user_episode_access_system_id = $weeklyAccess->id;
                        $episodeAccess->user_id = $get_user->id;
                        $episodeAccess->weekly_episode_id = $weekly->id;
                        $episodeAccess->weekly_episode_test_id = $episode->id;
                        $episodeAccess->test_id = $episode->test_id;
                        $episodeAccess->is_weekly_test_locked = ($key == 0 ? 0 : 1);
                        $episodeAccess->save();
                    }
                }
            }
            app()->setLocale($request->lang);
            return $this->sendResponse($returnData,  trans('messages.register_success_message'));
        }catch (\Exception $exception) {
            return $this->sendError($returnData, $exception->getMessage(),500);
        }
    }
}
