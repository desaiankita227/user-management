<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Resources\Api\EpisodeDetailsResource;
use App\Http\Resources\Api\UserLockedBucketListResource;
use App\Http\Resources\Api\WeeklyEpisodeDetailsResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserBucketList;
use App\Models\UserBucketListStep;
use App\Models\UserEpisodeAccessSystem;
use App\Models\UserEpisodeToolTestAccessSystem;
use App\Models\UserTest;
use App\Models\WeeklyEpisode;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HomeController extends BaseController
{
    /**
     * Retrieves the home data.
     *
     * @param Request $request The request object.
     * @throws \Exception If an error occurs while retrieving the data.
     * @return \Illuminate\Http\Response The response containing the tool data.
     */
    public function Home(Request $request){
        $returnData = (object)[];
        try{
            // logged in user id
            $userId = Auth::id();
            $user = User::select('id')->with('userEpisodeAccess')->where('id', $userId)->where('status', 1)->first()->toArray();
            // Episode which user can access
            $userEpisodeAccess = $user['user_episode_access'];

            // Find the last completed weekly episode for the user
            $lastCompletedEpisode = collect($userEpisodeAccess)->where('is_weekly_episode_completed', 1)->last();

            //----------------- Start Logic for Current week of the user -----------------//
            $currentWeekDetails = $existCurrentWeek = UserEpisodeAccessSystem::where('user_id', $userId)->where('completed_at', null)->where('is_weekly_episode_locked', 0)->where('is_weekly_episode_completed',0)->first();

            // If the current week is incomplete from the last successfully finished weekly episode, the last completed episode should apply for the current week
            if(!$existCurrentWeek){
                $currentWeekDetails = $lastCompletedEpisode;
            } else{
                $currentWeekDetails = $existCurrentWeek->toArray();
            }

            // Weekly episode
            $currentWeek = WeeklyEpisode::with('episodeTest.test', 'episodeTool.tool')->where('id',$currentWeekDetails['weekly_episode_id'])->first();

            // Create the resource instance
            $currentWeekResource = new EpisodeDetailsResource($currentWeek);
            
            //----------------- END Logic for Current week of the user -----------------//

            //----------------- Start Logic of Latest Bucket -----------------//
            //check DooRooWa bucket is locked
            $isDooRooWaBucketLocked = 1;
            //Find weekly episode 3
            $thirdEpisode = WeeklyEpisode::where('title', 'Week 3')->first();
             if($thirdEpisode){
                // Loop through the array to find the matching entry
                 $thirdEpisodeID =  $thirdEpisode->id;
                 foreach ($userEpisodeAccess as $episodeAccess) {
                    if ($episodeAccess['weekly_episode_id'] == $thirdEpisodeID) {
                        if($episodeAccess['is_weekly_episode_locked'] == 0){
                            $isDooRooWaBucketLocked = 0;
                        }
                        break; // Stop the loop once a match is found
                    }
                 }
             }
            $today = Carbon::today();

            //Query to retrieve the nearest due date task
            $deadlineBucketStep = UserBucketListStep::where('user_id',$userId)->where('is_completed', 0) 
                ->where('due_date', '>=', $today) // Filter tasks with due date greater than or equal to today
                ->orderBy('due_date', 'asc') // Order tasks by due date in ascending order
                ->first(); // Retrieve the first task (nearest due date)

            $proximateDeadlineBucketList = [];
            $proximateDeadlineBucketList['is_locked'] = $isDooRooWaBucketLocked;
            $bucketResource = (object) $proximateDeadlineBucketList;
            if($deadlineBucketStep){
                $proximateDeadlineBucketList =  UserBucketList::with(['steps' => function ($query) {
                                                    $query->orderBy('step', 'DESC');
                                                }])
                                                ->where('user_id',$userId)->where('id', $deadlineBucketStep->user_bucket_list_id)->first();
                
                if($proximateDeadlineBucketList) {
                    $proximateDeadlineBucketList['is_locked'] = $isDooRooWaBucketLocked;
                    $bucketResource = ($proximateDeadlineBucketList && !empty($proximateDeadlineBucketList)) ? new UserLockedBucketListResource($proximateDeadlineBucketList) : (object) $proximateDeadlineBucketList;
                }
            }

            //----------------- END Logic of Latest Bucket -----------------//

            //----------------- Start Logic of Garden Image -----------------//

            $gardenImage = 'https://d36938uz3zn5aj.cloudfront.net/episode/base-garden.png';
            if($lastCompletedEpisode){
                $gardenImage = WeeklyEpisode::find($lastCompletedEpisode['weekly_episode_id'])->garden_image;
                if($gardenImage){
                    $gardenImage = env('CLOUD_FRONT_URL').'/episode/'.$lastCompletedEpisode['weekly_episode_id'].'/'.$gardenImage;
                }
            }
            //----------------- END Logic of Garden Image -----------------//

            //----------------- Start Logic of DooRooWa Button -----------------//

            // Default button is locked
            $isDooRooWaButtonLocked = 1;

            //Find weekly episode 5
            $fifthEpisode = WeeklyEpisode::where('title', 'Week 5')->first();
            if($fifthEpisode){
                // Loop through the array to find the matching entry
                $fifthEpisodeID =  $fifthEpisode->id;
                foreach ($userEpisodeAccess as $episodeAccess) {
                    if ($episodeAccess['weekly_episode_id'] == $fifthEpisodeID) {
                        $isDooRooWaButtonLocked = $episodeAccess['is_weekly_episode_locked'];
                        break; // Stop the loop once a match is found
                    }
                }
            }

            if($request->input('lang') == 'ko'){
                $firstVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/dooroowa-button/ko/dooroowa-button-1st-video.mp4';
                $secondVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/dooroowa-button/ko/dooroowa-button-2nd-video.mp4';
            }else{
                $firstVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/dooroowa-button/en/dooroowa-button-1st-video.mp4';
                $secondVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/dooroowa-button/en/dooroowa-button-2nd-video.mp4';
            }

            $dooRooWaButton = [
                'is_locked' => $isDooRooWaButtonLocked, //(NOTE: 0 = not locked, 1 = locked)
                'first_video_url' => $firstVideoURL,
                'second_video_url' => $secondVideoURL
            ];

            //----------------- END Logic of DooRooWa Button -----------------//

            //----------------- Start Logic of Intro Video  -----------------//
            if($request->input('lang') == 'ko'){
                $introVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/intro/korean_intro.mp4';
            }else{
                $introVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/intro/english_intro.mp4';
            }
            //----------------- END Logic of Intro Video  -----------------//

            //----------------- Start Logic of All weeks  -----------------//
            $weeklyEpisodeData = WeeklyEpisode::get();

            $weeks = WeeklyEpisodeDetailsResource::collection($weeklyEpisodeData);

            //----------------- END Logic of All weeks  -----------------//

            $returnData = [
                'current_week' => $currentWeekResource,
                'proximate_deadline_bucket' =>  $bucketResource,
                'dooroowa_button' => $dooRooWaButton,
                'intro_video_url' => $introVideoURL,
                'garden_image' => !empty($gardenImage) ? $gardenImage : '',
                "is_subscribed" => 1, //(NOTE: 0 = not subscribed, 1 = subscribed)
                "weeks" => $weeks
            ];
    
            return $this->sendResponse($returnData, 'Home Data');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }

    // This method is used for new version of V1 (url:/v1/1/home)
    public function homeV1(Request $request){
        $returnData = (object)[];
        try{
            // logged in user id
            $userId = Auth::id();
            $user = User::select('id')->with('userEpisodeAccess')->where('id', $userId)->where('status', 1)->first()->toArray();
            if($user){
                // update user language
                $lang = $request->has('lang') ? $request->lang : Auth::user()->lang;
                User::where('id', $userId)->update(['lang' =>  $lang]);
            }

            // Episode which user can access
            $userEpisodeAccess = $user['user_episode_access'];

            // Find the last completed weekly episode for the user
            $lastCompletedEpisode = collect($userEpisodeAccess)->where('is_weekly_episode_completed', 1)->last();

            //----------------- Start Logic for Current week of the user -----------------//
            $currentWeekDetails = $existCurrentWeek = UserEpisodeAccessSystem::where('user_id', $userId)->where('completed_at', null)->where('is_weekly_episode_locked', 0)->where('is_weekly_episode_completed',0)->first();

            // If the current week is incomplete from the last successfully finished weekly episode, the last completed episode should apply for the current week
            if(!$existCurrentWeek){
                $currentWeekDetails = $lastCompletedEpisode;
            } else{
                $currentWeekDetails = $existCurrentWeek->toArray();
            }

            // Weekly episode
            $currentWeek = WeeklyEpisode::with('episodeTest.test', 'episodeTool.tool')->where('id',$currentWeekDetails['weekly_episode_id'])->first();

            // Create the resource instance
            $currentWeekResource = new EpisodeDetailsResource($currentWeek);
            
            //----------------- END Logic for Current week of the user -----------------//

            //----------------- Start Logic of Latest Bucket -----------------//
            //check DooRooWa bucket is locked
            $isDooRooWaBucketLocked = 1;
            //Find weekly episode 3
            $thirdEpisode = WeeklyEpisode::where('title', 'Week 3')->first();
             if($thirdEpisode){
                // Loop through the array to find the matching entry
                 $thirdEpisodeID =  $thirdEpisode->id;
                 foreach ($userEpisodeAccess as $episodeAccess) {
                    if ($episodeAccess['weekly_episode_id'] == $thirdEpisodeID) {
                        if($episodeAccess['is_weekly_episode_locked'] == 0){
                            $isDooRooWaBucketLocked = 0;
                        }
                        break; // Stop the loop once a match is found
                    }
                 }
             }
            $today = Carbon::today();

            //Query to retrieve the nearest due date task
            $deadlineBucketSteps = UserBucketListStep::where('user_id',$userId)
                ->where('step', 5)
                ->where('is_completed', 0) 
                ->where('due_date', '>=', $today) // Filter tasks with due date greater than or equal to today
                ->orderBy('due_date', 'asc') // Order tasks by due date in ascending order
                ->take(2) // Limit the number of tasks to 2
                ->get(); // Retrieve all tasks

            $proximateDeadlineBucketList = [];
            $proximateDeadlineBucketList['is_locked'] = $isDooRooWaBucketLocked;
            $bucketResource[] = $proximateDeadlineBucketList;
            if (!$deadlineBucketSteps->isEmpty()) {
                $bucketResource = [];
                foreach ($deadlineBucketSteps as $deadlineBucketStep){
                    $proximateDeadlineBucketList =  UserBucketList::with(['steps' => function ($query) {
                                                        $query->orderBy('step', 'DESC');
                                                    }])
                                                    ->where('user_id',$userId)->where('id', $deadlineBucketStep['user_bucket_list_id'])->first();
                    
                    if($proximateDeadlineBucketList) {
                        $proximateDeadlineBucketList['is_locked'] = $isDooRooWaBucketLocked;
                        $bucketResource[] = ($proximateDeadlineBucketList && !empty($proximateDeadlineBucketList)) ? new UserLockedBucketListResource($proximateDeadlineBucketList) : (object) $proximateDeadlineBucketList;
                    }
                }
            }

            //----------------- END Logic of Latest Bucket -----------------//

            //----------------- Start Logic of Garden Image -----------------//

            $gardenImage = 'https://d36938uz3zn5aj.cloudfront.net/episode/base-garden.png';
            if($lastCompletedEpisode){
                $gardenImage = WeeklyEpisode::find($lastCompletedEpisode['weekly_episode_id'])->garden_image;
                if($gardenImage){
                    $gardenImage = env('CLOUD_FRONT_URL').'/episode/'.$lastCompletedEpisode['weekly_episode_id'].'/'.$gardenImage;
                }
            }
            //----------------- END Logic of Garden Image -----------------//

            //----------------- Start Logic of DooRooWa Button -----------------//

            // Default button is locked
            $isDooRooWaButtonLocked = 1;

            //Find weekly episode 5
            $fifthEpisode = WeeklyEpisode::where('title', 'Week 5')->first();
            if($fifthEpisode){
                // Loop through the array to find the matching entry
                $fifthEpisodeID =  $fifthEpisode->id;
                foreach ($userEpisodeAccess as $episodeAccess) {
                    if ($episodeAccess['weekly_episode_id'] == $fifthEpisodeID) {
                        $isDooRooWaButtonLocked = $episodeAccess['is_weekly_episode_locked'];
                        break; // Stop the loop once a match is found
                    }
                }
            }

            if($request->input('lang') == 'ko'){
                $firstVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/dooroowa-button/ko/dooroowa-button-1st-video.mp4';
                $secondVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/dooroowa-button/ko/dooroowa-button-2nd-video.mp4';
            }else{
                $firstVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/dooroowa-button/en/dooroowa-button-1st-video.mp4';
                $secondVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/dooroowa-button/en/dooroowa-button-2nd-video.mp4';
            }

            $dooRooWaButton = [
                'is_locked' => $isDooRooWaButtonLocked, //(NOTE: 0 = not locked, 1 = locked)
                'first_video_url' => $firstVideoURL,
                'second_video_url' => $secondVideoURL
            ];

            //----------------- END Logic of DooRooWa Button -----------------//

            //----------------- Start Logic of Intro Video  -----------------//
            if($request->input('lang') == 'ko'){
                $introVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/intro/korean_intro.mp4';
            }else{
                $introVideoURL = 'https://d36938uz3zn5aj.cloudfront.net/intro/english_intro.mp4';
            }
            //----------------- END Logic of Intro Video  -----------------//

            //----------------- Start Logic of All weeks  -----------------//
            $weeklyEpisodeData = WeeklyEpisode::get();

            $weeks = WeeklyEpisodeDetailsResource::collection($weeklyEpisodeData);

            $dataForSecondVideo = WeeklyEpisode::where('title', 'Week 12')->first();

            if($dataForSecondVideo){
                $checkLocked = UserEpisodeAccessSystem::where('user_id', $userId)->where('weekly_episode_id', $dataForSecondVideo->id)->first();
                $is_locked = ($checkLocked != null ? $checkLocked->is_weekly_episode_locked : 1);
                $is_completed = ($checkLocked != null ? $checkLocked->is_weekly_episode_completed : 0);
                $is_test_completed = 0;
    
                $checkTestCompleted = UserEpisodeToolTestAccessSystem::where('user_id', $userId)->where('weekly_episode_id', $dataForSecondVideo->id)->pluck('test_id')->toArray();
                $count = UserTest::where('user_id', $userId)->where('weekly_episode_id', $dataForSecondVideo->id)->whereIn('test_id', $checkTestCompleted)->count();

                // Check week has no test
                $countOfWeeklyTest = WeeklyEpisode::with('episodeTest')->where('id', $dataForSecondVideo->id)->first();

                if($countOfWeeklyTest->episodeTest->count() == 0 || $count > 0){
                    $is_test_completed = 1;
                }
                if($request->input('lang') == 'ko'){
                    $thumbnail = ($dataForSecondVideo->k_second_video_thumbnail)?env('CLOUD_FRONT_URL').'/episode/'.$dataForSecondVideo->id . '/thumbnail/' . $dataForSecondVideo->k_second_video_thumbnail :"";
                }else{
                    $thumbnail = ($dataForSecondVideo->second_video_thumbnail)?env('CLOUD_FRONT_URL').'/episode/'.$dataForSecondVideo->id . '/thumbnail/' . $dataForSecondVideo->second_video_thumbnail :"";
                }   
                $extraData = [
                    "id" => $dataForSecondVideo->id,
                    "title" => "",
                    "image" => $thumbnail,
                    "is_locked" => $is_locked,
                    "is_completed" => $is_completed,
                    "is_test_completed" => $is_test_completed
                ];
            }

            // Append the extra data to the response
            $weeks = array_merge($weeks->toArray($request), [$extraData]);
            //----------------- END Logic of All weeks  -----------------//

            $returnData = [
                'current_week' => $currentWeekResource,
                'proximate_deadline_bucket' =>  $bucketResource,
                'dooroowa_button' => $dooRooWaButton,
                'intro_video_url' => $introVideoURL,
                'garden_image' => !empty($gardenImage) ? $gardenImage : '',
                "is_subscribed" => 1, //(NOTE: 0 = not subscribed, 1 = subscribed)
                "weeks" => $weeks
            ];
    
            return $this->sendResponse($returnData, 'Home Data');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }
}
