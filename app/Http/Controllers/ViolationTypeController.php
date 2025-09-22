<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreViolationTypeRequest;
use App\Http\Requests\UpdateViolationTypeRequest;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ViolationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ViolationType::query();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $violationTypes = $query->latest()->paginate(10);

        return Inertia::render('ViolationTypes/Index', [
            'violationTypes' => $violationTypes,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreViolationTypeRequest $request)
    {
        $validated = $request->validated();

        ViolationType::create($validated);

        return redirect()->route('violation-types.index')->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ViolationType $violationType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ViolationType $violationType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateViolationTypeRequest $request, ViolationType $violationType)
    {
        $validated = $request->validated();

        $violationType->update($validated);

        return redirect()->route('violation-types.index')->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ViolationType $violationType)
    {
        $violationType->delete();

        return redirect()->route('violation-types.index')->with('success', 'Jenis pelanggaran berhasil dihapus.');
    }
}