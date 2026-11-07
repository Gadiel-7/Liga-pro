<template>
  <DashboardLayout>
    <div class="max-w-2xl mx-auto py-8">
      <div
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-sm"
      >
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-6">
          Perfil de Usuario y Ajustes
        </h2>

        <!-- Información del usuario -->
        <div
          class="flex items-center gap-4 pb-6 border-b border-slate-100 dark:border-slate-800 mb-6"
        >
          <div
            class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-400 font-black flex items-center justify-center text-xl"
          >
            {{ userInitial }}
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ userName }}</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Administrador de la Liga</p>
          </div>
        </div>

        <!-- Cambio de Tema (Claro / Oscuro) -->
        <div class="space-y-4">
          <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            Apariencia de la Aplicación
          </h4>

          <div class="grid grid-cols-2 gap-4">
            <!-- Botón Tema Claro -->
            <button
              @click="setTheme('light')"
              :class="
                currentTheme === 'light'
                  ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400'
                  : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'
              "
              class="border-2 rounded-2xl p-4 flex items-center justify-center gap-3 font-semibold text-sm transition-all"
            >
              <span>☀️</span> Tema Claro
            </button>

            <!-- Botón Tema Oscuro -->
            <button
              @click="setTheme('dark')"
              :class="
                currentTheme === 'dark'
                  ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400'
                  : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'
              "
              class="border-2 rounded-2xl p-4 flex items-center justify-center gap-3 font-semibold text-sm transition-all"
            >
              <span>🌙</span> Tema Oscuro
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import DashboardLayout from './components/modals/DashboardLayout.vue'

const currentTheme = ref('light')
const userName = ref('José Gadiel Cab Tzuc')
const userInitial = ref('J')

const setTheme = (theme: string) => {
  currentTheme.value = theme
  localStorage.setItem('theme', theme)

  if (theme === 'dark') {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'light'
  setTheme(savedTheme)
})
</script>
