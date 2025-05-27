<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});






// <?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\User\EpisodeDetailController;
// use App\Http\Controllers\Api\User\ForgotPasswordController;
// use App\Http\Controllers\Api\User\HomeController;
// use App\Http\Controllers\Api\User\LoginController;
// use App\Http\Controllers\Api\User\RegisterController;
// use App\Http\Controllers\Api\User\TestController;
// use App\Http\Controllers\Api\User\TestQuestionController;
// use App\Http\Controllers\Api\User\ToolController;
// use App\Http\Controllers\Api\User\UserBucketListController;
// use App\Http\Controllers\Api\User\UserController;
// use App\Http\Controllers\Api\User\UserJournalToolController;
// use App\Http\Controllers\Api\User\UserNotificationController;
// use App\Http\Controllers\Api\User\UserPanicResponseController;
// use App\Http\Controllers\Api\User\UserTestController;
// use App\Http\Controllers\Api\User\WeeklyEpisodeController;

// /*
// |--------------------------------------------------------------------------
// | API Routes
// |--------------------------------------------------------------------------
// |
// | Here is where you can register API routes for your application. These
// | routes are loaded by the RouteServiceProvider within a group which
// | is assigned the "api" middleware group. Enjoy building your API!
// |
// */
// Route::group(['prefix' => 'v1'], function () {
// 	# User Login Routes...
// 	Route::post('/login', [LoginController::class, 'login']);

// 	#User Register Route..
// 	Route::post('/sign-up', [RegisterController::class, 'register']);

// 	#User Forgot Paasword Route..
// 	Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);

	
// 	Route::middleware(['checkuser', 'auth:sanctum'])->group(function(){
// 		#User Logout Route...
// 		Route::post('/logout',[LoginController::class,'logout']);
		
// 		#Refresh Access Token Route...
// 		Route::post('/refresh-token',[UserController::class,'refreshAccessToken']);
		
// 		#Get User Profile Details
// 		Route::get('/user-details', [UserController::class, 'userDetails']);

// 		#Update User Profile
// 		Route::post('/profile-update', [UserController::class, 'updateUser']);

// 		#Change User Password Route...
// 		Route::post('/change-password', [UserController::class, 'changePassword']);

// 		#User Delete Api
// 		Route::get('/delete-user', [UserController::class, 'deleteUser']);

// 		#Change User Language Route...
// 		Route::post('/change-language', [UserController::class, 'changeLanguage']);

// 		#Get WeeklyEpisodes Details Route...
// 		Route::get('/weeks', [WeeklyEpisodeController::class, 'weeklyEpisodes']);

// 		#Get Episodes Details Route...
// 		Route::get('/episode-details/{id}', [EpisodeDetailController::class, 'episodeDetails']);

// 		#Get Test Category Route...
// 		Route::get('/tests', [TestController::class, 'test']);

// 		#Post Test Route...
// 		Route::get('/test-details/{id}', [TestQuestionController::class, 'testDetail']);

// 		#Submit Test Route...
// 		Route::post('/submit-test', [UserTestController::class, 'userTest']);

// 		#Tool Route...
// 		Route::get('/tools', [ToolController::class, 'tool']);

// 		#Tool Details Route...
// 		Route::get('/audio-tool-details/{id}', [ToolController::class, 'toolDetails']);

// 		#User Journal Tool Route...
// 		Route::get('/journals', [UserJournalToolController::class, 'journals']);

// 		#Add User Journal Tool Route...
// 		Route::post('/journals', [UserJournalToolController::class, 'addJournal']);

// 		#Update User Journal Tool Route...
// 		Route::put('/journals/{id}', [UserJournalToolController::class, 'updateJournal']);

// 		#Delete Journal Tool Route...
// 		Route::delete('/journals/{id}', [UserJournalToolController::class, 'deleteJournal']);

// 		#User Panic Response Route...
// 		Route::get('/panic-responses', [UserPanicResponseController::class, 'panicResponses']);

// 		#Add User Panic Response Route...
// 		Route::post('/panic-responses', [UserPanicResponseController::class, 'addPanicResponse']);

// 		#Update User Panic Response Route...
// 		Route::put('/panic-responses/{id}', [UserPanicResponseController::class, 'updatePanicResponse']);

// 		#Delete User Panic Response Route...
// 		Route::delete('/panic-responses/{id}', [UserPanicResponseController::class, 'deletePanicResponse']);

// 		#Progressive Note Route...
// 		Route::put('/panic-responses-note/{id}', [UserPanicResponseController::class, 'panicResponsesNote']);

// 		#Bucket List Route...
// 		Route::get('/bucket-lists', [UserBucketListController::class, 'bucketLists']);

// 		#Bucket List Details Route...
// 		Route::get('/bucket-lists/{id}', [UserBucketListController::class, 'bucketListDetails']);

// 		#Add Bucket List Route...
// 		Route::post('/bucket-lists', [UserBucketListController::class, 'addBucketList']);

// 		#Update Bucket List Route...
// 		Route::put('/bucket-lists/{id}', [UserBucketListController::class, 'updateBucketList']);

// 		#Delete Bucket List Route...
// 		Route::delete('/bucket-lists/{id}', [UserBucketListController::class, 'deleteBucketList']);

// 		#Goal Completion Route...
// 		Route::put('/complete-goal/{id}', [UserBucketListController::class, 'goalCompletion']);

// 		#Update Goal Reminder Route...
// 		Route::put('/update-goal-reminder/{id}', [UserBucketListController::class, 'updateGoalReminder']);

// 		#Home Details Route...
// 		Route::get('/home', [HomeController::class, 'home']);

// 		#Week Completion Route...
// 		Route::put('/week-completion/{id}', [WeeklyEpisodeController::class, 'weekCompletion']);

// 		#Get Notifications Route...
// 		Route::get('/notifications', [UserNotificationController::class, 'getNotifications']);

// 		#Delete Notifications Route...
// 		Route::delete('/notification/{id}', [UserNotificationController::class, 'deleteNotifications']);

// 		#Clear all Notifications Route...
// 		Route::delete('/clear-all-notifications', [UserNotificationController::class, 'clearAllNotifications']);

// 		Route::group(['prefix' => '1'], function () {
// 			Route::get('/home', [HomeController::class, 'homeV1']);
// 			Route::post('/panic-responses', [UserPanicResponseController::class, 'addPanicResponseV1']);
// 		});
// 	});
// });

// Route::group(['prefix' => 'v1.1'], function () {
// 	Route::middleware(['checkuser', 'auth:sanctum'])->group(function(){
// 		#Get Test Route...
// 		Route::get('/tests', [TestController::class, 'testV1']);

// 		#Add User Panic Response Route...
// 		Route::post('/panic-responses', [UserPanicResponseController::class, 'addPanicResponseV1']);

// 		#Home Details Route...
// 		Route::get('/home', [HomeController::class, 'homeV1']);
// 	});
// });
