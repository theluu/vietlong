import { apiFetch } from './http'
import type { Branch } from '~/types/page'

export const fetchBranches = () => apiFetch<Branch[]>('/branches')

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
