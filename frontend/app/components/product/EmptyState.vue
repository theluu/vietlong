<script setup lang="ts">
defineEmits<{ (e: 'clear'): void }>()
</script>

<template>
  <div class="flex flex-col items-start gap-4 border border-border bg-surface px-6 py-12">
    <p class="text-heading m-0 font-bold">Không có sản phẩm phù hợp</p>
    <button
      type="button"
      class="text-body cursor-pointer rounded-sm bg-charcoal-900 px-6 py-3 font-bold text-white"
      @click="$emit('clear')"
    >Xóa bộ lọc</button>
  </div>
</template>
