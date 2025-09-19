<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import RoleForm from "./Form.vue";
import { Link } from "@inertiajs/vue3";
import { ArrowLeft } from "lucide-vue-next";
import { buttonVariants } from "@/components/ui/button";
import { toast } from "vue-sonner";

defineProps({
  permissions: Object,
});

const breadcrumbs = [
  {
    title: "Roles",
    href: "/roles",
  },
  {
    title: "Create",
    href: "/roles/create",
  },
];

const handleSubmit = (form) => {
  form.post(route("roles.store"), {
    onSuccess: () => {
      toast.success("Role berhasil dibuat");
    },
    onError: () => {
      toast.error("Gagal, silahkan coba lagi");
    },
  });
};
</script>

<template>
  <AppLayout title="Create Role" :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="flex justify-between">
        <h1 class="text-xl font-semibold mb-4">Create Role</h1>
        <Link
          :href="route('roles.index')"
          :class="buttonVariants({ variant: 'default', size: 'sm' })"
          title="Kembali"
        >
          <ArrowLeft class="w-4 h-4" /> Back
        </Link>
      </div>
      <div class="max-w-lg mx-auto w-full sm:shadow-2xl sm:p-6 rounded-xl">
        <RoleForm
          :permissions="permissions"
          :onSubmit="handleSubmit"
          submit-label="Simpan"
        />
      </div>
    </div>
  </AppLayout>
</template>
