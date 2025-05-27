<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Tool;
use App\Models\ToolMedia;
use App\Http\Resources\Api\ToolResource;
use App\Http\Resources\Api\ToolDetailResource;

class ToolController extends BaseController
{
    /**
     * Retrieves tool data and sends a response.
     *
     * @param  Request  $request  The request object.
     * @throws \Exception  If an error occurs while retrieving the tool data.
     * @return \Illuminate\Http\Response  The response containing the tool data.
     */
    public function tool(Request $request){
        $returnData = (object)[];
        try{
            $toolData = Tool::orderBy('sequence','asc')->get();

            $returnData = ToolResource::collection($toolData->toArray());
            return $this->sendResponse($returnData, 'Tool Data');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }

    /**
     * Retrieves the details of a tool.
     *
     * @param Request $request the HTTP request object
     * @param int $id the ID of the tool
     * @throws \Exception if an error occurs
     * @return \Illuminate\Http\Response the response object
     */
    public function toolDetails(Request $request, $id){
        $returnData = (object)[];
        try{
            $toolMediaData = ToolMedia::with('tool')->where('tool_id',$id)->first();
            if($toolMediaData){
                $returnData =  new ToolDetailResource($toolMediaData);
            }else{
                $returnData = (object)[];
            }
            return $this->sendResponse($returnData, 'Audio Tool Data');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }

}
