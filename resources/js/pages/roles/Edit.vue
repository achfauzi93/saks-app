<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import RoleForm from "./Form.vue";
import { toast } from "vue-sonner";
import { buttonVariants } from "@/components/ui/button";
import { ArrowLeft } from "lucide-vue-next";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
  role: Object,
  rolePermissions: Array,
  permissions: Object,
});

const breadcrumbs = [
  {
    title: "Roles",
    href: "/roles",
  },
  {
    title: "Update",
    href: "/roles/" + props.role.id + "/edit",
  },
];

const handleSubmit = (form) => {
  form.put(route("roles.update", props.role.id), {
    onSuccess: () => {
      toast.success("Role berhasil diupdate");
    },
    onError: () => {
      toast.error("Gagal, silahkan coba lagi");
    },
  });
};
</script>

<template>
  <AppLayout title="Edit Role" :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="flex justify-between">
        <h1 class="text-xl font-semibold mb-4">Update Role</h1>
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
          :role="role"
          :role-permissions="rolePermissions"
          :permissions="permissions"
          :onSubmit="handleSubmit"
          submit-label="Update"
        />
      </div>
    </div>
  </AppLayout>
</template>
