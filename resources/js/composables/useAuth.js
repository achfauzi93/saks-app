// resources/js/Composables/useAuth.js

import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export default function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user || null);
    const permissions = computed(() => page.props.auth?.permissions || []);
    const roles = computed(() => page.props.auth?.roles || []);
    const activeAcademicYear = computed(() => page.props.activeAcademicYear || null);

    const isSuperAdmin = computed(() => roles.value.includes('super-admin'));

    // Untuk Cek apakah user punya kelas binaan di tahun ajaran aktif
    const hasHomeroomClassInActiveYear = computed(() => {
        if (!user.value || !activeAcademicYear.value) return false;
        if (!user.value.homeroomClasses) return false;

        return user.value.homeroomClasses.some((classroom) => classroom.academic_year_id === activeAcademicYear.value.id);
    });

    const can = (permission) => {
        // super-admin bypass all permissions
        if (isSuperAdmin.value) return true;
        return permissions.value.includes(permission);
    };

    const is = (role) => {
        return roles.value.includes(role);
    };

    const isAny = (roleArray) => {
        return roleArray.some((r) => roles.value.includes(r));
    };

    return {
        user,
        roles,
        permissions,
        can,
        is,
        isAny,
        isSuperAdmin,
        hasHomeroomClassInActiveYear,
        activeAcademicYear,
    };
}
