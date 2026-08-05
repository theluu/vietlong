import { renderToString } from '@vue/server-renderer'
import { createSSRApp, h } from 'vue'
import { describe, expect, it } from 'vitest'
import ResponsiveImage from '../app/components/ui/ResponsiveImage.vue'

// No DOM environment or @vue/test-utils is installed in this project (see
// task-4-report.md). renderToString exercises the component exactly the way
// Nuxt SSR does in production, so assertions run against the emitted HTML
// string instead of a mounted wrapper.
const image = {
  url: 'https://x.test/styles/kb_card_800/cover.webp',
  srcset: 'https://x.test/styles/kb_card_400/cover.webp 400w, https://x.test/styles/kb_card_800/cover.webp 800w',
  width: 2000,
  height: 1200,
  alt: 'Khóa vân tay',
}

function render(props: Record<string, unknown>) {
  return renderToString(createSSRApp(() => h(ResponsiveImage, props)))
}

describe('ResponsiveImage', () => {
  it('phát ra srcset và sizes để trình duyệt tự chọn cỡ', async () => {
    const html = await render({ image, sizes: '(min-width: 1024px) 400px, 100vw' })
    expect(html).toContain(`srcset="${image.srcset}"`)
    expect(html).toContain('sizes="(min-width: 1024px) 400px, 100vw"')
  })

  it('khai width và height thật để trang không nhảy khi ảnh tải xong', async () => {
    const html = await render({ image, sizes: '100vw' })
    expect(html).toContain('width="2000"')
    expect(html).toContain('height="1200"')
  })

  it('mặc định lazy, nhưng ảnh ưu tiên thì tải ngay', async () => {
    const lazy = await render({ image, sizes: '100vw' })
    expect(lazy).toContain('loading="lazy"')
    expect(lazy).not.toContain('fetchpriority')

    const eager = await render({ image, sizes: '100vw', priority: true })
    expect(eager).toContain('loading="eager"')
    expect(eager).toContain('fetchpriority="high"')
  })

  it('không render gì khi không có ảnh, thay vì để lại thẻ img hỏng', async () => {
    const html = await render({ image: null, sizes: '100vw' })
    expect(html).not.toContain('<img')
  })

  it('alt lấy từ dữ liệu, nhưng prop alt đè được khi ngữ cảnh cần khác', async () => {
    const html = await render({ image, sizes: '100vw', alt: 'Ảnh dự án Vinhomes' })
    expect(html).toContain('alt="Ảnh dự án Vinhomes"')
  })
})
