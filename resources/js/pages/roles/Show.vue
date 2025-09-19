<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { buttonVariants } from "@/components/ui/button";
import { ArrowLeft } from "lucide-vue-next";
import Badge from "@/components/ui/badge/Badge.vue";

defineProps({
  role: Object,
  permissions: Object,
});

const breadcrumbs = [
  {
    title: "Roles",
    href: "/roles",
  },
  {
    title: "Detail",
  },
];
</script>

<template>
  <AppLayout title="Detail Role" :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <div class="flex justify-between">
        <h1 class="text-xl font-semibold">Detail Role</h1>
        <div class="flex items-center gap-2">
          <Link
            :href="route('roles.edit', role.id)"
            :class="buttonVariants({ variant: 'default', size: 'sm' })"
          >
            Edit Role
          </Link>
          <Link
            :href="route('roles.index')"
            :class="buttonVariants({ variant: 'default', size: 'sm' })"
            title="Kembali"
          >
            <ArrowLeft class="w-4 h-4" /> Back
          </Link>
        </div>
      </div>

      <section class="rounded-xl shadow p-6 max-w-3xl mx-auto space-y-8 w-full">
        <!-- Role Info -->
        <div>
          <h2 class="text-xl font-semibold mb-2">Role Name</h2>
          <p class="text-lg font-medium">{{ role.name }}</p>
        </div>

        <!-- Permissions -->
        <div>
          <h2 class="text-xl font-semibold mb-4">Permissions</h2>

          <div
            v-if="Object.keys(permissions).length > 0"
            class="grid grid-cols-1 md:grid-cols-2 gap-6"
          >
            <div
              v-for="(group, module) in permissions"
              :key="module"
              class="border rounded-lg p-5 shadow-sm hover:shadow-xl transition-colors"
            >
              <h3 class="font-semibold uppercase tracking-wide mb-3 select-none">
                {{ module.replace(/-/g, " ") }}
              </h3>
              <ul class="flex flex-wrap gap-2">
                <li v-for="permission in group" :key="permission.id">
                  <Badge>
                    {{ permission.name }}
                  </Badge>
                </li>
              </ul>
            </div>
          </div>

          <div v-else class="text-gray-500 italic text-center py-8">
            No permissions assigned.
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
