export interface FilterState {
  brand: string
  category: string
  finish: string
  /** Door position the lock suits — a way of searching, not a category. */
  position: string
  faceid: boolean
  remoteApp: boolean
  sort: string
  page: number
}

const SORTS = ['featured', 'az', 'za', 'cat'] as const
const DEFAULTS: FilterState = {
  brand: '', category: '', finish: '', position: '',
  faceid: false, remoteApp: false, sort: 'featured', page: 1,
}

/** State -> query object, omitting anything at its default. */
export function toQuery(state: FilterState): Record<string, string> {
  const q: Record<string, string> = {}
  if (state.brand) q.brand = state.brand
  if (state.category) q.category = state.category
  if (state.finish) q.finish = state.finish
  if (state.position) q.position = state.position
  // Present or absent, never `=0`: these narrow to products that have the
  // feature, and there is no request for locks without it.
  if (state.faceid) q.faceid = '1'
  if (state.remoteApp) q.remoteApp = '1'
  if (state.sort && state.sort !== DEFAULTS.sort) q.sort = state.sort
  if (state.page > 1) q.page = String(state.page)
  return q
}

/** Query object -> state, tolerating anything the user pastes into the URL. */
export function fromQuery(query: Record<string, unknown>): FilterState {
  const str = (v: unknown) => (typeof v === 'string' ? v : '')
  const on = (v: unknown) => str(v) === '1' || str(v) === 'true'
  const sort = str(query.sort)
  const page = Number.parseInt(str(query.page), 10)
  return {
    brand: str(query.brand),
    category: str(query.category),
    finish: str(query.finish),
    position: str(query.position),
    faceid: on(query.faceid),
    remoteApp: on(query.remoteApp),
    sort: (SORTS as readonly string[]).includes(sort) ? sort : DEFAULTS.sort,
    page: Number.isFinite(page) && page > 0 ? page : 1,
  }
}
