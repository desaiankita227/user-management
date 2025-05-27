<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\UserJournalTool;
use App\Http\Resources\Api\UserJournalToolResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserJournalToolController extends BaseController
{
    /**
     * Retrieves the journals for the authenticated user.
     *
     * @param Request $request The HTTP request object.
     * @throws \Exception If an error occurs while retrieving the journals.
     * @return Response The response containing the user's journal data.
     */
    public function journals(Request $request){
        Log::info($request->all());
        $userId = Auth::id();

        $returnData = (object)[];
        try{
            $userJournals = UserJournalTool::where('user_id',$userId)->get();
            if($userJournals){
                $returnData = UserJournalToolResource::collection($userJournals->toArray());
            }else{
                $returnData = (object)[];
            }
            return $this->sendResponse($returnData, 'User Journal Data');
        }catch(\Exception $e){
            return $this->sendError($returnData, $e->getMessage(),500);
        }
    }

    /**
     * Adds a journal entry based on the provided request data.
     *
     * @param Request $request The HTTP request object containing the data for the journal entry.
     * @throws \Illuminate\Validation\ValidationException If the validation fails.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the success of the operation and the added journal entry data.
     */
    public function addJournal(Request $request){
        // Log::info($request->all());
        $validator = Validator::make(request()->all(), [
            'automatic_thoughts' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $lang = $request->has('lang') ? $request->lang : Auth::user()->lang;
        $userJournal = new UserJournalTool;
        $userJournal->user_id = Auth::id();
        $userJournal->automatic_thoughts = $request->automatic_thoughts;
        $userJournal->save();

        $returnData = (object)[
            "id"=> $userJournal->id,
            "automatic_thoughts"=> $userJournal->automatic_thoughts != null ? $userJournal->automatic_thoughts : '',
            "reframed_thoughts"=> $userJournal->reframed_thoughts != null ? $userJournal->reframed_thoughts : '',
        ];

        if($lang == 'ko'){
            return response()->json(['success' => true, "data" => $returnData,'message' => '저널이 성공적으로 등록이 되었습니다.'], 200);
        } else {
            return response()->json(['success' => true, "data" => $returnData,'message' => 'Journal entry added successfully'], 200);
        }
    }

    /**
     * Updates a journal entry.
     *
     * @param Request $request The HTTP request object.
     * @throws \Illuminate\Validation\ValidationException if validation fails.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the updated journal entry data.
     */
    public function updateJournal(Request $request){
        Log::info($request->all());

        $userId = Auth::id();

        $userJournal = UserJournalTool::where('user_id',$userId)->where('id', $request->id)->first();
        $lang = $request->has('lang') ? $request->lang : Auth::user()->lang;
        // check if user journal exists
        if($userJournal){
            $oldAutomaticThought = $userJournal->automatic_thoughts;
            $userJournal->automatic_thoughts = $request->has('automatic_thoughts') ? $request->automatic_thoughts : $oldAutomaticThought;
            $userJournal->reframed_thoughts = $request->reframed_thoughts;
            $userJournal->save();

            $returnData = (object)[
                "id"=> $userJournal->id,
                "automatic_thoughts"=> $userJournal->automatic_thoughts != null ? $userJournal->automatic_thoughts : '',
                "reframed_thoughts"=> $userJournal->reframed_thoughts != null ? $userJournal->reframed_thoughts : '',
            ];
            if($lang == 'ko'){
                return response()->json(['success' => true, "data" => $returnData,'message' => '저널이 성공적으로 업데이트 되었습니다.'], 200);
            } else {
                return response()->json(['success' => true, "data" => $returnData,'message' => 'Journal entry updated successfully'], 200);
            }
        }
        if($lang == 'ko'){
            return response()->json(['success' => false,'message' => '저널을 찾을 수 없습니다.'], 404);
        }else{
            return response()->json(['success' => false,'message' => 'Journal entry not found'], 404);
        }
    }

    /**
     * Delete a journal entry.
     *
     * @param int $id The ID of the journal entry to be deleted.
     * @throws \Exception If an error occurs during the deletion process.
     * @return \Illuminate\Http\Response The response containing the success status and message.
     */
    public function deleteJournal($id){
        Log::info('Deleting journal entry with id: '.$id);
        try{
            $userId = Auth::id();

            $userJournals = UserJournalTool::where('user_id',$userId)->where('id', $id)->first();

            if($userJournals){
                $userJournals->delete();
                if(Auth::user()->lang == 'ko'){
                    return response(['success'=>true, 'message'=>'저널 내용이 삭제 되었습니다.' ]);
                }else{
                    return response(['success'=>true, 'message'=>'Your journal data has been deleted.']);
                }
            }

            if(Auth::user()->lang == 'ko'){
                return response(['success'=>false, 'message' => '저널 내용을 찾을 수 없습니다.'], 404);
            }else{
                return response(['success'=>false, 'message' => 'Your journal data not found.'], 404);
            }
        }catch(\Exception $e){
            return $this->sendError($e->getMessage(),500);
        }
    }

}
