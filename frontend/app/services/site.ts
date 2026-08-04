import type { SiteSettings } from '~/types/site'
import { apiFetch } from './http'

export const fetchSite = () => apiFetch<SiteSettings>('/site')
