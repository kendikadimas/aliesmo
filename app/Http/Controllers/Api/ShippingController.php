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
            'weight' => 'required|integer|min:1|max:100000',
        ]);

        $origin = config('services.rajaongkir.origin_city', 39);

        $costs = $this->rajaOngkir->getAllShippingCosts(
            (int) $origin,
            (int) $request->destination,
            (int) $request->weight,
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

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        return response()->json([
            'data' => $this->rajaOngkir->searchDestinations($request->q),
        ]);
    }
}
