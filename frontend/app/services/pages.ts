import { apiFetch } from './http'
import type { AboutPage, Branch, DealersPage } from '~/types/page'

export const fetchBranches = () => apiFetch<Branch[]>('/branches')
export const fetchPage = <T>(key: string) => apiFetch<T>(`/page/${key}`)
export const fetchAbout = () => fetchPage<AboutPage>('about')
export const fetchDealers = () => fetchPage<DealersPage>('dealers')

export interface LeadPayload {
  name: string
  phone: string
  message: string
  source: 'contact' | 'dealer' | 'consult'
  /** Honeypot — must stay empty for real users. */
  website?: string
}

export async function submitLead(payload: LeadPayload): Promise<void> {
  const base = useRuntimeConfig().public.apiBase
  await $fetch(`${base}/contact`, { method: 'POST', body: payload })
}
