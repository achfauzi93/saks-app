<script setup>
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { buttonVariants } from "./ui/button";
import { ChevronFirst, ChevronLast, ChevronLeft, ChevronRight } from "lucide-vue-next";

const props = defineProps({
  models: {
    type: Object,
    required: true,
  },
});
const selectedPerPage = ref(props.models.meta.per_page || 10);

const optionsPerPage = [5, 10, 25, 50];
</script>

<template>
  <div
    class="dark:bg-white w-full justify-between flex items-center p-1 rounded-b-md"
  >
    <div class="lg:flex dark:bg-slate-50 hidden space-x-0.5">
      <component
        :is="models.meta.links.url ? 'Link' : 'span'"
        v-for="(link, index) in models.meta.links"
        :key="index"
        :href="link.url"
        class="px-2 py-1 text-sm text-slate-700 hover:bg-slate-200 rounded-md transition-colors duration-200 ease-in-out"
        :class="{
          'bg-slate-200': link.active,
          'cursor-not-allowed': !link.url,
          'cursor-pointer': link.url && !link.active,
        }"
        @click="link.url ? $emit('navigate', link.url) : null"
        :aria-current="link.active ? 'page' : undefined"
      >
        <span v-html="link.label"></span>
      </component>
    </div>
    <!-- mobile -->
    <div class="flex lg:hidden space-x-1">
      <Link
        v-if="models.links.first"
        :href="models.links.first"
        preserve-scroll="true"
        class="px-2 py-1 text-sm text-slate-700 hover:bg-slate-200 rounded-md transition-colors duration-200 ease-in-out"
        :class="
          buttonVariants({
            variant: 'ghost',
            size: 'icon',
          })
        "
      >
        <ChevronFirst class="size-4" />
      </Link>
      <Link
        v-if="models.links.prev"
        :href="models.links.prev"
        preserve-scroll="true"
        class="px-2 py-1 text-sm text-slate-700 hover:bg-slate-200 rounded-md transition-colors duration-200 ease-in-out"
        :class="
          buttonVariants({
            variant: 'ghost',
            size: 'icon',
          })
        "
      >
        <ChevronLeft class="size-4" />
      </Link>
      <Link
        v-if="models.links.next"
        :href="models.links.next"
        preserve-scroll="true"
        class="px-2 py-1 text-sm text-slate-700 hover:bg-slate-200 rounded-md transition-colors duration-200 ease-in-out"
        :class="
          buttonVariants({
            variant: 'ghost',
            size: 'icon',
          })
        "
      >
        <ChevronRight class="size-4" />
      </Link>
      <Link
        v-if="models.links.last"
        :href="models.links.last"
        preserve-scroll="true"
        class="px-2 py-1 text-sm text-slate-700 hover:bg-slate-200 rounded-md transition-colors duration-200 ease-in-out"
        :class="
          buttonVariants({
            variant: 'ghost',
            size: 'icon',
          })
        "
      >
        <ChevronLast class="size-4" />
      </Link>
    </div>

    <div class="flex space-x-2 items-center px-2">
      <span class="text-sm text-slate-700">
        <select
          v-model="selectedPerPage"
          class="px-2 py-1 text-sm text-slate-700"
          @change="$emit('changePerPage', selectedPerPage)"
        >
          <option v-for="option in optionsPerPage" :key="option" :value="option">
            {{ option }}
          </option>
        </select>
      </span>
      <span class="text-sm text-slate-500">
        <span class="font-medium">{{ models.meta.from }}</span>
        -
        <span class="font-medium">{{ models.meta.to }}</span>
        dari
        <span class="font-medium">{{ models.meta.total }}</span>
        total
      </span>
    </div>
  </div>
</template>
