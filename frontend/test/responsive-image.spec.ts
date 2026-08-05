import { renderToString } from '@vue/server-renderer'
import { createSSRApp, h } from 'vue'
import { describe, expect, it } from 'vitest'
import ResponsiveImage from '../app/components/ui/ResponsiveImage.vue'

// No DOM environment or @vue/test-utils is installed in this project (see
// task-4-report.md). renderToString exercises the component exactly the way
// Nuxt SSR does in production, so assertions run against the emitted HTML
// string instead of a mounted wrapper.
const image = {
  url: 'https://x.test/styles/kb_card_800_webp/cover.webp',
  srcset: 'https://x.test/styles/kb_card_400_webp/cover.webp 400w, https://x.test/styles/kb_card_800_webp/cover.webp 800w',
  srcsetAvif: 'https://x.test/styles/kb_card_400_avif/cover.webp.avif 400w, https://x.test/styles/kb_card_800_avif/cover.webp.avif 800w',
  width: 2000,
  height: 1200,
  alt: 'Khóa vân tay',
}

function render(props: Record<string, unknown>, attrs: Record<string, unknown> = {}) {
  return renderToString(createSSRApp(() => h(ResponsiveImage, { ...props, ...attrs })))
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

  it('phát source AVIF trước, và img fallback vẫn là webp', async () => {
    const html = await render({ image, sizes: '100vw' })
    expect(html).toContain('type="image/avif"')
    expect(html).toContain(`srcset="${image.srcsetAvif}"`)
    expect(html).toContain(`src="${image.url}"`)
  })

  // Quan trọng nhất trong task này: root đổi từ <img> sang <picture> nghĩa là
  // $attrs mặc định sẽ rơi vào <picture> thay vì <img>. Mọi nơi gọi component
  // này đều truyền class tạo dáng (`size-full object-cover`) kỳ vọng nó nằm
  // trên chính tấm ảnh — đặt nhầm lên <picture> thì class không có tác dụng
  // gì, và mọi ảnh trên site mất định dạng cùng lúc mà mắt thường lướt qua
  // không thấy ngay.
  it('class của nơi gọi rơi vào img chứ không phải picture', async () => {
    const html = await render({ image, sizes: '100vw' }, { class: 'size-full object-cover' })
    expect(html).toMatch(/<img[^>]*class="size-full object-cover"/)
    expect(html).not.toMatch(/<picture[^>]*class=/)
  })
})
