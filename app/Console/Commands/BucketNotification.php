<?php

namespace App\Console\Commands;

use App\Commonhelper;
use App\Helpers\NotificationHelper;
use App\Models\Notification;
use Illuminate\Console\Command;
use App\Models\User;
use Log;

class BucketNotification extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bucket:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $usersWithBucketSteps = User::with([
                'userBucketListStep' => function ($query) use ($today, $tomorrow) {
                    $query->where('is_completed', 0)
                        ->where(function ($subQuery) use ($today, $tomorrow) {
                            $subQuery->whereDate('due_date', $today)
                                ->orWhereDate('due_date', $tomorrow);
                        })
                        ->orderBy('step', 'asc');

                },
                'tokens'
            ])
            ->where('status', 1)
            ->whereHas('userBucketListStep', function ($query) use ($today, $tomorrow) {
                $query->where('is_completed', 0)
                    ->where(function ($subQuery) use ($today, $tomorrow) {
                        $subQuery->whereDate('due_date', $today)
                            ->orWhereDate('due_date', $tomorrow);
                    });
            })
            ->get();

        foreach ($usersWithBucketSteps as $user) {
            $firebaseTokens = $user->tokens->pluck('name')->unique()->toArray();
            $uniqueCombinations = [];

            $userLanguage = $user->lang;
            foreach ($user->userBucketListStep as $step) {
                $title = Commonhelper::getBucketName($step->user_bucket_list_id);

                $key = "{$step->user_id}_{$step->user_bucket_list_id}";

                if (!isset($uniqueCombinations[$key])) {
                    $uniqueCombinations[$key] = true;

                    if($userLanguage == 'ko'){
                        $dueDay = ($today == $step->due_date) ? '오늘' : '내일';
                        $message =  " ". $title." 버킷 목록의 ". $step->step ." 단계 마감일은 " . $dueDay ." 입니다. 확인하고 완료해주세요";
                    } else {
                        $dueDay = ($today == $step->due_date) ? 'today' : 'tomorrow';
                        $message = "Your step ". $step->step ." in ". $title." bucket list is due " . $dueDay .". Please check and complete";
                    }

                    $notification_data =  [
                        'notification_type' => 'bucket_list',
                        'id' => (string) $step->user_bucket_list_id,
                        // 'id' => '26',
                    ];

                    foreach ($firebaseTokens as $firebaseToken) {
                        // $fields['to'] = 'cKJ5PadFRi-5n91uBNb-Ah:APA91bH0wlR_HbsN3i1jCqcFlADXugjIQKMNSU-gqCB5bdIB3zQr1Ov2gRcmyqCJFymFROftJlPPG2suHQ62cC6r15QKtLkx_7aI6uYYbsFjARZTAhvtjo4zpK2RxaeJklGk-ZSro8Qu';
                        $challenge_payload = ['title' => $title, 'body' => $message, 'notification_data' => $notification_data];

                        $response = NotificationHelper::sendPushNotification($firebaseToken, $challenge_payload);

                        if ($response == true) {
                            $dataNotification = new Notification();
                            $dataNotification->bucket_id = $step->user_bucket_list_id;
                            $dataNotification->user_id = $step->user_id;
                            $dataNotification->title = $title;
                            $dataNotification->description = $message;
                            $dataNotification->notification_type = 'bucket_list';
                            $dataNotification->save();

                            print_r("Notification sent successfully");
                        } else {
                            print_r("Notification sending failed");
                        }
                    }
                }
            }
        }
        return 0;
    }
}
