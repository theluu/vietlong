<script setup lang="ts">
defineProps<{ rows: { k: string; v: string }[] }>()
</script>

<template>
  <table class="w-full border-collapse text-left">
    <tbody>
      <tr v-for="row in rows" :key="row.k" class="border-b border-border">
        <th
          scope="row"
          class="text-body w-2/5 py-3 pr-6 align-top font-bold text-text"
        >{{ row.k }}</th>
        <td class="text-body text-text-muted py-3 align-top">{{ row.v }}</td>
      </tr>
    </tbody>
  </table>
</template>
