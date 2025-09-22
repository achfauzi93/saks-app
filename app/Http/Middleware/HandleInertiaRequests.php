<?php

namespace App\Http\Middleware;

// Import model dan helper yang dibutuhkan
use App\Models\AcademicYear; // Pastikan ini diimpor
use App\Models\User; // Pastikan ini diimpor
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

// Pastikan helper ini sudah dibuat dan diload (app/Helpers/academic_year.php)
if (!function_exists('active_academic_year')) {
    function active_academic_year()
    {
        static $activeYear = null;
        if ($activeYear === null) {
            $activeYear = AcademicYear::where('is_active', true)->first();
        }
        return $activeYear;
    }
}

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see   https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Dapatkan user yang sedang login
        $user = $request->user();

        // Jika user ada, muat relasi homeroomClasses jika belum dimuat
        // dan filter hanya kelas di tahun ajaran aktif
        if ($user) {
            $user->loadMissing(['homeroomClasses.academicYear']); // Muat relasi jika belum ada

            // Filter koleksi homeroomClasses berdasarkan tahun ajaran aktif
            $activeYear = active_academic_year();
            if ($activeYear) {
                $filteredHomeroomClasses = $user->homeroomClasses->filter(function ($classroom) use ($activeYear) {
                    return $classroom->academic_year_id == $activeYear->id;
                })->values(); // values() untuk me-reset index array
            } else {
                $filteredHomeroomClasses = collect(); // Koleksi kosong jika tidak ada tahun aktif
            }

            // Tambahkan properti homeroomClasses yang sudah difilter ke user
            // Kita buat array sederhana agar mudah digunakan di frontend
            $userArray = array_merge(
                // Konversi user model ke array
                $user->toArray(),
                // Tambahkan homeroomClasses yang sudah difilter
                ['homeroomClasses' => $filteredHomeroomClasses]
            );
        } else {
            $userArray = null;
        }

        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                // Gunakan userArray yang sudah dimodifikasi
                'user' => $userArray,
                // Roles dan permissions tetap menggunakan lazy loading
                'roles' => fn() => $user?->roles->pluck('name'),
                'permissions' => fn() => $user?->getPermissionsViaRoles()->pluck('name'),
            ],
            // Bagikan tahun ajaran aktif secara global
            'activeAcademicYear' => active_academic_year(), // Ini sudah dibagikan oleh middleware lain juga, opsional
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error')
                    ?? ($request->session()->get('errors')?->first('error')),
                'warning' => fn() => $request->session()->get('warning'),
                'info'    => fn() => $request->session()->get('info'),
            ],
        ];
    }
}