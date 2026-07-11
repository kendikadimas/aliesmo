<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Normalize path — hindari double 'storage/' jika Filament menyimpan path lengkap
        $path = $this->path;
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8); // strip leading 'storage/'
        }

        $url = str_starts_with($path, 'http') ? $path : asset('storage/' . $path);

        return [
            'id'         => $this->id,
            'path'       => $url,
            'raw_path'   => $this->path, // untuk debugging — bisa dilihat di Network tab
            'sort_order' => $this->sort_order,
        ];
    }
}
