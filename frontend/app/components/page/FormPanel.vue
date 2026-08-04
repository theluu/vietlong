<template>
  <div class="kb-form-panel border-border bg-background border">
    <div class="flex flex-col justify-center gap-[16px] p-[clamp(30px,3.4vw,48px)]">
      <slot name="left" />
    </div>
    <div class="border-border bg-surface border-l p-[clamp(30px,3.4vw,48px)]">
      <slot name="form" />
    </div>
  </div>
</template>
