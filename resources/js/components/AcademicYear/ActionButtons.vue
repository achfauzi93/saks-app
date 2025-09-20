<script setup>
import { Button } from '@/components/ui/button';
import { Pencil, Trash2 } from 'lucide-vue-next';

defineEmits(['edit', 'delete']);

defineProps({
    isActive: {
        type: Boolean,
        required: true,
    },
});
</script>

<template>
    <div class="flex justify-end space-x-2">
        <Button variant="outline" size="sm" @click="$emit('edit')">
            <Pencil class="h-4 w-4" />
        </Button>
        <Button variant="destructive" size="sm" @click="$emit('delete')" :disabled="isActive">
            <Trash2 class="h-4 w-4" />
        </Button>
    </div>
</template>
