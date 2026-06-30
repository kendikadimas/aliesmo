<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(
        private RajaOngkirService $rajaOngkir
    ) {}

    public function provinces()
    {
        return response()->json([
            'data' => $this->rajaOngkir->getProvinces(),
        ]);
    }

    public function cities(Request $request, int $provinceId)
    {
        return response()->json([
            'data' => $this->rajaOngkir->getCities($provinceId),
        ]);
    }

    public function cost(Request $request)
    {
        $request->validate([
            'destination' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string|in:jne,tiki,pos',
        ]);

        $origin = config('services.rajaongkir.origin_city', 39);

        $costs = $this->rajaOngkir->getShippingCost(
            (int) $origin,
            (int) $request->destination,
            (int) $request->weight,
            $request->courier
        );

        return response()->json([
            'data' => $costs,
        ]);
    }

    public function couriers()
    {
        return response()->json([
            'data' => $this->rajaOngkir->getAvailableCouriers(),
        ]);
    }
}
