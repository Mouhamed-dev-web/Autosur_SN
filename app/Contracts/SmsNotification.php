<?php

namespace App\Contracts;

interface SmsNotification
{
    public function toSms(object $notifiable): string;
}