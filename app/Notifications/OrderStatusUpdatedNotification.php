<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
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

        $statusLabels = [
            'pending'    => 'Menunggu Pembayaran',
            'paid'       => 'Pembayaran Dikonfirmasi',
            'processing' => 'Sedang Diproses',
            'shipped'    => 'Sedang Dikirim',
            'completed'  => 'Pesanan Selesai',
            'cancelled'  => 'Pesanan Dibatalkan',
            'expired'    => 'Pesanan Kedaluwarsa',
        ];

        $statusLabel = $statusLabels[$order->status->value] ?? $order->status->value;

        $messages = [
            'paid'       => 'Pembayaran kamu sudah kami terima. Pesanan akan segera diproses.',
            'processing' => 'Pesanan kamu sedang kami proses. Tunggu sebentar ya!',
            'shipped'    => 'Pesanan kamu sudah dikirim! Cek no. resi di halaman pesanan.',
            'completed'  => 'Pesanan kamu sudah selesai. Terima kasih sudah belanja di ' . $appName . '!',
            'cancelled'  => 'Pesanan kamu telah dibatalkan. Hubungi kami jika ada pertanyaan.',
        ];

        $bodyMessage = $messages[$order->status->value] ?? "Status pesanan kamu telah diperbarui menjadi: {$statusLabel}.";

        return (new MailMessage)
            ->subject("[{$appName}] Status Pesanan #{$order->order_number}: {$statusLabel}")
            ->greeting("Halo, {$order->customer_name}!")
            ->line($bodyMessage)
            ->line("**No. Pesanan:** {$order->order_number}")
            ->line("**Status:** {$statusLabel}")
            ->action('Lihat Status Pesanan', $appUrl . '/order/' . $order->order_number)
            ->line("Ada pertanyaan? Hubungi kami via WhatsApp.")
            ->salutation("Salam hangat,\nTim {$appName}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_number' => $this->order->order_number,
            'status'       => $this->order->status->value,
        ];
    }
}
