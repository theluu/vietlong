import { describe, expect, it } from 'vitest'
import { fromQuery, toQuery } from '../app/utils/productFilterState'

describe('productFilterState', () => {
  it('omits empty values from the query string', () => {
    expect(toQuery({ brand: '', category: '3', finish: '', sort: 'featured', page: 1 }))
      .toEqual({ category: '3' })
  })

  it('keeps non-default sort and page', () => {
    expect(toQuery({ brand: '', category: '', finish: '', sort: 'az', page: 2 }))
      .toEqual({ sort: 'az', page: '2' })
  })

  it('round-trips through the query string', () => {
    const state = { brand: '1', category: '3', finish: '7', sort: 'za', page: 4 }
    expect(fromQuery(toQuery(state))).toEqual(state)
  })

  it('falls back to defaults for a missing or malformed query', () => {
    expect(fromQuery({})).toEqual({ brand: '', category: '', finish: '', sort: 'featured', page: 1 })
    expect(fromQuery({ page: 'abc' }).page).toBe(1)
  })

  it('rejects an unknown sort value', () => {
    expect(fromQuery({ sort: 'price' }).sort).toBe('featured')
  })
})
