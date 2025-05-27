<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Http\Resources\Api\TestResource;
use App\Http\Resources\Api\V1\TestResourceV1;
use App\Models\UserEpisodeAccessSystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\UserTest;

class TestController extends BaseController
{
    public function test(Request $request){
        $returnData = (object)[];
        try{
            $testData = Test::with('episodeTest.weeklyEpisode:id,title')->get();

            $returnData = TestResource::collection($testData->toArray());
            
            return $this->sendResponse($returnData, 'Test data');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }

    public function testV1(Request $request){
        $returnData = (object)[];
        try{
            $userId = Auth::id();
            $testData = Test::with('episodeTest.weeklyEpisode:id,title')->get();

            $returnData = TestResourceV1::collection($testData->toArray());
            $response['tests'] = $returnData;
            $result = null;

            // Check week 12 is completed or not
            //$checkWeekCompleted = UserEpisodeAccessSystem::where('user_id', $userId)->whereNotNull('completed_at')->first();
            // Check if the user has any completed records for "Week 1"
            $checkWeekTestCompleted = UserTest::where('user_id', $userId)
            ->whereHas('weeklyEpisodeTest', function ($query) {
                $query->where('title', 'Week 1');
            })
            ->exists();
            $userLang = ($request->has('lang')) ? $request->lang : Auth::user()->lang;

            if($checkWeekTestCompleted){
                // Send URL of route
                $encryptedUserId = Crypt::encryptString($userId);

                $result = route('test-result', ['userId' => $encryptedUserId,'lang' => $userLang]);
            }
            $response['result_url'] = $result;

            return $this->sendResponse($response, 'Test data');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }
}
