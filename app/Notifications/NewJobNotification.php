<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use App\Models\JobListing;

class NewJobNotification extends Notification
{
    use Queueable;

    public $job;

    public function __construct(JobListing $job)
    {
        $this->job = $job;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $company = $this->job->company_name ?? 'Perusahaan Dirahasiakan';
        return (new WebPushMessage)
            ->title('Loker Baru Sesuai Keahlian Anda!')
            ->icon('/favicon.svg')
            ->body($this->job->job_title . ' di ' . $company . '. Cek sekarang!')
            ->action('Lihat Loker', '/');
    }
}
