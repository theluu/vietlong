import { apiFetch } from './http'
import type { ProductCard } from '~/types/product'

export interface HomeCategory {
  id: number
  name: string
  number: string
  desc: string
  image: string
}

export interface HomeBrand {
  id: number
  name: string
  tag: string
  desc: string
  cta: string
}

export interface HomepagePayload {
  categories: HomeCategory[]
  brands: HomeBrand[]
  featured: Record<string, ProductCard[]>
}

export const fetchHomepage = () => apiFetch<HomepagePayload>('/homepage')
