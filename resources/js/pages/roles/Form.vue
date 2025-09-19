<!-- Pages/Roles/Form.vue -->
<script setup>
import { useForm } from "@inertiajs/vue3";
import Input from "@/components/ui/input/Input.vue";
import Checkbox from "@/components/ui/checkbox/Checkbox.vue";
import { watch } from "vue";
import Button from "@/components/ui/button/Button.vue";
import { LoaderCircle } from "lucide-vue-next";

const props = defineProps({
  role: {
    type: Object,
    default: () => ({ name: "" }),
  },
  rolePermissions: {
    type: Array,
    default: () => [],
  },
  permissions: {
    type: Object,
    required: true,
  },
  submitLabel: {
    type: String,
    default: "Save",
  },
  onSubmit: Function,
});

const form = useForm({
  name: props.role.name || "",
  permissions: props.rolePermissions || [],
});

watch(
  () => props.rolePermissions,
  (val) => {
    form.permissions = val;
  }
);

const togglePermission = (id) => {
  if (form.permissions.includes(id)) {
    form.permissions = form.permissions.filter((p) => p !== id);
  } else {
    form.permissions.push(id);
  }
};
</script>

<template>
  <form @submit.prevent="onSubmit(form)">
    <div class="mb-4">
      <label for="name" class="block text-sm font-medium">Role Name</label>
      <Input v-model="form.name" id="name" type="text" class="mt-1 w-full" autofocus />
      <div v-if="form.errors.name" class="text-sm text-red-500">
        {{ form.errors.name }}
      </div>
    </div>

    <div class="space-y-4">
      <h2 class="font-semibold">Permissions</h2>
      <div
        v-for="(group, module) in permissions"
        :key="module"
        class="border p-3 rounded shadow-md"
      >
        <h3 class="text-sm font-bold mb-2 capitalize">
          Modul : {{ module.replace("-", " ") }}
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
          <label
            v-for="permission in group"
            :key="permission.id"
            class="flex items-center gap-2"
          >
            <Checkbox
              :modelValue="form.permissions.includes(permission.id)"
              @update:modelValue="() => togglePermission(permission.id)"
            />
            <span class="text-sm">{{ permission.name }}</span>
          </label>
        </div>
      </div>
    </div>

    <div class="mt-6">
      <Button type="submit" variant="default" size="sm">
        <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
        {{ form.processing ? "Memproses..." : submitLabel }}
      </Button>
    </div>
  </form>
</template>
