<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $appName = config('app.name', 'Aliesmo');
        $appUrl  = config('app.url');

        // Frontend reset password URL
        $resetUrl = $appUrl . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject("[{$appName}] Reset Password Akun Kamu")
            ->greeting("Halo!")
            ->line("Kami menerima permintaan reset password untuk akunmu.")
            ->line("Klik tombol di bawah untuk membuat password baru. Link ini berlaku selama **60 menit**.")
            ->action('Reset Password Sekarang', $resetUrl)
            ->line("Jika kamu tidak merasa meminta reset password, abaikan email ini. Password kamu tidak akan berubah.")
            ->salutation("Salam hangat,\nTim {$appName}");
    }
}
