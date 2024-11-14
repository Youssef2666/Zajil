<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\ShipmentMethod;
use App\Http\Controllers\Controller;

class ShipmentMethodController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $shipmentMethods = ShipmentMethod::all();
        return $this->success($shipmentMethods);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $shipmentMethod = ShipmentMethod::find($id);
        return $this->success($shipmentMethod);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
