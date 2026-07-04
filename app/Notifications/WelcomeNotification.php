<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private \App\Models\User $user
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'Aliesmo');
        $appUrl  = config('app.url');

        return (new MailMessage)
            ->subject("Selamat datang di {$appName}!")
            ->greeting("Halo, {$this->user->name}!")
            ->line("Selamat bergabung di {$appName}. Kami senang kamu ada di sini!")
            ->line("Sekarang kamu bisa menjelajahi koleksi kemeja premium kami dan mulai belanja.")
            ->action('Mulai Belanja', $appUrl)
            ->line("Ada pertanyaan? Jangan ragu untuk menghubungi kami.")
            ->salutation("Salam hangat,\nTim {$appName}");
    }

    public function toArray(object $notifiable): array
    {
        return ['user_id' => $this->user->id];
    }
}
