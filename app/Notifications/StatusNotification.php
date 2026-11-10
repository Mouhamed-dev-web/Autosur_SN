<?php

namespace App\Notifications;

use App\Contracts\SmsNotification;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StatusNotification extends Notification implements ShouldQueue, SmsNotification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->notify_sms && $notifiable->phone) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function toSms(object $notifiable): string
    {
        return 'AutoSur.sn : '.$this->message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}