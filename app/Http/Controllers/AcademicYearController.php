<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicYearRequest as AcademicYearStoreRequest;
use App\Http\Requests\UpdateAcademicYearRequest as AcademicYearUpdateRequest;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $academicYears = AcademicYear::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('AcademicYears/Index', [
            'academicYears' => $academicYears,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('AcademicYears/Create');
    }

    public function store(AcademicYearStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        // Jika yang baru dibuat adalah aktif, nonaktifkan yang lain
        if ($data['is_active']) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        AcademicYear::create($data);

        return redirect()->route('academic-years.index')->with('success', 'Tahun Akademik berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear): \Inertia\Response
    {
        return Inertia::render('AcademicYears/Edit', [
            'academicYear' => $academicYear,
        ]);
    }

    public function update(AcademicYearUpdateRequest $request, AcademicYear $academicYear): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        if ($data['is_active'] === false && $academicYear->is_active === true) {
            return to_route('academic-years.index')->withError('Tidak bisa menonaktifkan tahun akademik yang sedang aktif. Pilih tahun akademik lain untuk diaktifkan terlebih dahulu.');
        }
        // Jika yang diupdate menjadi aktif, nonaktifkan yang lain
        if ($data['is_active'] && !$academicYear->is_active) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        $academicYear->update($data);

        return redirect()->route('academic-years.index')->with('success', 'Tahun Akademik berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): \Illuminate\Http\RedirectResponse
    {
        // Cek apakah ini tahun akademik yang aktif
        if ($academicYear->is_active) {
            return redirect()->route('academic-years.index')->with('error', 'Tidak bisa menghapus tahun akademik yang sedang aktif.');
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')->with('success', 'Tahun Akademik berhasil dihapus.');
    }
}