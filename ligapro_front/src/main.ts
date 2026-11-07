import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './assets/main.css' // O el archivo CSS principal de Tailwind

// Cargar el tema guardado en localStorage antes de montar la app
const savedTheme = localStorage.getItem('theme') || 'light'
if (savedTheme === 'dark') {
  document.documentElement.classList.add('dark')
} else {
  document.documentElement.classList.remove('dark')
}

const app = createApp(App)
app.use(router)
app.mount('#app')
