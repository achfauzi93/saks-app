<script setup>
import useAuth from "@/composables/useAuth";
import { buttonVariants } from "@/components/ui/button";
import Button from "@/components/ui/button/Button.vue";
import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Link } from "@inertiajs/vue3";

defineProps({
  models: Object,
});

const { can } = useAuth();
</script>

<template>
  <Table>
    <TableCaption>A list of your recent users.</TableCaption>
    <TableHeader>
      <TableRow>
        <TableHead class="w-[10px]">No.</TableHead>
        <TableHead>Nama</TableHead>
        <TableHead>Email</TableHead>
        <TableHead>Role</TableHead>
        <TableHead>dibuat</TableHead>
        <TableHead class="text-right"> Aksi </TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="(item, index) in models.data" :key="item.id">
        <TableCell
          >{{
            index + 1 + (models.meta.current_page - 1) * models.meta.per_page
          }}.</TableCell
        >
        <TableCell class="font-medium">
          {{ item.name }}
        </TableCell>
        <TableCell>
          {{ item.email }}
        </TableCell>
        <TableCell>
          <Badge>{{ item.role.join(", ") }}</Badge>
        </TableCell>
        <TableCell>
          {{ item.created_at }}
        </TableCell>
        <TableCell class="text-right space-x-1">
          <Link
            v-if="can('edit-users')"
            :href="route('users.edit', item.id)"
            :class="buttonVariants({ size: 'sm', variant: 'edit' })"
            >Edit</Link
          >
          <Button
            v-if="can('delete-users')"
            variant="destructive"
            size="sm"
            @click="$emit('delete', item.id)"
            class="cursor-pointer"
            >Del</Button
          >
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>
