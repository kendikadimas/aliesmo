<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order   = $this->order;
        $appName = config('app.name', 'Aliesmo');
        $appUrl  = config('app.url');

        $mail = (new MailMessage)
            ->subject("[{$appName}] Pesanan #{$order->order_number} Sudah Dikirim!")
            ->greeting("Halo, {$order->customer_name}!")
            ->line("Yeay! Pesanan kamu sudah dalam perjalanan menuju alamatmu.")
            ->line("**No. Pesanan:** {$order->order_number}")
            ->line("**Kurir:** " . ($order->courier ?? '-'))
            ->line("**No. Resi:** " . ($order->tracking_number ?? '-'));

        if ($order->tracking_url) {
            $mail->action('Cek Resi Sekarang', $order->tracking_url);
        } else {
            $mail->action('Lihat Status Pesanan', $appUrl . '/order/' . $order->order_number);
        }

        $mail->line("Pesanan akan tiba dalam estimasi 1-5 hari kerja tergantung lokasi.")
            ->line("Ada pertanyaan? Hubungi kami via WhatsApp.")
            ->salutation("Salam hangat,\nTim {$appName}");

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_number'    => $this->order->order_number,
            'tracking_number' => $this->order->tracking_number,
            'courier'         => $this->order->courier,
        ];
    }
}
