<?php
namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Hapus Pesanan')
                ->modalDescription('Pesanan diarsipkan (soft delete) dan hilang dari daftar. Data tetap di database. Batalkan dulu di Biteship jika shipment masih aktif.'),
        ];
    }
}
