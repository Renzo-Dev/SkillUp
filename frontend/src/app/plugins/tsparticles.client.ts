import { loadSlim } from 'tsparticles-slim'

export default defineNuxtPlugin(async () => {
  const { tsParticles } = await loadSlim()
  
  return {
    provide: {
      tsParticles,
    },
  }
})
