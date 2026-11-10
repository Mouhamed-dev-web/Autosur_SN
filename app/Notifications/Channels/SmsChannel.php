<?php

namespace App\Notifications\Channels;

use App\Contracts\SmsGatewayInterface;
use App\Contracts\SmsNotification;
use Illuminate\Notifications\Notification;
use App\Models\Setting;

class SmsChannel
{
    public function __construct(private SmsGatewayInterface $sms) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! Setting::bool('channel_sms_enabled', true)) {
            return; // SMS désactivés depuis le back-office
        }
        if (! $notification instanceof SmsNotification) {
            return; // cette notification ne gère pas le SMS
        }

        $phone = $notifiable->phone ?? null;

        if (empty($phone)) {
            return;
        }

        $this->sms->send($phone, $notification->toSms($notifiable));
    }
}