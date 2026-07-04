<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private \App\Models\Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $appName = config('app.name', 'Aliesmo');
        $appUrl = config('app.url');

        $mail = (new MailMessage)
            ->subject("Konfirmasi Pesanan #{$order->order_number} - {$appName}")
            ->greeting("Halo, {$order->customer_name}!")
            ->line("Terima kasih telah berbelanja di {$appName}. Pesanan kamu sudah kami terima.")
            ->line("**Order #{$order->order_number}**")
            ->line('---');

        foreach ($order->items as $item) {
            $mail->line("• {$item->product_name} × {$item->quantity} — Rp" . number_format($item->subtotal, 0, ',', '.'));
        }

        $mail->line('---')
            ->line("Subtotal: Rp" . number_format($order->subtotal, 0, ',', '.'))
            ->line("Ongkir: Rp" . number_format($order->shipping_cost, 0, ',', '.'))
            ->line("**Total: Rp" . number_format($order->total, 0, ',', '.') . "**")
            ->line('---')
            ->line("**Alamat Pengiriman:**")
            ->line($order->shipping_address)
            ->line('---')
            ->line('Kami akan segera memproses pesananmu setelah pembayaran dikonfirmasi.')
            ->action('Lihat Status Pesanan', $appUrl . '/order/' . $order->order_number)
            ->line("Ada pertanyaan? Hubungi kami via WhatsApp di +{$this->getWhatsappNumber()}.")
            ->salutation("Salam hangat,\nTim {$appName}");

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_number' => $this->order->order_number,
            'total'        => $this->order->total,
        ];
    }

    private function getWhatsappNumber(): string
    {
        return config('services.whatsapp.number', '6285196811722');
    }
}
