import type { HomeContent, HomepagePayload } from '~/services/homepage'

/**
 * The homepage payload, already fetched by pages/index.vue. Sections read it
 * from the shared cache rather than taking a dozen props through the page.
 */
export function useHome() {
  const { data } = useNuxtData<{ data: HomepagePayload }>('homepage')
  return computed<HomeContent>(() => data.value?.data ?? EMPTY)
}

/**
 * Splits the hero headline an editor typed. Newlines are line breaks and
 * *word* marks the run painted with the gold gradient, so the styling stays
 * in the component while the wording stays in Drupal.
 */
export function heroTitleParts(title: string): { text: string; gradient: boolean }[][] {
  return title.split('\n').map(line =>
    line.split(/(\*[^*]+\*)/).filter(Boolean).map(part =>
      part.startsWith('*') && part.endsWith('*')
        ? { text: part.slice(1, -1), gradient: true }
        : { text: part, gradient: false },
    ),
  )
}

const EMPTY: HomeContent = {
  hero: { eyebrow: '', title: '', subtitle: '', ctaPrimary: null, ctaSecondary: null, stats: [] },
  usps: [],
  categorySection: { eyebrow: '', title: '' },
  featuredSection: { eyebrow: '', title: '', tabs: [] },
  solutionSection: { eyebrow: '', title: '', items: [] },
  tech: { eyebrow: '', title: '', desc: '', features: [], image: null, cta: null },
  consult: { eyebrow: '', title: '', desc: '' },
  seo: { title: '', description: '' },
}
