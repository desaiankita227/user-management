<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Resources\Api\EpisodeDetailsResource;
use App\Models\WeeklyEpisode;

class EpisodeDetailController extends BaseController
{
    public function episodeDetails(Request $request,$id){
        $returnData = (object)[];
        try{
            $weeklyEpisodeData = WeeklyEpisode::with('episodeTest.test', 'episodeTool.tool')->where('id',$id)->first();

            if($weeklyEpisodeData){
                $returnData = new EpisodeDetailsResource($weeklyEpisodeData);
            }else{
                $returnData = (object)[];
            }

            return $this->sendResponse($returnData, 'Episode Details data');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }
}
