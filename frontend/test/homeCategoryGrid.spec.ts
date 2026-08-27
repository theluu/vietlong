import { renderToString } from '@vue/server-renderer'
import { createSSRApp, h, ref } from 'vue'
import { describe, expect, it, beforeAll } from 'vitest'

// The component reaches for Nuxt's auto-imports and global components, which
// only exist inside a Nuxt build. Bare identifiers fall through to globalThis,
// so stubbing there is enough to render it the way SSR does — the same trick
// responsive-image.spec.ts avoids needing by testing a leaf component.
beforeAll(() => {
  Object.assign(globalThis, {
    useCarousel: () => ({
      track: ref(null),
      canPrev: ref(false),
      canNext: ref(true),
      scroll: () => {},
      buttonClass: '',
    }),
  })
})

const CategoryGrid = await import('../app/components/home/CategoryGrid.vue').then(m => m.default)

const categories = [
  { id: 5, name: 'Khóa thông minh', number: '01', desc: 'Vân tay, thẻ từ', image: null, children: [] },
]

function render(section: { eyebrow: string; title: string; desc: string }) {
  return renderToString(createSSRApp(() => h(CategoryGrid, { categories, section })))
}

describe('HomeCategoryGrid', () => {
  it('lấy tiêu đề khối từ dữ liệu biên tập, không viết cứng trong component', async () => {
    const html = await render({
      eyebrow: 'Bộ sưu tập',
      title: 'Chọn khóa cho ngôi nhà',
      desc: 'Mô tả do biên tập viên nhập.',
    })

    expect(html).toContain('Bộ sưu tập')
    expect(html).toContain('Chọn khóa cho ngôi nhà')
    expect(html).toContain('Mô tả do biên tập viên nhập.')
    // Nếu chữ cũ còn sót lại thì nghĩa là vẫn đang viết cứng.
    expect(html).not.toContain('Khám phá sản phẩm')
  })

  it('bỏ hẳn đoạn mô tả khi biên tập viên để trống, không để lại thẻ rỗng', async () => {
    const html = await render({ eyebrow: 'Danh mục', title: 'Khám phá sản phẩm', desc: '' })

    expect(html).toContain('Khám phá sản phẩm')
    expect(html).not.toContain('text-heading text-text m-0 font-semibold')
  })
})
