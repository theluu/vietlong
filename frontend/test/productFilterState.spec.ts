import { describe, expect, it } from 'vitest'
import { fromQuery, toQuery, type FilterState } from '../app/utils/productFilterState'

const EMPTY: FilterState = {
  brand: '', category: '', finish: '', position: '',
  faceid: false, remoteApp: false, sort: 'featured', page: 1,
}

describe('productFilterState', () => {
  it('omits empty values from the query string', () => {
    expect(toQuery({ ...EMPTY, category: '3' })).toEqual({ category: '3' })
  })

  it('keeps non-default sort and page', () => {
    expect(toQuery({ ...EMPTY, sort: 'az', page: 2 })).toEqual({ sort: 'az', page: '2' })
  })

  it('round-trips through the query string', () => {
    const state: FilterState = {
      brand: '1', category: '3', finish: '7', position: '44',
      faceid: true, remoteApp: true, sort: 'za', page: 4,
    }
    expect(fromQuery(toQuery(state))).toEqual(state)
  })

  it('falls back to defaults for a missing or malformed query', () => {
    expect(fromQuery({})).toEqual(EMPTY)
    expect(fromQuery({ page: 'abc' }).page).toBe(1)
  })

  it('rejects an unknown sort value', () => {
    expect(fromQuery({ sort: 'price' }).sort).toBe('featured')
  })

  it('leaves an unrequested feature out of the query entirely', () => {
    // `faceid=0` would read as a filter for locks without FaceID, which is
    // not a thing anyone asks for and would still narrow the result.
    expect(toQuery({ ...EMPTY, faceid: false })).toEqual({})
    expect(toQuery({ ...EMPTY, faceid: true })).toEqual({ faceid: '1' })
  })

  it('accepts either spelling of an on switch', () => {
    expect(fromQuery({ remoteApp: 'true' }).remoteApp).toBe(true)
    expect(fromQuery({ remoteApp: '1' }).remoteApp).toBe(true)
    expect(fromQuery({ remoteApp: '0' }).remoteApp).toBe(false)
  })
})
