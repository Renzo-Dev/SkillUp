import Lenis from 'lenis'

export default defineNuxtPlugin(() => {
  if (!import.meta.client) return

  const lenis = new Lenis({
    duration: 1.1,
    easing: t => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smoothWheel: true,
  })

  const raf = (time: number) => {
    lenis.raf(time)
    requestAnimationFrame(raf)
  }

  requestAnimationFrame(raf)

  return {
    provide: {
      lenis,
    },
  }
})
