import { apiFetch } from './http'
import type { Branch } from '~/types/page'

export const fetchBranches = () => apiFetch<Branch[]>('/branches')
