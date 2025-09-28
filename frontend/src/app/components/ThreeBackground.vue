<template>
  <div ref="threeContainer" class="three-container" />
</template>

<script setup>
const threeContainer = ref()

onMounted(async () => {
  const { THREE } = await import('three')
  
  // Сцена
  const scene = new THREE.Scene()
  
  // Камера
  const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000)
  camera.position.z = 5
  
  // Рендерер
  const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true })
  renderer.setSize(window.innerWidth, window.innerHeight)
  renderer.setClearColor(0x000000, 0)
  threeContainer.value.appendChild(renderer.domElement)
  
  // Геометрия - плавающие кубы
  const cubes = []
  for (let i = 0; i < 20; i++) {
    const geometry = new THREE.BoxGeometry(0.2, 0.2, 0.2)
    const material = new THREE.MeshBasicMaterial({ 
      color: new THREE.Color().setHSL(Math.random() * 0.1 + 0.5, 0.7, 0.5),
      transparent: true,
      opacity: 0.6
    })
    const cube = new THREE.Mesh(geometry, material)
    
    cube.position.x = (Math.random() - 0.5) * 10
    cube.position.y = (Math.random() - 0.5) * 10
    cube.position.z = (Math.random() - 0.5) * 10
    
    cube.rotation.x = Math.random() * Math.PI
    cube.rotation.y = Math.random() * Math.PI
    
    scene.add(cube)
    cubes.push(cube)
  }
  
  // Анимация
  function animate() {
    requestAnimationFrame(animate)
    
    cubes.forEach((cube, index) => {
      cube.rotation.x += 0.01
      cube.rotation.y += 0.01
      cube.position.y += Math.sin(Date.now() * 0.001 + index) * 0.001
    })
    
    renderer.render(scene, camera)
  }
  
  animate()
  
  // Ресайз
  window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight
    camera.updateProjectionMatrix()
    renderer.setSize(window.innerWidth, window.innerHeight)
  })
})
</script>

<style scoped>
.three-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: -1;
}
</style>
