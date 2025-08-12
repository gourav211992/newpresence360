<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MaintWoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('plant.maint_wo.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('plant.maint_wo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Add your store logic here
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('plant.maint_wo.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('plant.maint_wo.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Add your update logic here
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Add your delete logic here
    }
}
