<?php

namespace App\Jobs;

use App\Helpers\Commonhelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $toEmail;
    protected $data;
    protected $template;
    protected $subject;
    /**
     * Create a new job instance.
     */
    public function __construct($toEmail, $data, $template, $subject)
    {
        $this->toEmail = $toEmail;
        $this->data = $data;
        $this->template = $template;
        $this->subject = $subject;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('Sending email to: ' . $this->toEmail);
        Commonhelper::sendmail($this->toEmail, $this->data, $this->template, $this->subject);
    }
}
