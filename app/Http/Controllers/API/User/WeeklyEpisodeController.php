<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Resources\Api\WeeklyEpisodeDetailsResource;
use App\Models\UserEpisodeAccessSystem;
use App\Models\UserEpisodeToolTestAccessSystem;
use App\Models\UserTest;
use App\Models\WeeklyEpisode;
use Illuminate\Support\Facades\Auth;

class WeeklyEpisodeController extends BaseController
{
    public function weeklyEpisodes(Request $request){
        $returnData = (object)[];
        try{
            $userId = Auth::id();

            $weeklyEpisodeData = WeeklyEpisode::get();

            $returnData = WeeklyEpisodeDetailsResource::collection($weeklyEpisodeData);

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
            $returnData = array_merge($returnData->toArray($request), [$extraData]);

            return $this->sendResponse($returnData, 'Weeks episode data');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage());
        }
    }

    public function weekCompletion(Request $request,$id){
        try{
            $userId = Auth::id();

            $weeklyEpisode = UserEpisodeAccessSystem::where('user_id',$userId)->where('weekly_episode_id', $id)->first();

            if($weeklyEpisode){
                $weeklyEpisode->update(['is_weekly_episode_completed' => 1, 'completed_at' => now()]);
                if($request->input('lang') == 'ko'){
                    return response(['success'=>true, 'message'=>'이번주 에피소드를 성공적으로 마치셨습니다!' ]);
                }else{
                    return response(['success'=>true, 'message'=>'Your Weekly Episode has been completed successfully!' ]);
                }
            }
            if($request->input('lang') == 'ko'){
                return response(['success'=>false, 'message' => '이번주 에피소드를 찾을 수 없습니다.' ], 404);
            }else{
                return response(['success'=>false, 'message' => 'Your week episode not found.' ], 404);
            }
        }catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }

    }
}
