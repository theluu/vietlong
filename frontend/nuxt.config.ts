import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2026-07-31',
  devtools: { enabled: true },
  ssr: true,

  modules: ['@nuxt/fonts'],
  css: ['~/assets/css/tokens.css'],
  // The dev server is reached through ddev's router, not localhost, so Vite
  // has to be told that host is legitimate or it answers 403 to every route.
  vite: {
    plugins: [tailwindcss()],
    server: { allowedHosts: ['vietlong.ddev.site', '.ddev.site'] },
  },

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
      // Public by design. Empty means the forms skip reCAPTCHA entirely.
      recaptchaSiteKey: process.env.NUXT_PUBLIC_RECAPTCHA_SITE_KEY || '',
    },
  },
})
