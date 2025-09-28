<template>
  <NuxtLayout name="default">
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup>
import { usePreferredReducedMotion } from '@vueuse/core'

const prefersReducedMotion = usePreferredReducedMotion()

onMounted(() => {
  if (prefersReducedMotion.value) {
    document.documentElement.classList.add('reduce-motion')
  }
})
</script>
