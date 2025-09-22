<?php

use App\Models\AcademicYear;

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