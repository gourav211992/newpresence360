<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MaintBomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return view('plant.maint_bom.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('plant.maint_bom.create');
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
         return view('plant.maint_bom.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
         return view('plant.maint_bom.edit');
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
