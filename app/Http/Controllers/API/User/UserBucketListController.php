<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\UserBucketList;
use App\Http\Resources\Api\UserBucketListResource;
use App\Models\UserBucketListStep;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserBucketListController extends BaseController
{   
    /**
     * Retrieves the bucket lists for the authenticated user.
     *
     * @param Request $request The HTTP request object.
     * @throws \Exception If an error occurs while retrieving the bucket lists.
     * @return \Illuminate\Http\Response The response containing the user's bucket lists.
     */
    public function bucketLists(Request $request){
        // Log::info($request->all());
        $userId = Auth::id();

        $returnData = (object)[];
        try{
            $userBucketLists = UserBucketList::with(['steps' => function ($query) {
                $query->orderBy('step', 'DESC');
            }])
            ->where('user_id',$userId)->get();

            if($userBucketLists){
                $returnData = UserBucketListResource::collection($userBucketLists->toArray());
            }else{
                $returnData = (object)[];
            }
            return $this->sendResponse($returnData, 'User Bucket Lists');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }

    /**
     * Add a bucket list item.
     *
     * @param Request $request The HTTP request object.
     * @throws Some_Exception_Class Description of the exception.
     * @return void
     */
    public function addBucketList(Request $request){
        try{
            // Log::info($request->all());
            $userId = Auth::id();
    
            $userBucketList          = new UserBucketList;
            $userBucketList->user_id = $userId;
            $userBucketList->save();

            $goals = json_decode($request->getContent(), true);
            foreach($goals as $goal){
                if(isset($goal['title']) && $goal['title'] != ''){
                    $userBucketList->title = $goal['title'];
                    $userBucketList->save();
                }
                $goalStep = new UserBucketListStep();
                $goalStep->user_bucket_list_id = $userBucketList->id;
                $goalStep->user_id = $userId;
                $goalStep->step = $goal['step'];
                $goalStep->description = $goal['description'];
                $goalStep->due_date = $goal['due_date'];
                $goalStep->save();
            }
            $newestBucket = UserBucketList::with(['steps' => function ($query) {
                $query->orderBy('step', 'DESC');
            }])
            ->where('user_id',$userId)->where('id', $userBucketList->id)->first()->toArray();

            $returnData = new UserBucketListResource($newestBucket);
            if($request->input('lang') == 'ko'){
                return $this->sendResponse($returnData, '당신의 목표가 버킷리스트에 성공적으로 추가되었습니다.');
            }else{
                return $this->sendResponse($returnData, 'Your goal was successfully added to Bucket.');
            }
        }catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }

    /**
     * Updates the bucket list.
     *
     * @param Request $request The request object.
     * @throws Some_Exception_Class Description of the exception.
     * @return void
     */
    public function updateBucketList(Request $request){
        try{
            Log::info($request->all());
            $userId = Auth::id();
            $bucketID = $request->id;
            $userBucket = UserBucketList::with('steps')->where('user_id', $userId)->where('id', $bucketID)->first();

            if ($userBucket) {
                $goals = json_decode($request->getContent(), true);
                foreach($goals as $goal){
                    if(isset($goal['title']) && $goal['title'] != ''){
                        $userBucket->title = $goal['title'];
                        $userBucket->save();
                    }

                    $goalStep = UserBucketListStep::where('user_bucket_list_id', $userBucket->id)->where('step', $goal['step'])->first();
                    if($goalStep){
                        $goalStep->description = $goal['description'];
                        $goalStep->due_date = $goal['due_date'];
                        $goalStep->save();
                    }else{
                        $newGoalStep = new UserBucketListStep();
                        $newGoalStep->user_bucket_list_id = $userBucket->id;
                        $newGoalStep->step = $goal['step'];
                        $newGoalStep->description = $goal['description'];
                        $newGoalStep->due_date = $goal['due_date'];
                        $newGoalStep->save();
                    }
                }
                $newestBucket = UserBucketList::with(['steps' => function ($query) {
                                    $query->orderBy('step', 'DESC');
                                }])
                                ->where('user_id',$userId)->where('id', $userBucket->id)->first()->toArray();
    
                $returnData = new UserBucketListResource($newestBucket);

                if($request->input('lang') == 'ko'){
                    return $this->sendResponse($returnData, '당신의 목표가 버킷리스트에서 성공적으로 업데이트되었습니다.');
                }else{
                    return $this->sendResponse($returnData, 'Your goal was successfully updated to Bucket.');
                }
            }else{
                if($request->input('lang') == 'ko'){
                    return response(['success' => false, 'message' => '버킷리스트 목표를 찾을 수 없습니다.'], 404);
                }else{
                    return response(['success' => false, 'message' => 'Bucket goal not found.'], 404);
                }
            }
        } catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }

    /**
     * Deletes a specific bucket list identified by the given ID.
     *
     * @param int $id The ID of the bucket list to delete.
     * @throws Some_Exception_Class Description of exception that can be thrown.
     * @return void
     */
    public function deleteBucketList($id){
        Log::info('Deleting bucket list with id: '.$id);
        try{
            $userId = Auth::id();

            $userBucket = UserBucketList::where('user_id',$userId)->where('id', $id)->first();

            if($userBucket){
                $userBucket->steps()->delete(); // This will delete all the steps related to the bucket

                $userBucket->delete();

                if(Auth::user()->lang == 'ko'){
                    return response([
                        'success'=>true,
                        'message'=>'당신의 버킷리스트 목표가 삭제 되었습니다.'
                    ]);
                } else {
                    return response([
                        'success'=>true,
                        'message'=>'Your bucket goal has been deleted.'
                    ]);
                }
            }
            if(Auth::user()->lang == 'ko'){
                return response([
                    'success'=>false,
                    'message' => '당신의 버킷리스트 목표를 찾을 수 없습니다.'
                ], 404);
            } else {
                return response([
                    'success'=>false,
                    'message' => 'Your bucket goal not found.'
                ], 404);
            }
        }catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }

    /**
     * Retrieves the details of a bucket list item.
     *
     * @param int $id The ID of the bucket list item.
     * @throws \Exception If an error occurs while retrieving the details.
     * @return void
     */
    public function bucketListDetails($id){
        try{
            $userId = Auth::id();

            $userBucket = UserBucketList::with(['steps' => function ($query) {
                $query->orderBy('step', 'DESC');
            }])->where('user_id',$userId)->where('id', $id)->first();

            $returnData = $userBucket ? new UserBucketListResource($userBucket) : (object)[];
            if(Auth::user()->lang == 'ko'){
                return $this->sendResponse($returnData, '당신의 버킷리스트가 성공적으로 검색되었습니다.');
            } else {
                return $this->sendResponse($returnData, 'Your bucket was successfully retrieved.');
            }
        }catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }

    /**
     * A description of the entire PHP function.
     *
     * @param Request $request The request object.
     * @throws Some_Exception_Class Description of the exception.
     * @return void
     */
    public function goalCompletion(Request $request){
        Log::info($request->all());
        try{
            $userId = Auth::id();
            $bucketID = $request->id;

            $goalStep = UserBucketListStep::where('user_id', $userId)->where('id', $bucketID)->first();

            if (!$goalStep) {
                if($request->input('lang') == 'ko'){
                    return response(['success' => false, 'message' => '버킷리스트 목표를 찾을 수 없습니다.'], 404);
                }else{
                    return response(['success' => false, 'message' => 'Bucket goal not found.'], 404);
                }
            }

            if ($goalStep->is_completed == 0) {
                $goalStep->is_completed = 1;
                $goalStep->save();
                
                $bucketId = $goalStep->user_bucket_list_id;
                $newestBucket = UserBucketList::with(['steps' => function ($query) {
                                    $query->orderBy('step', 'DESC');
                                }])->where('user_id',$userId)->where('id', $bucketId)->first()->toArray();

                $returnData = new UserBucketListResource($newestBucket);

                if($request->input('lang') == 'ko'){
                    return $this->sendResponse($returnData, '당신의 버킷리스트 목표를 달성하셨습니다.');
                }else{
                    return $this->sendResponse($returnData, 'Your bucket goal has been completed.');
                }
            }
            if($request->input('lang') == 'ko'){
                return response(['success' => true, 'message' => '당신의 버킷리스트 목표가 이미 달성되었습니다.'], 200);
            }else{
                return response(['success' => true, 'message' => 'Your bucket goal has already been completed.'], 200);
            }
        } catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }

    public function updateGoalReminder($id){
        Log::info('Updating goal reminder with id: '.$id);
        try{
            $userId = Auth::id();

            $userBucket = UserBucketList::where('user_id',$userId)->where('id', $id)->first();

            if($userBucket){
                $userBucket->goal_reminder = ($userBucket->goal_reminder == 1) ? 0 : 1;
                $userBucket->save();
                if(Auth::user()->lang == 'ko'){
                    return response([
                        'success' => true,
                        'message' => '목표 알림이 성공적으로 업데이트 되었습니다.',
                    ], 200);
                } else {
                    return response([
                        'success' => true,
                        'message' => 'Goal reminder updated successfully.',
                    ], 200);
                }
            }

            if(Auth::user()->lang == 'ko'){
                return response([
                    'success'=>false,
                    'message' => '당신의 버킷리스트 목표를 찾을 수 없습니다.'
                ], 404);
            } else {
                return response([
                    'success'=>false,
                    'message' => 'Your bucket goal not found.'
                ], 404);
            }
        }catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }
}
