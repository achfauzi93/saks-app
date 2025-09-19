<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import useAuth from '@/composables/useAuth';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';


defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const { can } = useAuth();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Main Menu</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title" v-show="can(item.can)"
            >
                <SidebarMenuButton as-child :is-active="page.url.startsWith(item.href)" :tooltip="item.title">
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
