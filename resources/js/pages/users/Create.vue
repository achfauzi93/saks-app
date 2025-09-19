<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import UserForm from "./Form.vue";
import { Link, Head } from "@inertiajs/vue3";
import { ArrowLeft } from "lucide-vue-next";
import { buttonVariants } from "@/components/ui/button";
import { toast } from "vue-sonner";

defineProps({
  roles: Object,
});

const urlName = "users";

const breadcrumbs = [
  {
    title: "Pengguna",
    href: "/users",
  },
  {
    title: "Tambah Pengguna",
  },
];

const handleSubmit = (form) => {
  form.post(route(`${urlName}.store`), {
    onSuccess: () => {
      toast.success("Data berhasil dibuat");
    },
    onError: () => {
      toast.error("Gagal, silahkan coba lagi");
    },
  });
};
</script>

<template>
  <Head title="Tambah Pengguna" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="flex justify-between">
        <h1 class="text-xl font-semibold mb-4">Tambah Pengguna</h1>
        <Link
          :href="route(`${urlName}.index`)"
          :class="buttonVariants({ variant: 'default', size: 'sm' })"
          title="Kembali"
        >
          <ArrowLeft class="w-4 h-4" /> Back
        </Link>
      </div>
      <div class="max-w-lg mx-auto w-full sm:shadow-2xl sm:p-6 rounded-xl">
        <UserForm :roles="roles" :onSubmit="handleSubmit" submit-label="Simpan" />
      </div>
    </div>
  </AppLayout>
</template>
