import { describe, expect, it } from 'vitest'
import { detailSlug } from '../app/services/products'

/**
 * ProductCard.slug is the full path alias, but the detail route wants only the
 * trailing segment. Passing a card's slug straight to fetchProduct is the
 * natural thing to write, so it has to work.
 */
describe('detailSlug', () => {
  it('strips the san-pham/ prefix a card carries', () => {
    expect(detailSlug('san-pham/khoa-dong-dai-sanh')).toBe('khoa-dong-dai-sanh')
  })

  it('strips a leading slash too', () => {
    expect(detailSlug('/san-pham/khoa-dong-dai-sanh')).toBe('khoa-dong-dai-sanh')
  })

  it('leaves an already-bare slug alone', () => {
    expect(detailSlug('khoa-dong-dai-sanh')).toBe('khoa-dong-dai-sanh')
  })

  it('drops a trailing slash', () => {
    expect(detailSlug('san-pham/khoa-dong-dai-sanh/')).toBe('khoa-dong-dai-sanh')
  })
})
