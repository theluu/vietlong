import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vitest/config'

// Scoped to frontend/test so a bare `vitest run` never walks the Drupal tree.
export default defineConfig({
  // Needed so vitest can import a .vue SFC directly (responsive-image.spec.ts does);
  // vitest otherwise has no idea how to parse a <template>/<script> block.
  plugins: [vue()],
  test: {
    root: import.meta.dirname,
    include: ['test/**/*.spec.ts'],
  },
})
