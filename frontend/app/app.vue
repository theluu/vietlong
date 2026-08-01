<script setup lang="ts">
// Temporary token smoke screen. Task 15 replaces this with the layout shell.
const swatches = [
  'primary', 'accent', 'accent-strong', 'accent-ink',
  'surface', 'border', 'text-muted', 'success', 'warning', 'danger',
]
const type: [string, string][] = [
  ['display-xl', 'Khóa cửa Việt Long'],
  ['display-lg', 'Khóa cửa Việt Long'],
  ['display', 'Khóa cửa Việt Long'],
  ['heading', 'Khóa tay gạt phân thể KB 1700'],
  ['body', 'Thân khóa inox 304, mặt khóa hợp kim kẽm mạ PVD vàng bóng.'],
  ['caption', 'Bảo hành 24 tháng'],
]
</script>

<template>
  <div>
    <NuxtRouteAnnouncer />
    <main class="mx-auto max-w-[var(--container-max)] p-8">
      <h1 class="text-display font-bold">Design tokens</h1>

      <section class="mt-8">
        <h2 class="text-heading font-light">Colour</h2>
        <ul class="mt-4 flex list-none flex-wrap gap-4 p-0">
          <li v-for="name in swatches" :key="name" class="w-40">
            <div class="border-border h-16 border" :style="{ background: `var(--color-${name})` }" />
            <code class="text-caption text-text-muted">{{ name }}</code>
          </li>
        </ul>
      </section>

      <section class="mt-8">
        <h2 class="text-heading font-light">Type — Nunito Sans</h2>
        <p v-for="[size, text] in type" :key="size" class="mt-3" :style="{ fontSize: `var(--text-${size})` }">
          {{ text }}
          <code class="text-caption text-text-muted">&nbsp;{{ size }}</code>
        </p>
      </section>
    </main>
  </div>
</template>
