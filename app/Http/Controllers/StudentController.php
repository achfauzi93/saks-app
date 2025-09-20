<?php

namespace App\Http\Controllers;

use App\Imports\DapodikImport;
use App\Imports\StudentsImport;
use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Student::query();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('student_id', 'like', '%' . $search . '%');
        }

        $students = $query
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(10);

        return Inertia::render('Students/Index', [
            'students' => $students,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|unique:students,student_id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        Student::create($validated);

        return redirect()->route('students.index')->with('flash', [
            'message' => 'Student created successfully!',
            'type' => 'success',
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|unique:students,student_id,' . $student->id,
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:students,email,' . $student->id,
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $student->update($validated);

        return redirect()->route('students.index')->with('flash', [
            'message' => 'Student updated successfully!',
            'type' => 'success',
        ]);
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')->with('flash', [
            'message' => 'Student deleted successfully!',
            'type' => 'success',
        ]);
    }

    // Import
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        $import = new StudentsImport;

        try {
            // Reset counter
            StudentsImport::$rowCount = 0;

            // Import data
            Excel::import($import, $request->file('file'));

            // Ambil jumlah baris yang diproses
            $processedCount = StudentsImport::$rowCount;

            // Ambil failure (jika ada)
            $failures = $import->failures();
            $failureCount = count($failures);

            if ($failureCount > 0) {
                $errorMessages = [];
                foreach ($failures as $failure) {
                    $row = $failure->row();
                    $attribute = $failure->attribute();
                    $errors = $failure->errors();

                    foreach ($errors as $error) {
                        $errorMessages[] = "Baris {$row} ({$attribute}): {$error}";
                    }
                }

                return to_route('students.index')->withError(
                    "Import sebagian gagal. {$failureCount} baris error: \n" . implode("\n", $errorMessages)
                );
            }

            // Jika semua sukses
            return to_route('students.index')->with('success', "Berhasil mengimpor {$processedCount} data siswa.");
        } catch (\Exception $e) {
            return to_route('students.index')->withError('Import gagal: ' . $e->getMessage());
        }
    }

    // public function importDapodik(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,xls|max:2048',
    //     ]);

    //     try {
    //         Excel::import(new DapodikImport, $request->file('file'));

    //         return to_route('students.index')->with('success', 'Data Dapodik berhasil diimpor: siswa, kelas, dan assignment.');
    //     } catch (\Exception $e) {
    //         return to_route('students.index')->withError('Import gagal: ' . $e->getMessage());
    //     }
    // }

    public function importDapodik(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return to_route('students.index')->withError('Tidak ada tahun ajaran aktif. Silakan atur tahun ajaran aktif terlebih dahulu di menu Tahun Akademik.');
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $import = new DapodikImport();
            Excel::import($import, $request->file('file'));

            // Hitung siswa yang dinonaktifkan
            $deactivatedCount = Student::where('is_active', false)
                ->where('updated_at', '>=', now()->subMinute())
                ->count();

            $message = 'Data Dapodik berhasil diimpor: siswa, kelas, dan assignment.';
            if ($deactivatedCount > 0) {
                $message .= " {$deactivatedCount} siswa yang tidak ada di file telah dinonaktifkan.";
            }

            return to_route('students.index')->with('success', $message);
        } catch (\Exception $e) {
            return to_route('students.index')->withError('Import gagal: ' . $e->getMessage());
        }
    }
}