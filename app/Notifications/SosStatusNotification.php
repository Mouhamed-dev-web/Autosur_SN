<?php

namespace App\Notifications;

use App\Contracts\SmsNotification;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SosStatusNotification extends Notification implements ShouldQueue, SmsNotification
{
    use Queueable;

    public function __construct(public int $sosId, public string $message) {}

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
        return 'AutoSur.sn SOS : '.$this->message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'SOS Panne',
            'message' => $this->message,
            'url' => route('client.sos.show', $this->sosId),
        ];
    }
}