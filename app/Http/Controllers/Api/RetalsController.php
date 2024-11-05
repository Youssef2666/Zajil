<?php

namespace App\Http\Controllers\Api;

use App\Models\Retal;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;

class RetalsController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $retals = Retal::all();
        return $this->success($retals);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $retal = Retal::find($id);
        return $this->success($retal);
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
