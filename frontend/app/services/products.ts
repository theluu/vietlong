import type { ApiListResponse, ApiResponse, ProductCard, ProductDetail } from '~/types/product'
import { apiFetch } from './http'

export interface ProductFilters {
  brand?: string
  category?: string
  finish?: string
  sort?: string
  page?: number
}

export const fetchProducts = (filters: ProductFilters = {}) =>
  apiFetch<ProductCard[]>('/products', filters as Record<string, unknown>) as Promise<
    ApiListResponse<ProductCard[]>
  >

/**
 * The detail route takes the bare alias segment, but `ProductCard.slug` carries
 * the full alias (`san-pham/khoa-dong-dai-sanh`). Accept either — passing a card's
 * slug straight through is the obvious thing to do and would otherwise 404.
 */
export const detailSlug = (slug: string): string =>
  slug.replace(/^\/?(san-pham\/)?/, '').replace(/\/$/, '')

export const fetchProduct = (slug: string): Promise<ApiResponse<ProductDetail>> =>
  apiFetch<ProductDetail>(`/products/${detailSlug(slug)}`)

export const suggestProducts = (q: string) =>
  apiFetch<ProductCard[]>('/products/suggest', { q })

/** Same matching as the header overlay, but paged for the /tim-kiem page. */
export const searchProducts = (q: string, page = 1, limit = 12) =>
  apiFetch<ProductCard[]>('/products/suggest', { q, page, limit }) as Promise<
    ApiListResponse<ProductCard[]>
  >
