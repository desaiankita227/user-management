<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\TestQuestion;
use App\Models\Test;
use App\Models\UserTest;
use Auth;
use Hash;
use App\Http\Resources\Api\TestQuestionResource;
use App\Http\Resources\Api\UserTestResource;

class TestQuestionController extends BaseController
{
    public function testDetail(Request $request,$id){
       $returnData = (object)[];
        try{
            $test = Test::with('testquestion','episodeTest.weeklyEpisode:id,title')->where('id',$id)->first();

            $returnData = new TestQuestionResource($test->toArray());
            return $this->sendResponse($returnData, 'Test Details data');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        } 
    } 

}
