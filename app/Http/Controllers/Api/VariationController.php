<?php

namespace App\Http\Controllers\Api;

use App\Models\Variation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ResponseTrait;

class VariationController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $variations = Variation::all();
        return $this->success($variations);
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
        $variation = Variation::findOrFail($id);
        return $this->success($variation);
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
