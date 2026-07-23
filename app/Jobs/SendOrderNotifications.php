<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\OrderConfirmationNotification;
use App\Notifications\NewOrderAdminNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendOrderNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        // Send customer confirmation
        try {
            if ($this->order->user_id) {
                $this->order->user->notify(new OrderConfirmationNotification($this->order));
            } else {
                Notification::route('mail', $this->order->customer_email)
                    ->notify(new OrderConfirmationNotification($this->order));
            }
            Log::debug('Order confirmation email sent', ['order' => $this->order->order_number]);
        } catch (\Throwable $e) {
            Log::error('Failed to send customer email', [
                'order' => $this->order->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        // Send admin notification
        try {
            $csEmail = config('mail.from.address', 'cs@aliesmo.id');
            Notification::route('mail', $csEmail)
                ->notify(new NewOrderAdminNotification($this->order));
            Log::debug('Admin notification sent', ['order' => $this->order->order_number]);
        } catch (\Throwable $e) {
            Log::error('Failed to send admin notification', [
                'order' => $this->order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
