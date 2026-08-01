import { defineConfig } from 'vitest/config'

// Scoped to frontend/test so a bare `vitest run` never walks the Drupal tree.
export default defineConfig({
  test: {
    root: import.meta.dirname,
    include: ['test/**/*.spec.ts'],
  },
})
