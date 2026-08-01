<script setup lang="ts">
defineProps<{ modelValue: string }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: string): void }>()

const OPTIONS = [
  { value: 'featured', label: 'Nổi bật' },
  { value: 'az', label: 'Tên A → Z' },
  { value: 'za', label: 'Tên Z → A' },
  { value: 'cat', label: 'Theo danh mục' },
]
</script>

<template>
  <label class="flex items-center gap-3">
    <span class="text-caption text-text-muted tracking-[0.04em]">Sắp xếp</span>
    <select
      :value="modelValue"
      class="text-body cursor-pointer rounded-sm border border-border bg-background px-4 py-2 text-text"
      @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
      <option v-for="o in OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
    </select>
  </label>
</template>
