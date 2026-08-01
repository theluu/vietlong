/**
 * Mirrors the shapes produced by keybolts_api's ProductSerializer and
 * ProductController. Verified against the live API on 2026-08-01.
 */

export interface TermRef {
  id: number
  name: string
  swatch?: string
}

export interface ProductCard {
  id: number
  /** Full path alias without the leading slash, e.g. `san-pham/khoa-dong-dai-sanh`. */
  slug: string
  name: string
  model: string
  family: string
  badge: string | null
  brand: TermRef | null
  category: TermRef | null
  finish: TermRef | null
  image: { url: string; alt: string } | null
  stockStatus: string | null
  contactPrice: boolean
}

export interface VariantOption {
  key: string
  label: string
  note?: string
  swatch?: string
  available: boolean
  slug: string | null
  code: string | null
}

export interface VariantMatrix {
  family: string
  sizes: VariantOption[]
  finishes: VariantOption[]
}

export interface ProductDetail extends ProductCard {
  shortDesc: string
  descHeading: string
  /** Pre-rendered HTML from Drupal's text format. */
  description: string
  highlights: string[]
  certification: string[]
  warranty: string
  doorThickness: string
  origin: string
  sizeLabel: string
  sizeNote: string
  images: { url: string; alt: string }[]
  specifications: { k: string; v: string }[]
  faqs: { q: string; a: string }[]
  policyCards: { title: string; desc: string }[]
  variants: VariantMatrix
  related: ProductCard[]
  breadcrumb: { label: string; url: string }[]
  jsonLd: Record<string, unknown>
}

/** Axis name -> term label -> count. */
export type Facets = Record<string, Record<string, number>>

export interface ListMeta {
  total: number
  page: number
  limit: number
}

/**
 * Every endpoint returns this envelope. Endpoints that do not paginate
 * (the detail endpoint) send `meta: []` rather than an object, so narrow
 * with `ApiListResponse` when you need the totals.
 */
export interface ApiResponse<T> {
  data: T
  meta: ListMeta | never[]
  facets: Facets
}

export interface ApiListResponse<T> extends ApiResponse<T> {
  meta: ListMeta
}
