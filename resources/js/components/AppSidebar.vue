<script setup>
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Calendar,
    ClipboardPlus,
    Folder,
    GraduationCap,
    HomeIcon,
    KeyIcon,
    LayoutGrid,
    Presentation,
    Users,
    Warehouse,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();

const user = page.props.auth?.user;
const activeAcademicYear = page.props.activeAcademicYear;

// Cek apakah user adalah walas di tahun ajaran aktif
const isHomeroomTeacherInActiveYear = computed(() => {
    if (!user || !activeAcademicYear) return false;
    if (!user.homeroomClasses) return false;

    return user.homeroomClasses.some((classroom) => classroom.academic_year_id === activeAcademicYear.id);
});

const mainNavItems = [
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
    // { title: 'Assign Kelas', href: '/student-class-assignments', icon: Handshake, can: 'view-student-class-assignments' },
    { title: 'Pelanggaran Siswa', href: '/student-violations', icon: ClipboardPlus, can: 'view-student-violations' },
    {
        title: 'Absensi',
        href: '/attendances',
        icon: Presentation, // Pastikan ikon ini diimpor
        can: 'view-attendances', // Sesuaikan dengan permission kamu, atau hapus jika tidak pakai
    },
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

const footerNavItems = [
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
            <!-- Menu Walikelas — hanya muncul jika user walas di tahun ajaran aktif -->
            <SidebarGroup v-if="isHomeroomTeacherInActiveYear" class="px-2 py-0">
                <SidebarGroupLabel>Menu Walikelas</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child tooltip="Dashboard Walikelas" :is-active="page.url.startsWith('/homeroom')">
                            <Link :href="route('homeroom.index')">
                                <HomeIcon class="size-4" />
                                <span>Dashboard Walikelas</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
