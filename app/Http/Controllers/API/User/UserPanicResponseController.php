<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\UserPanicResponse;
use App\Http\Resources\Api\UserPanicResponseResource;
use App\Models\UserPanicResult;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserPanicResponseController extends BaseController
{
    /**
     * Retrieves the panic responses for a user.
     *
     * @param Request $request The HTTP request object.
     * @throws \Exception If an error occurs while retrieving the panic responses.
     * @return mixed The response data.
     */
    public function panicResponses(Request $request){
        Log::info($request->all());
        $userId = Auth::id();

        $returnData = (object)[];
        try{
            $userPanicResponses = UserPanicResponse::with('negativeResults', 'positiveResults')->where('user_id',$userId)->get();
            if($userPanicResponses){
                $returnData = UserPanicResponseResource::collection($userPanicResponses->toArray());
            }else{
                $returnData = (object)[];
            }
            return $this->sendResponse($returnData, 'User Panic Response');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }

    /**
     * Adds a panic response to the database.
     *
     * @param Request $request The HTTP request object.
     * @throws \Exception If an error occurs during the process.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the success status, data, and message.
     */
    public function addPanicResponse(Request $request){
        Log::info($request->all());
        $userLang = ($request->has('lang')) ? $request->lang : Auth::user()->lang;
        try{
            $validator = Validator::make(request()->all(), [
                'cue_title' => 'required',
                'cue_description' => 'required',
                'old_panic_response' => 'required',
                'old_reward' => 'required',
                'negative_results' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            $userPanicResponse = new UserPanicResponse;
            $userPanicResponse->user_id = Auth::id();
            $userPanicResponse->cue_title = $request->has('cue_title') ? $request->cue_title : '';
            $userPanicResponse->cue_description = $request->has('cue_description') ? $request->cue_description : '';
            $userPanicResponse->old_panic_response = $request->has('old_panic_response') ? $request->old_panic_response : '';
            $userPanicResponse->old_reward = $request->has('old_reward') ? $request->old_reward : '';
            $userPanicResponse->save();
    
            $addedNegativeResultArray = [];
            $negativeResultString = $request->has('negative_results') ? $request->negative_results : '';
            $arrayNegativeResult = json_decode($negativeResultString, true);   
    
            if($request->has('negative_results') && count($arrayNegativeResult) > 0){
                foreach($arrayNegativeResult as $key => $value){
                    $negativeResults = new UserPanicResult;
                    $negativeResults->user_id = Auth::id();
                    $negativeResults->user_panic_response_id = $userPanicResponse->id;
                    $negativeResults->result = $value;
                    $negativeResults->response_type = 'negative';
                    $negativeResults->save();

                    $addedNegativeResultArray[] = $value;
                }
            }

            $returnData = (object)[
                'id'                   => $userPanicResponse['id'],
                'cue_title'            => $userPanicResponse['cue_title'] != null ? $userPanicResponse['cue_title'] : '',
                'cue_description'      => $userPanicResponse['cue_description'] != null ? $userPanicResponse['cue_description'] : '',
                'old_panic_response'   => $userPanicResponse['old_panic_response'] != null ? $userPanicResponse['old_panic_response'] : '',
                'old_reward'           => $userPanicResponse['old_reward'] != null ? $userPanicResponse['old_reward'] : '',
                'negative_results'     => !empty($addedNegativeResultArray) ? $addedNegativeResultArray : [],
                'new_reward'           => $userPanicResponse['old_reward'] != null ? $userPanicResponse['old_reward'] : '',
                'new_panic_response'   => '',
                'positive_results'     => [],
                'note'                 => $userPanicResponse['progress_note'] != null ? $userPanicResponse['progress_note'] : '',
            ];
            if($userLang == "ko"){
                return response()->json(['success' => true, 'data' => $returnData, 'message' => '공황 발작 대처 방식이 성공적으로 등록되었습니다.'], 200);
            }else{
                return response()->json(['success' => true, 'data' => $returnData, 'message' => 'Panic Response added successfully'], 200);
            }
        } catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }

    /**
     * Updates the panic response based on the provided request.
     *
     * @param \Illuminate\Http\Request $request The request object.
     * @throws \Exception If an error occurs during the update process.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the result of the update.
     */
    public function updatePanicResponse(Request $request){
        Log::info($request->all());
        $userLang = ($request->has('lang')) ? $request->lang : Auth::user()->lang;

        try{
            $validator = Validator::make(request()->all(), [
                'new_panic_response' => 'required',
                'positive_results' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $userId = Auth::id();

            $userPanicResponse = UserPanicResponse::with('negativeResults', 'positiveResults')->where('user_id',$userId)->where('id',$request->id)->first();

            if($userPanicResponse){
                $userPanicResponse->new_panic_response = $request->has('new_panic_response') ? $request->new_panic_response : '';
                $userPanicResponse->update();
        
                $positiveResultString = $request->has('positive_results') ? $request->positive_results : '';
                $arrayPositiveResult = json_decode($positiveResultString, true);   

                if($request->has('positive_results') && count($arrayPositiveResult) > 0){
                    $userPanicResponse->positiveResults()->delete(); // This will delete all the comments related to the post
                    foreach($arrayPositiveResult as $key => $value){
                        $positiveResults = new UserPanicResult;
                        $positiveResults->user_id = Auth::id();
                        $positiveResults->user_panic_response_id = $userPanicResponse->id;
                        $positiveResults->result = $value;
                        $positiveResults->response_type = 'positive';
                        $positiveResults->save();
                    }
                }

                $userPanicResponse = UserPanicResponse::with('negativeResults', 'positiveResults')->where('user_id',$userId)->where('id',$request->id)->first();

                $negativeResultArray = $userPanicResponse->negativeResults->pluck('result')->toArray();
                $positiveResultArray = $userPanicResponse->positiveResults->pluck('result')->toArray();

                $returnData = (object)[
                    'id'                   => $userPanicResponse['id'],
                    'cue_title'            => $userPanicResponse['cue_title'] != null ? $userPanicResponse['cue_title'] : '',
                    'cue_description'      => $userPanicResponse['cue_description'] != null ? $userPanicResponse['cue_description'] : '',
                    'old_panic_response'   => $userPanicResponse['old_panic_response'] != null ? $userPanicResponse['old_panic_response'] : '',
                    'old_reward'           => $userPanicResponse['old_reward'] != null ? $userPanicResponse['old_reward'] : '',
                    'negative_results'     => !empty($negativeResultArray) ? $negativeResultArray : [],
                    'new_reward'           => $userPanicResponse['old_reward'] != null ? $userPanicResponse['old_reward'] : '',
                    'new_panic_response'   => $userPanicResponse['new_panic_response'] != null ? $userPanicResponse['new_panic_response'] : '',
                    'positive_results'     => !empty($positiveResultArray) ? $positiveResultArray : [],
                    'note'                 => $userPanicResponse['progress_note'] != null ? $userPanicResponse['progress_note'] : '',
                ];
                if($userLang == "ko"){
                    return response()->json(['success' => true, 'data' => $returnData, 'message' => '공황 발작 대처 방식이 성공적으로 업데이트 되었습니다.'], 200);
                }else{
                    return response()->json(['success' => true, 'data' => $returnData, 'message' => 'Panic Response updated successfully'], 200);
                }
            }
            if($userLang == "ko"){
                return response()->json(['success' => false, 'message' => '공황 발작 대처 방식을 찾을 수 없습니다.'], 404);
            }else{
                return response()->json(['success' => false, 'message' => 'Panic Response not found'], 404);
            }
        } catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }
    
    /**
     * Deletes a panic response.
     *
     * @param int $id The ID of the panic response to delete.
     * @throws \Exception If an error occurs while deleting the panic response.
     * @return \Illuminate\Http\Response The response indicating the success or failure of the deletion.
     */
    public function deletePanicResponse($id){
        Log::info("Deleting panic response: ".$id);
        try{
            $userId = Auth::id();

            $userPanicResponse = UserPanicResponse::where('user_id',$userId)->where('id', $id)->first();

            if($userPanicResponse){
                $userPanicResponse->delete();
                if(Auth::user()->lang == "ko"){
                    return response([
                        'success'=>true,
                        'message'=>'공황 발작 대처 방식이 삭제 되었습니다.'
                    ]);
                } else {
                    return response([
                        'success'=>true,
                        'message'=>'Your panic response has been deleted.'
                    ]);
                }
            }

            if(Auth::user()->lang == "ko"){
                return response([
                    'success'=>false,
                    'message' => '당신의 공황 발작 대처 방식을 찾을 수 없습니다.'
                ], 404);
            }else{
                return response([
                    'success'=>false,
                    'message' => 'Your panic response not found.'
                ], 404);
            }
        }catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }

    /**
     * Updates the progress note for a panic response.
     *
     * @param Request $request The request object containing the panic response id and the new note.
     * @throws \Exception If an error occurs while updating the panic response note.
     * @return \Illuminate\Http\Response The response indicating the success or failure of the update operation.
     */
    public function panicResponsesNote(Request $request){
        Log::info($request->all());
        try{
            $lang = $request->has('lang') ? $request->lang : Auth::user()->lang;
            $userId = Auth::id();

            $userPanicResponse = UserPanicResponse::where('user_id',$userId)->where('id', $request->id)->first();

            if($userPanicResponse){
                $userPanicResponse->progress_note = $request->has('note') ? $request->note : '';
                $userPanicResponse->save();
                return response([
                    'success'=>true,
                    'message'=>'Your panic response note has been updated.'
                ]);
            }

            if($lang == "ko"){
                return response([
                    'success'=>false,
                    'message' => '당신의 공황 발작 대처 방식을 찾을 수 없습니다.'
                ], 404);
            }else{
                return response([
                    'success'=>false,
                    'message' => 'Your panic response not found.'
                ], 404);
            }
        }catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }

    /**
     * Adds a panic response to the database. Version 1.1
     *
     * @param Request $request The HTTP request object.
     * @throws \Exception If an error occurs during the process.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the success status, data, and message.
     */
    public function addPanicResponseV1(Request $request){
        Log::info($request->all());
        $lang = $request->has('lang') ? $request->lang : Auth::user()->lang;
        try{
            $validator = Validator::make(request()->all(), [
                'cue_title' => 'required',
                'cue_description' => 'required',
                'old_panic_response' => 'required',
                'old_reward' => 'required',
                'negative_results' => 'required',
                'new_panic_response' => 'required',
                'positive_results' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            $userId = Auth::id();

            $userPanicResponse = new UserPanicResponse;
            $userPanicResponse->user_id = $userId;
            $userPanicResponse->cue_title = $request->has('cue_title') ? $request->cue_title : '';
            $userPanicResponse->cue_description = $request->has('cue_description') ? $request->cue_description : '';
            $userPanicResponse->old_panic_response = $request->has('old_panic_response') ? $request->old_panic_response : '';
            $userPanicResponse->old_reward = $request->has('old_reward') ? $request->old_reward : '';
            $userPanicResponse->new_panic_response = $request->has('new_panic_response') ? $request->new_panic_response : '';
            $userPanicResponse->save();
    
            $negativeResultString = $request->has('negative_results') ? $request->negative_results : '';
            $arrayNegativeResult = json_decode($negativeResultString, true);   

            if($request->has('negative_results') && count($arrayNegativeResult) > 0){
                foreach($arrayNegativeResult as $key => $value){
                    $negativeResults = new UserPanicResult;
                    $negativeResults->user_id = $userId;
                    $negativeResults->user_panic_response_id = $userPanicResponse->id;
                    $negativeResults->result = $value;
                    $negativeResults->response_type = 'negative';
                    $negativeResults->save();
                }
            }

            $positiveResultString = $request->has('positive_results') ? $request->positive_results : '';
            $arrayPositiveResult = json_decode($positiveResultString, true);   

            if($request->has('positive_results') && count($arrayPositiveResult) > 0){
                $userPanicResponse->positiveResults()->delete(); // This will delete all the comments related to the post
                foreach($arrayPositiveResult as $key => $value){
                    $positiveResults = new UserPanicResult;
                    $positiveResults->user_id = Auth::id();
                    $positiveResults->user_panic_response_id = $userPanicResponse->id;
                    $positiveResults->result = $value;
                    $positiveResults->response_type = 'positive';
                    $positiveResults->save();
                }
            }

            $negativeResultArray = $userPanicResponse->negativeResults->pluck('result')->toArray();
            $positiveResultArray = $userPanicResponse->positiveResults->pluck('result')->toArray();

            $returnData = (object)[
                'id'                   => $userPanicResponse['id'],
                'cue_title'            => $userPanicResponse['cue_title'] != null ? $userPanicResponse['cue_title'] : '',
                'cue_description'      => $userPanicResponse['cue_description'] != null ? $userPanicResponse['cue_description'] : '',
                'old_panic_response'   => $userPanicResponse['old_panic_response'] != null ? $userPanicResponse['old_panic_response'] : '',
                'old_reward'           => $userPanicResponse['old_reward'] != null ? $userPanicResponse['old_reward'] : '',
                'negative_results'     => !empty($negativeResultArray) ? $negativeResultArray : [],
                'new_reward'           => $userPanicResponse['old_reward'] != null ? $userPanicResponse['old_reward'] : '',
                'new_panic_response'   => $userPanicResponse['new_panic_response'] != null ? $userPanicResponse['new_panic_response'] : '',
                'positive_results'     => !empty($positiveResultArray) ? $positiveResultArray : [],
                'note'                 => $userPanicResponse['progress_note'] != null ? $userPanicResponse['progress_note'] : '',
            ];
            if($lang == "ko"){
                return response()->json(['success' => true, 'data' => $returnData, 'message' => '공황 발작 대처 방식이 성공적으로 등록되었습니다.'], 200);
            }else{
                return response()->json(['success' => true, 'data' => $returnData, 'message' => 'Panic Response added successfully'], 200);
            }
        } catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }
}
