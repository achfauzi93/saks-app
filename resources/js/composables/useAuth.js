// resources/js/Composables/useAuth.js

import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export default function useAuth() {
    const page = usePage()

    const user = computed(() => page.props.auth?.user || null)
    const permissions = computed(() => page.props.auth?.permissions || [])
    const roles = computed(() => page.props.auth?.roles || [])

    const isSuperAdmin = computed(() => roles.value.includes('super-admin'))

    const can = (permission) => {
        // super-admin bypass all permissions
        if (isSuperAdmin.value) return true
        return permissions.value.includes(permission)
    }

    const is = (role) => {
        return roles.value.includes(role)
    }

    const isAny = (roleArray) => {
        return roleArray.some(r => roles.value.includes(r))
    }

    return {
        user,
        roles,
        permissions,
        can,
        is,
        isAny,
        isSuperAdmin,
    }
}
