<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import useAuth from "@/composables/useAuth";

import { buttonVariants } from "@/components/ui/button";
import AppLayout from "@/layouts/AppLayout.vue";
import DataTable from "./DataTable.vue";
import PaginationMeta from "@/components/PaginationMeta.vue";
import Input from "@/components/ui/input/Input.vue";
import { Delete, PlusIcon, Search } from "lucide-vue-next";
import { toast } from "vue-sonner";

const urlName = "users";

const breadcrumbs = [
  {
    title: "Pengguna",
    href: "/users",
  },
];

const props = defineProps({
  users: Object,
  searchTerm: Object,
  perPage: Number,
});

const { can } = useAuth();

const form = useForm({
  search: props.searchTerm.search || "",
  per_page: props.perPage,
});

const handleSearch = () => {
  form.get(route(urlName + ".index"), {
    search: form.search,
    preserveState: true,
    preserveScroll: true,
  });
};
const clearSearch = () => {
  form.search = "";
  handleSearch();
};
const handleNavigate = (url) => {
  form.get(url, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handleChangePerPage = (perPage) => {
  form.per_page = perPage;
  handleSearch();
};

const handleDelete = (id) => {
  if (confirm("Yakin? Data akan dihapus secara permanen")) {
    form.delete(route(urlName + ".destroy", id), {
      onSuccess: () => {
        toast.success("Data berhasil dihapus");
      },
      onError: (e) => {
        toast.error("Gagal, " + e.error);
      },
    });
  }
};
</script>

<template>
  <Head title="Pengguna" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="flex justify-between">
        <h1 class="text-xl font-semibold">Pengguna</h1>
        <div class="flex items-center gap-2">
          <Link
            v-if="can('create-users')"
            :href="route(`${urlName}.create`)"
            :class="buttonVariants({ variant: 'default', size: 'sm' })"
            title="Tambah Data"
          >
            <PlusIcon class="w-4 h-4" /> Add
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
        <div></div>
      </div>

      <div>
        <DataTable :models="users" @delete="handleDelete" />
      </div>
      <div>
        <PaginationMeta
          :models="users"
          @navigate="handleNavigate"
          @changePerPage="handleChangePerPage"
        />
      </div>
    </div>
    <pre>{{ users }}</pre>
  </AppLayout>
</template>
