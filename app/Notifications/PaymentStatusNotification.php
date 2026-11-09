<?php

namespace App\Notifications;

use App\Contracts\SmsNotification;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentStatusNotification extends Notification implements ShouldQueue, SmsNotification
{
    use Queueable;

    public function __construct(public string $reference, public string $message) {}

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
            'title' => 'Paiement',
            'message' => $this->message,
            'url' => route('client.payments.result', $this->reference),
        ];
    }
}