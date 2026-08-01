import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2026-07-31',
  devtools: { enabled: true },
  ssr: true,

  modules: ['@nuxt/fonts'],
  css: ['~/assets/css/tokens.css'],
  vite: { plugins: [tailwindcss()] },

  // Self-hosted so there is no render-blocking request to a third party.
  // Roboto stays named in the stack as a local fallback but is not downloaded.
  fonts: {
    families: [
      { name: 'Nunito Sans', provider: 'google', weights: [300, 400, 600, 700, 800, 900] },
    ],
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://vietlong.ddev.site/api/v1',
    },
  },
})
