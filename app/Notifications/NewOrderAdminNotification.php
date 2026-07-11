<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAdminNotification extends Notification implements ShouldQueue
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

        $adminUrl = $appUrl . '/admin/orders/' . $order->id;

        $mail = (new MailMessage)
            ->subject("[{$appName}] Pesanan Baru #{$order->order_number}")
            ->greeting('Ada pesanan baru masuk!')
            ->line("**No. Pesanan:** {$order->order_number}")
            ->line("**Pelanggan:** {$order->customer_name} ({$order->customer_email})")
            ->line("**No. HP:** {$order->customer_phone}")
            ->line("**Alamat:** {$order->shipping_address}")
            ->line("**Metode Bayar:** " . strtoupper(str_replace('_', ' ', $order->payment_method ?? '-')))
            ->line('---');

        foreach ($order->items as $item) {
            $variant = $item->variant_name ? " [{$item->variant_name}]" : '';
            $mail->line("• {$item->product_name}{$variant} × {$item->quantity} — Rp" . number_format($item->subtotal, 0, ',', '.'));
        }

        $mail->line('---')
            ->line("Subtotal: Rp" . number_format($order->subtotal, 0, ',', '.'))
            ->line("Ongkir: Rp" . number_format($order->shipping_cost, 0, ',', '.'))
            ->line("**Total: Rp" . number_format($order->total, 0, ',', '.') . "**")
            ->action('Lihat di Admin Panel', $adminUrl)
            ->salutation("-- {$appName} System");

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_number' => $this->order->order_number,
            'total'        => $this->order->total,
        ];
    }
}
