<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import UserForm from "./Form.vue";
import { toast } from "vue-sonner";
import { buttonVariants } from "@/components/ui/button";
import { ArrowLeft } from "lucide-vue-next";
import { Link, Head } from "@inertiajs/vue3";

const props = defineProps({
  user: Object,
  roles: Array,
});

const urlName = "users";

const breadcrumbs = [
  {
    title: "Pengguna",
    href: "/users",
  },
  {
    title: "Update",
    href: "/users/" + props.user.id + "/edit",
  },
];

const handleSubmit = (form) => {
  form.put(route("users.update", props.user.id), {
    onSuccess: () => {
      toast.success("Data berhasil diupdate");
    },
    onError: () => {
      toast.error("Gagal, silahkan coba lagi");
    },
  });
};
</script>

<template>
  <Head title="Edit Data Pengguna" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="flex justify-between">
        <h1 class="text-xl font-semibold mb-4">Edit Data Pengguna</h1>
        <Link
          :href="route(`${urlName}.index`)"
          :class="buttonVariants({ variant: 'default', size: 'sm' })"
          title="Kembali"
        >
          <ArrowLeft class="w-4 h-4" /> Back
        </Link>
      </div>
      <div class="max-w-lg mx-auto w-full sm:shadow-2xl sm:p-6 rounded-xl">
        <UserForm
          :user="user"
          :roles="roles"
          :onSubmit="handleSubmit"
          submit-label="Update"
        />
      </div>
    </div>
  </AppLayout>
</template>
