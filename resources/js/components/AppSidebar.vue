<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Calendar, ClipboardPlus, Folder, GraduationCap, Handshake, KeyIcon, LayoutGrid, Users, Warehouse } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        can: 'view-dashboard',
    },
    {
        title: 'Tahun Akademik',
        href: '/academic-years',
        icon: Calendar,
        can: 'view-academic-years',
    },
    { title: 'Jenis Pelanggaran', href: '/violation-types', icon: BookOpen, can: 'view-violation-types' },
    { title: 'Siswa', href: '/students', icon: GraduationCap, can: 'view-students' },
    { title: 'Kelas', href: '/classrooms', icon: Warehouse, can: 'view-classrooms' },
    { title: 'Assign Kelas', href: '/student-class-assignments', icon: Handshake, can: 'view-student-class-assignments' },
    { title: 'Pelanggaran Siswa', href: '/student-violations', icon: ClipboardPlus, can: 'view-student-violations' },
    {
        title: 'Pengguna',
        href: '/users',
        icon: Users,
        can: 'view-users',
    },
    {
        title: 'Roles',
        href: '/roles',
        icon: KeyIcon,
        can: 'view-roles',
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
