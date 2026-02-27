<?php
// app/Http/Controllers/TierController.php

namespace App\Http\Controllers;

use App\Models\Tier;
use Illuminate\Http\Request;

class TierController extends Controller
{
    /**
     * Display a listing of the tiers.
     */
    public function index()
    {
        $tiers = Tier::latest()->get();

        return inertia('Admin/Tier/Index', [
            'tiers' => $tiers
        ]);
    }

    /**
     * Show the form for creating a new tier.
     */
    public function create()
    {
        return inertia('Admin/Tier/Create');
    }

    /**
     * Store a newly created tier in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tiers,name'
        ]);

        Tier::create($validated);

        return redirect()->route('admin.tiers.index')
            ->with('success', 'Tier created successfully.');
    }

    /**
     * Display the specified tier.
     */
    public function show(Tier $tier)
    {
        return inertia('Admin/Tier/Show', [
            'tier' => $tier
        ]);
    }

    /**
     * Show the form for editing the specified tier.
     */
    public function edit(Tier $tier)
    {
        return inertia('Admin/Tier/Edit', [
            'tier' => $tier
        ]);
    }

    /**
     * Update the specified tier in storage.
     */
    public function update(Request $request, Tier $tier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tiers,name,' . $tier->id
        ]);

        $tier->update($validated);

        return redirect()->route('admin.tiers.index')
            ->with('success', 'Tier updated successfully.');
    }

    /**
     * Remove the specified tier from storage.
     */
    public function destroy(Tier $tier)
    {
        $tier->delete();

        return redirect()->route('admin.tiers.index')
            ->with('success', 'Tier deleted successfully.');
    }
}