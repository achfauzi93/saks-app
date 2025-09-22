<script setup>
import { CheckCircle2, XCircle } from 'lucide-vue-next';

defineProps({
    isActive: {
        type: Boolean,
        required: true,
    },
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
            isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
        ]"
    >
        <CheckCircle2 v-if="isActive" class="mr-1 h-3 w-3" />
        <XCircle v-else class="mr-1 h-3 w-3" />
        {{ isActive ? 'Aktif' : 'Tidak Aktif' }}
    </span>
</template>
