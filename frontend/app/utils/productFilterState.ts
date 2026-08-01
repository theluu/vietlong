export interface FilterState {
  brand: string
  category: string
  finish: string
  sort: string
  page: number
}

const SORTS = ['featured', 'az', 'za', 'cat'] as const
const DEFAULTS: FilterState = { brand: '', category: '', finish: '', sort: 'featured', page: 1 }

/** State -> query object, omitting anything at its default. */
export function toQuery(state: FilterState): Record<string, string> {
  const q: Record<string, string> = {}
  if (state.brand) q.brand = state.brand
  if (state.category) q.category = state.category
  if (state.finish) q.finish = state.finish
  if (state.sort && state.sort !== DEFAULTS.sort) q.sort = state.sort
  if (state.page > 1) q.page = String(state.page)
  return q
}

/** Query object -> state, tolerating anything the user pastes into the URL. */
export function fromQuery(query: Record<string, unknown>): FilterState {
  const str = (v: unknown) => (typeof v === 'string' ? v : '')
  const sort = str(query.sort)
  const page = Number.parseInt(str(query.page), 10)
  return {
    brand: str(query.brand),
    category: str(query.category),
    finish: str(query.finish),
    sort: (SORTS as readonly string[]).includes(sort) ? sort : DEFAULTS.sort,
    page: Number.isFinite(page) && page > 0 ? page : 1,
  }
}
