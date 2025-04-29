<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;

class OfficeLocationController extends Controller
{
    /**
     * Display a listing of the office locations.
     */
    public function index()
    {
        $locations = OfficeLocation::paginate(10);
        return view('admin.locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new office location.
     */
    public function create()
    {
        return view('admin.locations.create');
    }

    /**
     * Store a newly created office location in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        OfficeLocation::create([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.locations.index')->with('success', 'Office location created successfully.');
    }

    /**
     * Show the form for editing the specified office location.
     */
    public function edit(OfficeLocation $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    /**
     * Update the specified office location in storage.
     */
    public function update(Request $request, OfficeLocation $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        $location->update([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.locations.index')->with('success', 'Office location updated successfully.');
    }

    /**
     * Remove the specified office location from storage.
     */
    public function destroy(OfficeLocation $location)
    {
        $location->delete();
        return redirect()->route('admin.locations.index')->with('success', 'Office location deleted successfully.');
    }
}
