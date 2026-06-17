<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManuFacturer;

class ManufacturerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $manufacturers = ManuFacturer::all();
        return view('admin.manufacturer.manufacturer-list', compact('manufacturers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.manufacturer.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        // Đã sửa thành f thường
        $manufacturer = ManuFacturer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        if ($manufacturer) {
            return redirect()->route('admin.manufacturer.index')->with('success', 'Manufacturer created successfully.');
        } else {
            return back()->with('error', 'Failed to create manufacturer. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $mainufacturer = ManuFacturer::find($id);
        if (!$mainufacturer) {
            return redirect()->route('admin.manufacturer.index')->with('error', 'Manufacturer not found.');
        }
        return view('admin.manufacturer.edit', compact('mainufacturer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        // Đã sửa thành find() giống hàm edit
        $manufacturer = ManuFacturer::find($id);

        if (!$manufacturer) {
            return redirect()->route('admin.manufacturer.index')->with('error', 'Manufacturer not found.');
        }

        $manufacturer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.manufacturer.index')->with('success', 'Manufacturer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $manufacturer = Manufacturer::find($id);

        if ($manufacturer) {
            $manufacturer->delete();
            return redirect()->route('admin.manufacturer.index')->with('success', 'Manufacturer deleted successfully.');
        }

        return redirect()->route('admin.manufacturer.index')->with('error', 'Manufacturer not found.');
    }
}
