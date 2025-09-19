<script setup>
import useAuth from "@/composables/useAuth";
import { buttonVariants } from "@/components/ui/button";
import Button from "@/components/ui/button/Button.vue";
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
    <TableCaption>A list of your recent roles.</TableCaption>
    <TableHeader>
      <TableRow>
        <TableHead class="w-[10px]">No.</TableHead>
        <TableHead> Nama Role </TableHead>
        <TableHead>Permissions</TableHead>
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
          <ul>
            <li v-for="permission in item.permissions" :key="permission.id">
              - {{ permission.name }}
            </li>
          </ul>
        </TableCell>
        <TableCell class="text-right space-x-1">
          <Link
            v-if="can('show-roles')"
            :href="route('roles.show', item.id)"
            :class="buttonVariants({ size: 'sm', variant: 'show' })"
            >Show</Link
          >
          <Link
            v-if="can('edit-roles')"
            :href="route('roles.edit', item.id)"
            :class="buttonVariants({ size: 'sm', variant: 'edit' })"
            >Edit</Link
          >
          <Button
            v-if="can('delete-roles')"
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
