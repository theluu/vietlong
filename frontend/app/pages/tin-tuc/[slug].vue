<script setup lang="ts">
import { fetchArticle } from '~/services/pages'
const slug = String(useRoute().params.slug)
const { data, error } = await useAsyncData(`article:${slug}`, () => fetchArticle(slug))
if (error.value || !data.value?.data) throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy bài viết', fatal: true })
const article = computed(() => data.value!.data)
const opened = ref(0)
useSeoMeta({ title: () => `${article.value.title} | Keybolts`, description: () => article.value.summary })
</script>

<template>
  <div>
    <div class="border-b border-border bg-surface"><div class="mx-auto max-w-[1360px] px-[clamp(20px,4vw,48px)] py-4"><LayoutBreadcrumb :items="[{ label: 'Trang chủ', url: '/' }, { label: 'Tin tức', url: '/tin-tuc' }, { label: article.title, url: `/tin-tuc/${article.slug}` }]" /></div></div>
    <header class="bg-charcoal-900 py-[clamp(40px,4.5vw,66px)] text-white"><div class="mx-auto flex max-w-[920px] flex-col gap-4 px-[clamp(20px,4vw,48px)]"><span class="text-gold-200 text-[10px] font-bold tracking-[.2em] uppercase">{{ article.category }}</span><h1 class="text-display-lg m-0 font-bold leading-tight">{{ article.title }}</h1><p class="text-heading m-0 leading-loose text-white/80">{{ article.summary }}</p><div class="text-caption flex flex-wrap gap-4 text-white/60"><span>{{ article.author }}</span><span>{{ article.updated }}</span><span>{{ article.readTime }}</span></div></div></header>
    <main class="mx-auto grid max-w-[1360px] items-start gap-[clamp(32px,4vw,60px)] px-[clamp(20px,4vw,48px)] py-[clamp(32px,4vw,52px)] lg:grid-cols-[minmax(0,1fr)_340px]">
      <article class="flex min-w-0 flex-col gap-7.5">
        <img :src="article.image" :alt="article.title" class="aspect-video w-full border border-border object-cover">
        <div v-if="article.quickAnswer" class="border-l-3 border-gold-200 bg-charcoal-900 p-7 text-white"><span class="text-gold-200 text-[10px] font-bold tracking-[.2em] uppercase">Trả lời nhanh</span><p class="text-heading mb-0 leading-loose">{{ article.quickAnswer }}</p></div>
        <template v-if="article.sections.length"><section v-for="section in article.sections" :id="section.id" :key="section.id" class="scroll-mt-36"><h2 class="text-display font-bold">{{ section.title }}</h2><p v-for="paragraph in section.paragraphs" :key="paragraph" class="text-body text-text-muted leading-loose">{{ paragraph }}</p><ul v-if="section.list?.length" class="space-y-2"><li v-for="item in section.list" :key="item">{{ item }}</li></ul><p v-if="section.note" class="border-l-3 border-brass-500 bg-surface p-5 leading-loose">{{ section.note }}</p></section></template>
        <p v-else class="text-heading text-text-muted leading-loose">{{ article.summary }}</p>
        <section v-if="article.compareRows.length" id="bang-so-sanh"><h2 class="text-display font-bold">Bảng so sánh nhanh theo loại cửa</h2><div class="overflow-x-auto border border-border"><table class="w-full min-w-[620px] border-collapse text-left"><thead class="bg-charcoal-900 text-white"><tr><th>Loại cửa</th><th>Độ dày</th><th>Kiểu khóa nên dùng</th><th>Dự phòng</th></tr></thead><tbody><tr v-for="row in article.compareRows" :key="row.door" class="border-t border-border"><td class="font-bold">{{ row.door }}</td><td>{{ row.thickness }}</td><td>{{ row.lock }}</td><td>{{ row.backup }}</td></tr></tbody></table></div></section>
        <section v-if="article.faqs.length" id="cau-hoi"><h2 class="text-display font-bold">Câu hỏi thường gặp</h2><div class="border-t border-border"><div v-for="(faq, index) in article.faqs" :key="faq.question" class="border-b border-border"><button class="text-heading flex w-full justify-between bg-transparent py-5 text-left font-bold" @click="opened = opened === index ? -1 : index">{{ faq.question }}<span>⌄</span></button><p v-if="opened === index" class="text-body text-text-muted mt-0 pb-5 leading-loose">{{ faq.answer }}</p></div></div></section>
      </article>
      <aside v-if="article.sections.length" class="bg-surface p-6 lg:sticky lg:top-32"><span class="text-caption text-brass-700 font-bold tracking-[.18em] uppercase">Trong bài viết</span><nav class="mt-4 flex flex-col"><a v-for="(section, index) in article.sections" :key="section.id" :href="`#${section.id}`" class="border-t border-border py-3 text-sm text-text no-underline"><span class="mr-3 text-brass-700">0{{ index + 1 }}</span>{{ section.title.replace(/^\d+\.\s*/, '') }}</a></nav></aside>
    </main>
  </div>
</template>

<style scoped>
th, td { padding: 14px 18px; }
</style>
