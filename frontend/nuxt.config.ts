import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2026-07-31',
  devtools: { enabled: true },
  ssr: true,

  modules: ['@nuxt/fonts'],

  // Nuxt only auto-links /favicon.ico; the rest have to be declared.
  //
  // The `?v=` is not decoration. Chrome keeps favicons in a database separate
  // from the HTTP cache, and a hard reload does not evict it — an old icon can
  // survive for days. Changing the URL is the only reliable way to force a
  // refetch. Bump the number whenever the artwork changes.
  app: {
    head: {
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico?v=2' },
        { rel: 'apple-touch-icon', href: '/apple-touch-icon.png?v=2', sizes: '180x180' },
      ],
      meta: [{ name: 'theme-color', content: '#282d30' }],
    },
  },
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
