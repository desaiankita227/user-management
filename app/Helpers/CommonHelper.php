<?php

namespace App;

use Image;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use File;
use App\Models\User;
use App\Models\Tool;
use App\Models\UserBucketList;
use Twilio\Rest\Client;
use Log;
use Mail;
use Storage;
use Google\Auth\Credentials\ServiceAccountCredentials;

class Commonhelper
{
	public static function uploadFileWithThumbnail($request, $filename, $path, $thumbnailPath = NULL, $resizeH = 100, $resizeW = 100, $oldFileName = NULL)
	{
		try {
			$image		= $request->file($filename);
			$imagename 	= time() . Str::random(5) . '.' . $image->extension();
			//Delete old file
			if ($oldFileName) {
				if (file_exists(public_path($path . $oldFileName))) {
					self::deleteFile(public_path($path . $oldFileName));
				}
				if (file_exists(public_path($thumbnailPath . $oldFileName))) {
					self::deleteFile(public_path($thumbnailPath . $oldFileName));
				}
			}

			$input['imagename'] = time() . Str::random(5) . '.' . $image->extension();
			// Create thumbnail
			if ($thumbnailPath) {
				$destinationPath	= public_path($thumbnailPath);
				if (!file_exists($destinationPath)) {
					mkdir($destinationPath, 0777, true);
				}
				$img = Image::make($image->path());
				$img->resize($resizeH, $resizeW, function ($constraint) {
					$constraint->aspectRatio();
				})->save($destinationPath . '/' . $imagename);
			}
			
			// Upload file
			if ($path) {
				$destinationPath = public_path($path);
				if (!file_exists($destinationPath)) {
					mkdir($destinationPath, 0777, true);
				}
				$image->move($destinationPath, $imagename);
			}

			$message = "Image Upload successful";

			return response()->json([
				'code'		=> 200,
				'message'	=> $message,
				'imagename' => $imagename
			]);
			//return self::apiresponse(1,$message,$imageNameArr);
		} catch (\Exception $e) {
			return response()->json([
				'code'		=> 400,
				'message'	=> $e->getMessage(),
				'imagename' => ""
			]);
			// return self::apiresponse(0,$e->getMessage());
		}
	}


    public static function sendmail($toemail, $data, $template, $subject)
    {
        $from = Config::get('constants.MAIL_FROM_ADDRESS');
        $appname = Config::get('constants.APP_NAME');
        Mail::send('emails.' . $template, $data, function ($message) use ($toemail, $appname, $subject) {
            $message->to($toemail, $appname)
                ->cc('info@fivestarsitters.com')
                ->subject($subject);
        });
    }


	public static function uploadMultipleFileWithThumbnail($request, $filename, $path, $thumbnailPath = NULL, $resizeH = 100, $resizeW = 100)
	{
		try {
			$nameImages = array();
			$image		= $request->file($filename);
			foreach ($request->file($filename) as $image) {
				$imagename 	= time() . Str::random(5) . '.' . $image->extension();

				// Create thumbnail
				if ($thumbnailPath) {
					$destinationPathThum = public_path($thumbnailPath);
					if (!file_exists($destinationPathThum)) {
						mkdir($destinationPathThum, 0777, true);
					}
					$img = Image::make($image->path());
					$img->resize($resizeH, $resizeW, function ($constraint) {
						$constraint->aspectRatio();
					})->save($destinationPathThum . '/' . $imagename);
				}

				// Upload file
				if ($path) {
					$destinationPath = public_path($path);
					if (!file_exists($destinationPath)) {
						mkdir($destinationPath, 0777, true);
					}
					$image->move($destinationPath, $imagename);
				}
				$nameImages[] = array(
					"image" => $imagename
				);
			}
			$message = "Image Upload successful";
			return array('code' => 200, 'message' => "", 'data' => $nameImages);
			//return response()->json(['code' => 200, 'message' => "", 'data' => $nameImages]);
		} catch (\Exception $e) {
			return response()->json(['code' => 400, 'message' => $e->getMessage(), 'data' => array()]);
		}
	}

	public static function deleteFile($filePath)
	{
		unlink($filePath);
	}

	public static function deleteDirectory($directoryPath)
	{
		if (file_exists($directoryPath)) {
			File::deleteDirectory($directoryPath);
		}
		return true;
	}

	public static function dateFormatChange($date, $returnTime = false)
	{
		if ($returnTime) {
			return date('m-d-Y H:i:s', strtotime($date));
		} else {
			return date('m-d-Y', strtotime($date));
		}
	}

	public static function apiresponse($code = 1, $message = "success", $content = null)
	{
		if ($code == 1) {
			$status = 200;
		} elseif ($code == 2) {
			$status = 999;
		} elseif ($code == 3) {
			$status = 400;
		} else {
			$status = 404;
		}
		if (is_array($content) && count($content) == 0) {
			$content = [];
		} else {
			if ($content == null) {
				$content = (object)[];
			}
		}
		$interResponse = array(
			'code'		=> $status,
			'message'	=> $message,
			'data'		=> $content
		);
		$response = new Response($interResponse);
		return $response;
	}

	public static function jobSendEmailSMSUserOTPVerification($userData)
	{
		// Log::info("test123" . $userData);
		try {
			if (!empty($userData)) {
				if ($userData->signup_via > 1) {
					// Send OTP To Mobile Number
					$accountSid         = config('app.twilio')['TWILIO_ACCOUNT_SID'];
					$authToken          = config('app.twilio')['TWILIO_AUTH_TOKEN'];
					$twilioPhoneNumber  = config('app.twilio')['TWILIO_PHONE_NUMBER'];

					$client = new Client($accountSid, $authToken);

					$phoneNumber = $userData->phone_number;
					// $phoneNumber = "+919722552298";
					// Use the client to do fun stuff like send text messages!
					$client->messages->create(
						// the number you'd like to send the message to
						$phoneNumber,
						array(
							// A Twilio phone number you purchased at twilio.com/console
							'from' => $twilioPhoneNumber,
							// the body of the text message you'd like to send
							'body' => "Dear User, " . $userData->otp . " is the OTP for your request initiated through Homer."
						)
					);
				} else {
					Mail::send('emails.user.otp_verification', ['user_data' => $userData], function ($message) use ($userData) {
						$message->to($userData->email);
						$message->subject('HOMER Account OTP Verification');
					});
				}
				return true;
			}
		} catch (\Exception $e) {
			$returnObject = (object)[];
			return response()->json([
				'success'   => false,
				'message'   => $e->getMessage(),
				'data'      => $returnObject
			], 400);
		}
	}
	public static function compressImageVideo($type,$file,$path,$episode_id, $setKiloBitrate = 600)
	{
		try{
			$s3Directory = 'files/'.$episode_id.'/';
	        $filename    =  $file->getClientOriginalName();
	        $extension   =  $file->extension();
	        $arrayFileName = explode(".", $file->getClientOriginalName());
	      
	        $fileName = $arrayFileName[0] . date('his') . '.' . $extension;
	        $s3FilePath = $s3Directory. $fileName;
	     
	        $storage_path_full = '/'.$filename;
	       
			if($type == 'video'){

				Storage::disk('local')->put($s3FilePath, file_get_contents($file));

				/*Uploded Video Path*/
				$videoPath = Storage::disk('local')->url($s3FilePath);

				$message = "video Upload successful";
				return array(
					'code'		=> 200,
					'message'	=> $message,
					'filePath' => $videoPath,
					'fileName' => $fileName,

				);
			}elseif($type == 'image'){

				if ($path) {
					//$fileName = $arrayFileName[0] . date('his') . '.' . $extension;
	                $img = Image::make($file->path());
	                $destinationPath = public_path($path);
	                if (!file_exists($destinationPath)) {
	                    mkdir($destinationPath, 0777, true);
	                }
					$fileSize = $file->getSize();
					if($fileSize > 1000000){
						$img->resize(1000, 1000, function ($constraint) {
							$constraint->aspectRatio();
						});
					}
	               	$img->save($destinationPath . '/' . $fileName);
					/*Upload Image to S3 From Storage*/
					Storage::disk('local')->put($s3Directory.$fileName, file_get_contents($destinationPath . '/' . $fileName));

					/*Get Image Path*/
					$imagePath = Storage::disk('local')->url($s3Directory.$fileName);
	                self::deleteFile($destinationPath . '/' . $fileName);
					$message = "Image Upload successful";
					return array(
						'code'		=> 200,
						'message'	=> $message,
						'filePath' => $imagePath,
						'fileName' => $fileName,
					);
	            }
	        }elseif($type == 'audio'){

	        	if ($path) {

					/*Upload Image to S3 From Storage*/
					Storage::disk('local')->put($s3Directory.$fileName, file_get_contents($file));

					/*Get Image Path*/
					$audioPath = Storage::disk('local')->url($s3Directory.$fileName);
	                
					$message = "Audio Upload successful";
					return array(
						'code'		=> 200,
						'message'	=> $message,
						'filePath' => $audioPath,
						'fileName' => $fileName,
					);
	            }

	        }
		}catch (\Exception $e) {
			return response()->json([
				'code'		=> 400,
				'message'	=> $e->getMessage(),
				'files' => ""
			]);
		}
	}

	public static function maxToolSequence(){
		try{
			$sequence = Tool::withTrashed()->max('sequence');
			return $sequence > 0 ? $sequence + 1 : 1;
		}catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public static function getBucketName($id){
		try{
			$bucket = UserBucketList::find($id);
			if($bucket){
				return $bucket['title'];
			}
			return "";
		}catch (\Exception $e) {
			return $e->getMessage();
		}

	}
	
	public static function fetchFirebaseToken(){
		try{
			$serviceAccountFile = base_path('dooroowa-app-e9eb1bc9cd6e.json');        

			// Define the required scopes
			$scopes = [
				'https://www.googleapis.com/auth/firebase.messaging'
			];

			// Authenticate a JWT client with the service account
			$credentials = new ServiceAccountCredentials($scopes, $serviceAccountFile);

			// Use the JWT client to generate an access token
			$accessToken = $credentials->fetchAuthToken();
			if(sizeof($accessToken) > 0) {
				return $accessToken['access_token'];
			} else {
				return false;
			}
		} catch (\Exception $e) {
			Log::error($e->getMessage());
			return false;
		}
    }
}
