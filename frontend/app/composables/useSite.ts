import type { SiteSettings } from '~/types/site'
import { fetchSite } from '~/services/site'

/**
 * The site-wide chrome — menu, hotline, footer, social links — read from
 * Drupal at runtime rather than baked in at build time.
 *
 * That distinction is the whole point: an editor changes the hotline in
 * /admin and it is live on the next request, with no deploy.
 *
 * One shared `useAsyncData` key, so every component on the page reuses a
 * single request.
 */
export function useSite() {
  const { data } = useNuxtData<{ data: SiteSettings }>('site')
  const site = computed(() => data.value?.data ?? FALLBACK)
  return {
    site,
    menu: computed(() => site.value.menu),
    contact: computed(() => site.value.contact),
    social: computed(() => site.value.social),
    footer: computed(() => site.value.footer),
    topbar: computed(() => site.value.topbar),
    header: computed(() => site.value.header),
    hotline: computed(() => site.value.contact.hotline),
    hotlineTel: computed(() => `tel:${site.value.contact.hotlineTel}`),
  }
}

/** Fetches once per request; call from app.vue so every page has it. */
export async function useSiteFetch() {
  return await useAsyncData('site', () => fetchSite())
}

/**
 * Used only if the API is unreachable. Enough for the header and footer to
 * render something sane rather than blank markup.
 */
const FALLBACK: SiteSettings = {
  menu: [],
  topbar: { text: '', badges: [] },
  header: { tagline: '', cta: null },
  contact: {
    hotline: '1900 9018',
    hotlineTel: '19009018',
    email: '',
    companyName: '',
    companyShort: '',
    address: '',
    workingHours: [],
  },
  footer: { description: '', copyright: '', columns: [] },
  social: [],
  seo: { title: '', description: '' },
}
