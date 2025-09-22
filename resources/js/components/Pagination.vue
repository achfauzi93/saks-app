<!-- resources/js/Components/Pagination.vue -->
<script setup>
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    meta: {
        type: Object,
        required: true,
    },
    links: {
        type: Array,
        required: true,
    },
    mobileSimple: {
        type: Boolean,
        default: true,
    },
    queryParams: {
        type: Object,
        default: () => ({}),
    },
});

// Filter links untuk mobile
const visibleLinks = computed(() => {
    if (!props.mobileSimple || window.innerWidth >= 768) {
        return props.links;
    }

    return props.links.filter((link) => {
        if (link.label.includes('Previous') || link.label.includes('Next')) return true;
        if (link.active) return true;
        if (link.label === '...') return true;
        return false;
    });
});

// Fungsi untuk menambahkan queryParams ke URL pagination
const paginateWithQuery = (url) => {
    if (!url) return;

    // Parse URL tujuan
    const urlObj = new URL(url, window.location.origin);

    // Tambahkan semua queryParams dari props
    Object.keys(props.queryParams).forEach((key) => {
        const value = props.queryParams[key];
        if (value !== null && value !== undefined && value !== '') {
            urlObj.searchParams.set(key, value);
        } else {
            urlObj.searchParams.delete(key);
        }
    });

    // Kirim request dengan preserve state & scroll
    router.get(urlObj.toString(), {}, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <div class="mt-4 flex flex-col items-center justify-between gap-4 md:flex-row">
        <!-- Info jumlah data -->
        <p class="text-sm text-muted-foreground">Tampil {{ meta.from }} sampai {{ meta.to }} dari {{ meta.total }} data</p>

        <!-- Tombol pagination -->
        <div class="flex flex-wrap items-center justify-center gap-1 md:gap-2">
            <Button
                v-for="(link, index) in visibleLinks"
                :key="index"
                :variant="link.active ? 'default' : 'outline'"
                :disabled="!link.url"
                size="sm"
                class="whitespace-nowrap"
                @click="paginateWithQuery(link.url)"
                v-html="link.label"
            />
        </div>
    </div>
</template>
