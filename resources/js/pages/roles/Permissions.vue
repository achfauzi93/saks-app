<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";

import { buttonVariants } from "@/components/ui/button";
import AppLayout from "@/layouts/AppLayout.vue";
import Input from "@/components/ui/input/Input.vue";
import { Delete, Search } from "lucide-vue-next";

const breadcrumbs = [
  {
    title: "Roles",
    href: "/roles",
  },
  {
    title: "Permissions",
    href: "/roles/permissions",
  },
];

const props = defineProps({
  permissions: Object,
  searchTerm: Object,
});

const form = useForm({
  search: props.searchTerm.search || "",
});

const handleSearch = () => {
  form.get(route("roles.permissions"), {
    search: form.search,
    preserveState: true,
    preserveScroll: true,
  });
};

const clearSearch = () => {
  form.search = "";
  handleSearch();
};
</script>

<template>
  <Head title="Roles" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="flex justify-between">
        <h1 class="text-xl font-semibold">List Permissions</h1>
        <div class="flex items-center gap-2">
          <Link
            :href="route('roles.index')"
            :class="buttonVariants({ variant: 'default', size: 'sm' })"
            title="Kembali"
          >
            Kembali
          </Link>
        </div>
      </div>
      <div class="flex justify-between">
        <form @submit.prevent="handleSearch" class="flex items-center gap-2">
          <div class="relative w-full sm:max-w-xs items-center">
            <Input
              id="search"
              type="text"
              placeholder="Search..."
              class="pl-10"
              v-model="form.search"
            />
            <span
              class="absolute start-0 inset-y-0 flex items-center justify-center px-2"
            >
              <Search class="size-4 text-muted-foreground" />
            </span>
            <span class="absolute end-0 inset-y-0 flex items-center pr-2">
              <Delete
                class="size-4 text-muted-foreground cursor-pointer"
                :class="form.search ? '' : 'hidden'"
                @click="clearSearch"
              />
            </span>
          </div>
        </form>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4">
        <div
          v-for="(permissions, moduleName) in props.permissions"
          :key="moduleName"
          class="border rounded-lg p-4 shadow-sm"
        >
          <h2 class="font-semibold mb-2 text-lg capitalize">{{ moduleName }}</h2>
          <ul class="list-disc list-inside">
            <li v-for="permission in permissions" :key="permission.id">
              {{ permission.name }}
            </li>
          </ul>
        </div>
      </div>

      <!-- {{ props.permissions }} -->
    </div>
  </AppLayout>
</template>
