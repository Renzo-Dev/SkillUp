<template>
  <div ref="lottieContainer" class="hero-lottie" aria-hidden="true" />
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'

const lottieContainer = ref(null)
let animation

const nuxtApp = useNuxtApp()

onMounted(() => {
  if (!lottieContainer.value) return

  animation = nuxtApp.$lottie?.loadAnimation({
    container: lottieContainer.value,
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: 'https://assets9.lottiefiles.com/packages/lf20_ygiuluqn.json',
    rendererSettings: {
      preserveAspectRatio: 'xMidYMid slice',
    },
  })
})

onUnmounted(() => {
  animation?.destroy()
})
</script>

<style scoped>
.hero-lottie {
  position: relative;
  width: clamp(220px, 30vw, 360px);
  height: clamp(220px, 30vw, 360px);
  margin-inline: auto;
}

.hero-lottie::after {
  content: '';
  position: absolute;
  inset: -15%;
  background: radial-gradient(circle at center, rgba(168, 85, 247, 0.25), transparent 75%);
  filter: blur(20px);
  z-index: -1;
}
</style>

