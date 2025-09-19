<!-- Pages/Roles/Form.vue -->
<script setup>
import { useForm } from "@inertiajs/vue3";
import Input from "@/components/ui/input/Input.vue";
import { watch } from "vue";
import Button from "@/components/ui/button/Button.vue";
import { LoaderCircle } from "lucide-vue-next";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

const props = defineProps({
  user: {
    type: Object,
    default: () => ({ name: "", email: "", role: null }),
  },
  roles: {
    type: Array,
    default: () => [],
  },
  submitLabel: {
    type: String,
    default: "Save",
  },
  onSubmit: Function,
});

const form = useForm({
  name: props.user.name || "",
  email: props.user.email || "",
  role: props.user.roles?.[0]?.id || null, // role tunggal, bisa null jika create
  password: "",
});

watch(
  () => props.user.roles?.[0]?.id,
  (val) => {
    form.role = val;
  }
);
</script>

<template>
  <form @submit.prevent="onSubmit(form)" class="w-full">
    <div class="mb-4">
      <label for="name" class="block text-sm font-medium">Nama Lengkap</label>
      <Input v-model="form.name" id="name" type="text" class="mt-1 w-full" autofocus />
      <div v-if="form.errors.name" class="text-sm text-red-500">
        {{ form.errors.name }}
      </div>
    </div>

    <div class="mb-4">
      <label for="email" class="block text-sm font-medium">Email</label>
      <Input v-model="form.email" id="email" type="text" class="mt-1 w-full" />
      <div v-if="form.errors.email" class="text-sm text-red-500">
        {{ form.errors.email }}
      </div>
    </div>

    <div class="mb-4 w-full">
      <label for="email" class="block text-sm font-medium">Role</label>
      <Select v-model="form.role" class="w-full">
        <SelectTrigger class="w-full">
          <SelectValue placeholder="Pilih Role" />
        </SelectTrigger>
        <SelectContent class="w-full">
          <SelectGroup>
            <SelectItem v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.name }}
            </SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>

      <div v-if="form.errors.role" class="text-sm text-red-500">
        {{ form.errors.role }}
      </div>
    </div>

    <div class="mb-4">
      <label for="password" class="block text-sm font-medium">Password</label>
      <Input v-model="form.password" id="password" type="password" class="mt-1 w-full" />
      <small v-if="props.user.id">Kosongkan jika tidak ingin mengganti password</small>
      <div v-if="form.errors.password" class="text-sm text-red-500">
        {{ form.errors.password }}
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
