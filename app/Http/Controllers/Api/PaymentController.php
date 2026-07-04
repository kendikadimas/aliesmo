<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Midtrans callback endpoint — dinonaktifkan karena payment sudah via WhatsApp.
     * Endpoint ini sengaja di-abort agar tidak bisa disalahgunakan.
     * JANGAN log $request->all() — bisa mengandung data sensitif kartu/token Midtrans.
     */
    public function callback(Request $request)
    {
        Log::warning('Payment callback endpoint hit — endpoint disabled (WA flow active)', [
            'ip'     => $request->ip(),
            'method' => $request->method(),
            'at'     => now()->toIso8601String(),
        ]);

        abort(410, 'Payment callback endpoint is disabled.');
    }
}
