<?php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $maxProducts = 30;
        if (Product::count() >= $maxProducts) {
            abort(403, "Anda telah mencapai batas maksimal {$maxProducts} produk. Untuk upgrade, hubungi 085196811722 (WhatsApp).");
        }
        return $data;
    }
}
