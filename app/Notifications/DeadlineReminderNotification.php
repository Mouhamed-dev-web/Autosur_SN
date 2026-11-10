<?php

namespace App\Notifications;

use App\Contracts\SmsNotification;
use App\Models\Reminder;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Setting;

class DeadlineReminderNotification extends Notification implements ShouldQueue, SmsNotification
{
    use Queueable;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public Reminder $reminder) {}

    public function via(object $notifiable): array
    {
        $channels = ['database']; // toujours actif : c'est la trace dans l'application

        if (Setting::bool('channel_mail_enabled', true) && $notifiable->notify_mail && $notifiable->email) {
            $channels[] = 'mail';
        }

        if ($notifiable->notify_sms && $notifiable->phone) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Rappel AutoSur.sn : '.$this->reminder->subjectLabel())
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($this->reminder->message())
            ->action('Ouvrir mon espace', $this->url())
            ->salutation("L'équipe AutoSur.sn");
    }

    public function toSms(object $notifiable): string
    {
        return 'AutoSur.sn : '.$this->reminder->message();
    }

    public function toArray(object $notifiable): array
    {
        return [
            'reminder_id' => $this->reminder->id,
            'title' => $this->reminder->subjectLabel(),
            'message' => $this->reminder->message(),
            'url' => $this->url(),
        ];
    }

    private function url(): string
    {
        return $this->reminder->vehicle_id
            ? route('client.vehicles.show', $this->reminder->vehicle_id)
            : route('client.documents.index');
    }
}