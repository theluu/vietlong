import type { ApiResponse } from '~/types/product'

/**
 * The single place that knows the API exists. Components never call $fetch.
 */
export async function apiFetch<T>(
  path: string,
  query: Record<string, unknown> = {},
): Promise<ApiResponse<T>> {
  const base = useRuntimeConfig().public.apiBase
  const clean = Object.fromEntries(
    Object.entries(query).filter(([, v]) => v !== undefined && v !== null && v !== ''),
  )
  return await $fetch<ApiResponse<T>>(`${base}${path}`, { query: clean })
}
