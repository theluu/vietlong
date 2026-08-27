import { apiFetch } from './http'
import type { ProductCard } from '~/types/product'
import type { ResponsiveImage } from '~/types/page'

export interface HomeCategory {
  id: number
  name: string
  /** Tile number (`01`–`04`). Empty on every level below the roots. */
  number: string
  desc: string
  /** Borrowed from a product in the branch, and only ever set on a root. */
  image: ResponsiveImage | null
  children: HomeCategory[]
}

export interface HomeBrand {
  id: number
  name: string
  tag: string
  desc: string
  cta: string
}

export interface HomeLink { label: string; url: string }

export interface HomeSection { eyebrow: string; title: string }

/** The editorial half of the payload; the rest is the live catalogue. */
export interface HomeContent {
  hero: {
    eyebrow: string
    /** Newlines are line breaks; *word* is painted with the gold gradient. */
    title: string
    subtitle: string
    /** Banner slides, in editor order. Empty until someone uploads any. */
    images: ResponsiveImage[]
    ctaPrimary: HomeLink | null
    ctaSecondary: HomeLink | null
    stats: { value: string; label: string }[]
  }
  usps: { title: string; desc: string }[]
  /** `desc` is the blurb under the heading; only this section has one. */
  categorySection: HomeSection & { desc: string }
  featuredSection: HomeSection & { tabs: { key: string; label: string }[] }
  solutionSection: HomeSection & {
    items: {
      title: string
      desc: string
      tags: string[]
      image: string | null
      link: HomeLink | null
    }[]
  }
  tech: {
    eyebrow: string
    title: string
    desc: string
    features: string[]
    image: string | null
    cta: HomeLink | null
  }
  consult: { eyebrow: string; title: string; desc: string }
  seo: { title: string; description: string }
}

export interface HomepagePayload extends HomeContent {
  categories: HomeCategory[]
  brands: HomeBrand[]
  featured: Record<string, ProductCard[]>
}

export const fetchHomepage = () => apiFetch<HomepagePayload>('/homepage')
