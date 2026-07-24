<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofController extends Controller
{
    /**
     * Stream bukti bayar dari disk private — hanya admin (Filament auth).
     */
    public function show(Order $order): StreamedResponse
    {
        $path = $order->payment?->proof_image;
        abort_unless($path, 404);

        // Prefer private disk; fallback public for proof lama sebelum migrasi
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->response($path);
            }
        }

        abort(404);
    }
}
