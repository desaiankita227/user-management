<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\TestQuestion;
use App\Models\Test;
use App\Models\UserEpisodeAccessSystem;
use App\Models\UserEpisodeToolTestAccessSystem;
use App\Models\UserTest;
use Auth;
use Hash;
use App\Http\Resources\Api\TestDetailsResource;
use App\Http\Resources\Api\UserTestResource;
use Illuminate\Support\Facades\Crypt;

class UserTestController extends BaseController
{
    // Save data for submit test quetion answers
    public function userTest(Request $request){
        $userId = Auth::id();
        $userLang = ($request->has('lang')) ? $request->lang : Auth::user()->lang;
        $requestData    = json_decode($request->getContent(), true); // Get body data
       
        $currentWeekDetails = UserEpisodeAccessSystem::where('user_id', $userId)->where('completed_at', null)->where('is_weekly_episode_locked', 0)->where('is_weekly_episode_completed',0)->first();
        $currentWeekId = '';
        if($currentWeekDetails && $currentWeekDetails->weekly_episode_id){
            $currentWeekId  = $currentWeekDetails->weekly_episode_id;
        }
        $chartUrl       = "";

        $returnData     = (object)[];
        try{
            $testId         = $requestData["test_id"];
            $totalPoints    = 0;
            $totalYes       = 0;
            $totalNo        = 0;
            $totalQuestion  = 0;

            $part1TotalYes       = 0;
            $part2TotalYes       = 0;
            $part3TotalYes       = 0;

            $part1TotalNo        = 0;
            $part2TotalNo        = 0;
            $part3TotalNo        = 0;

            $saveAnswers    = array();
            $testScreen     = Test::where("id",$testId)->pluck('screen')->first();
            
            foreach ($requestData["answers"] as $key => $answersValue) {
                $points = 0;
                // Note: 1,2,3 is app screen
                // 1 = Negative Thinking    Yes,No
                // 2 = Symptoms Checklist   1 to 10 rating
                // 3 = Self-Diagnosis test  Yes,No
                
                if($testScreen == 1){
                    if($answersValue['answer'] == 1){
                        $totalYes   += 1;
                    } else {
                        $totalNo    += 1;
                    }
                } elseif ($testScreen == 2) {
                    $testQuestionData = TestQuestion::select('answer','points', 'part')->where('id',$answersValue['question_id'])->first();
                    
                    if($testQuestionData->part == 1){
                        if($answersValue['answer'] == 1){
                            $part1TotalYes  += 1;
                        } else {
                            $part1TotalNo   += 1;
                        }
                    } elseif ($testQuestionData->part == 2) {
                        if($answersValue['answer'] == 1){
                            $part2TotalYes  += 1;
                        } else {
                            $part2TotalNo   += 1;
                        }
                    } elseif ($testQuestionData->part == 3) {
                        if($answersValue['answer'] == 1){
                            $part3TotalYes  += 1;
                        } else {
                            $part3TotalNo   += 1;
                        }
                    } else {

                    }

                    if($answersValue['answer'] == 1){
                        $totalYes   += 1;
                    } else {
                        $totalNo    += 1;
                    }
                } else {

                }

                $subAnswer = '';
                if(isset($answersValue['sub_answer'])){
                    $subAnswer = $answersValue['sub_answer'];
                }
                
                $saveAnswers[]    = array(
                    "test_questions_id" => $answersValue['question_id'],
                    "weekly_episode_id" => $currentWeekId,
                    "test_id"           => $testId,
                    "user_id"           => $request->user()->id,
                    "answer"            => $answersValue['answer'],
                    "sub_answer"        => $subAnswer,
                    "points"            => $points,
                    "created_at"        => date("Y-m-d H:i:s")
                );
                $totalQuestion += 1;
                
            }

            if(!empty($saveAnswers)){
                UserTest::insert($saveAnswers);
            }

            $note   = "";
            $score  = "";
            $pointsMsg  = "";

            // Note: 1,2,3 is app screen
            // 1 = Negative Thinking    Yes,No
            // 2 = Symptoms Checklist   1 to 10 rating
            // 3 = Self-Diagnosis test  Yes,No
            if($testScreen == 1){
                $score  = ($totalYes / $totalQuestion) * 100;
                if($score > 70){
                    if($userLang == 'ko'){
                        $note = "테스트 결과, 부정적 사고 방식 패턴과 느끼시는 불안한 감정 사이에 강한 연관성이 있는 것으로 분석되었습니다. 다시 말해 현재 경험하고 계시는 불안 증상은 부정적 사고 방식의 패턴이 큰 원인이 되고 있으며 평소에 하는 생각들이 불안감을 극대화 시키고 있다는 것을 의미합니다. 이러한 패턴을 인식하고 부정적인 생각을 긍정적인 생각으로 대체하는 노력이 필요합니다.";
                        $pointsMsg = '강한 연관성';
                    }else{
                        $note = "The test shows a strong connection between having negative thoughts and feeling anxious. It means that when you have negative thoughts, you're more likely to feel anxious, and these thoughts can make anxiety worse. It's essential to recognize these patterns and try to replace them with more positive thoughts.";
                        $pointsMsg = 'Strong Link';
                    }
                } elseif ($score >= 30 && $score <= 70) {
                    if($userLang == 'ko'){
                        $note = "테스트 결과, 부정적 사고 방식 패턴이 현재 느끼시는 불안감의 유일한 원인은 아닌 것으로 분석되었습니다. 그러나 부정적 사고 방식과 불안감이 종종 보여지고 있으니 부정적 사고를 긍정적으로 전환하여 불안감을 효과적으로 관리하는 것이 중요할 것으로 보입니다.";
                        $pointsMsg = '보통 연관성';
                    }else{
                        $note = "The test suggests a moderate connection between anxiety symptoms and negative thinking patterns. This means that negative thoughts can contribute to anxiety and vice versa, but it's not the only cause. It's still crucial to address negative thinking to manage anxiety effectively.";
                        $pointsMsg = 'Moderate Link';
                    }
                } else {
                    if($userLang == 'ko'){
                        $note = "테스트 결과, 부정적 사고 방식 패턴과 느끼시는 불안감 또는 불안 증상과는 연관 관계가 매우 약한 것으로 분석되었습니다. 따라서 릴렉세이션 테크닉을 평소에 훈련함으로써 현재 경험하고 계신 증상을 완화시키시기 바랍니다.";
                        $pointsMsg = '약한 연관성';
                    }else{
                        $note = "The test shows a weak connection between anxiety symptoms and negative thinking patterns. It means that anxiety might have other factors besides negative thoughts. But don't worry, addressing negative thoughts can still help in managing anxiety and promoting a more positive mindset.";
                        $pointsMsg = 'Weak Link';
                    }
                }
            } elseif ($testScreen == 2) {
                if($part1TotalYes > 0 && $part2TotalYes >= 4 && $part3TotalNo <= 0){
                    if($userLang == 'ko'){
                        $note = "질문에 대한 대답과 설명하신 증상들을 고려하면, 공황 장애를 경험하고 계실 가능성이 높습니다. 그러나 이러한 증상들은 다른 정신 질환, 본인의 의료적 상태, 또는 약물 남용이나 약물 복용과 같은 생리적인 영향으로도 나타날 수 있습니다. 그렇기에, 정확한 진단을 받으시기 위해서는, 정신 건강 전문가의 진료를 받으시는 것이 중요합니다. 그 진료를 통해 정확한 진단과 그에 맞는 적절한 치료를 받으시는 것을 추천 드립니다.";
                    }else{
                        $note = "Based on the symptoms you have described, there is a strong likelihood that you may be experiencing a panic disorder. However, it's important to consider that these symptoms could also be related to other mental disorders, medical conditions, or the physiological effects of substances like drug abuse or medication. To receive an accurate diagnosis, it is crucial that you consult with a mental health professional. They will be able to evaluate your symptoms and provide the appropriate guidance and support.";                        
                    }
                    $score = "";
                } else {
                    if($userLang == 'ko'){
                        $note = "질문에 대한 대답과 설명하신 증상들을 고려하면, 공황 장애를 겪고 계신 것은 아닌 것으로 보입니다. 그렇지만, 이러한 증상들은 다른 정신 질환, 본인의 의료적 상태, 또는 약물 남용이나 약물 복용과 같은 생리적인 영향으로도 나타날 수 있습니다. 그렇기에, 정확한 진단을 받으시기 위해서는, 정신 건강 전문가의 진료를 받으시는 것이 중요합니다. 그 진료를 통해 정확한 진단과 그에 맞는 적절한 치료를 받으시는 것을 추천 드립니다.";
                    }else{
                        $note = "Based on the information provided, it appears that you do not have a panic disorder. However, it's important to consider that the symptoms you have been experiencing could be attributed to other mental disorders, medical conditions, or the physiological effects of substances like drug abuse or medication. To ensure an accurate diagnosis, it is highly recommended that you consult with a mental health professional. They will be able to assess your symptoms thoroughly and provide the necessary guidance and support for understanding and addressing your condition effectively.";
                    }
                    $score = "";
                }

                // Old code comment by dipali gupta
                // $note = "You will take this test once more at the end of the 12-week program. The purpose of this test is to evaluate the improvement of your symptoms. Therefore, the test results will be provided to you upon completion of the 12-week period.";
                // $score      = "";
                // end Old code comment by dipali

                // If current Week Id is last than create url
                if($currentWeekId == 12){
                    $encryptedUserId = Crypt::encryptString($userId);

                    $chartUrl   = route('test-result', ['userId' => $encryptedUserId]);
                }
            } elseif ($testScreen == 3) {

                // if($part1TotalYes > 0 && $part2TotalYes >= 4 && $part3TotalNo <= 0){
                //     $note = "Based on the symptoms you have described, there is a strong likelihood that you may be experiencing a panic disorder. However, it's important to consider that these symptoms could also be related to other mental disorders, medical conditions, or the physiological effects of substances like drug abuse or medication. To receive an accurate diagnosis, it is crucial that you consult with a mental health professional. They will be able to evaluate your symptoms and provide the appropriate guidance and support.";
                //     $score = "";
                // } else {
                //     $note = "Based on the information provided, it appears that you do not have a panic disorder. However, it's important to consider that the symptoms you have been experiencing could be attributed to other mental disorders, medical conditions, or the physiological effects of substances like drug abuse or medication. To ensure an accurate diagnosis, it is highly recommended that you consult with a mental health professional. They will be able to assess your symptoms thoroughly and provide the necessary guidance and support for understanding and addressing your condition effectively.";
                //     $score = "";
                // }
            } else {

            }
           
            //after submit the test lock the perticular test 
            UserEpisodeToolTestAccessSystem::where('test_id',$requestData['test_id'])
                        ->where('user_id', $userId)
                        ->where('weekly_episode_id',$currentWeekId)
                        ->update([
                           'is_weekly_test_locked' => 1
                        ]);
            //end after submit the test lock the perticular test   

            if(!empty($score)){
                $returnData = array(
                    "note"          => $note,
                    "points"        => round($score)."%",
                    "points_message" => $pointsMsg,
                    "chart_url"     => $chartUrl,
                    //"save_answers"  => $saveAnswers,
                );
            } else {
                $returnData = array(
                    "note"          => $note,
                    "points"        => "",
                    "points_message" => $pointsMsg,
                    "chart_url"     => $chartUrl,
                    //"save_answers"  => $saveAnswers,
                );
            }
            
            return $this->sendResponse($returnData, '');
        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return $this->sendError($returnData, 'Oops! Something went wrong. We’re unable to process your request right now. Please try again later. If the issue persists, reach out to our support team at support@dooroowa.com for assistance. We’re here to help!', 422);
            // return $this->sendError($returnData, $e->getMessage());
        }
    }
}
