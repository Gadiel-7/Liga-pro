<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
interface League {
  id: number | string
  name: string
  description?: string | null
}

interface Match {
  id: number | string
  local_team_name?: string | null
  visitor_team_name?: string | null
}

const leagues = ref<League[]>([])
const matches = ref<Match[]>([])
const loading = ref(true)

const fetchData = async () => {
  try {
    loading.value = true
    const [leaguesRes, matchesRes] = await Promise.all([api.get('/leagues'), api.get('/matches')])
    leagues.value = leaguesRes.data.data || leaguesRes.data
    matches.value = matchesRes.data.data || matchesRes.data
  } catch (error) {
    console.error('Error al cargar datos del aficionado:', error)
  } finally {
    loading.value = false
  }
}

const handleLogout = async () => {
  try {
    await api.post('/logout')
    localStorage.removeItem('auth_token')
    router.push('/login')
  } catch (error) {
    console.error('Error al cerrar sesión', error)
  }
}

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white">
    <!-- Navbar del Aficionado -->
    <header
      class="border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md sticky top-0 z-30"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="text-2xl">⚽</span>
          <h1 class="text-xl font-black tracking-tight">
            LigaPro
            <span
              class="text-indigo-600 text-xs px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950"
              >Aficionado</span
            >
          </h1>
        </div>
        <nav class="flex items-center gap-4">
          <router-link
            to="/standings"
            class="text-sm font-semibold hover:text-indigo-600 transition-colors"
            >Tablas</router-link
          >
          <router-link
            to="/statistics"
            class="text-sm font-semibold hover:text-indigo-600 transition-colors"
            >Estadísticas</router-link
          >
          <button
            @click="handleLogout"
            class="text-sm font-bold text-red-600 hover:text-red-700 cursor-pointer ml-4"
          >
            Cerrar Sesión
          </button>
        </nav>
      </div>
    </header>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="mb-10">
        <h2 class="text-3xl font-black tracking-tight mb-2">Bienvenido a la comunidad</h2>
        <p class="text-slate-500 dark:text-slate-400">
          Explora las ligas activas, consulta los próximos partidos y sigue el rendimiento de tus
          equipos favoritos.
        </p>
      </div>

      <!-- Sección de Ligas Disponibles -->
      <section class="mb-12">
        <h3 class="text-xl font-bold mb-6 flex items-center gap-2">🏆 Torneos Activos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="league in leagues"
            :key="league.id"
            @click="router.push(`/leagues/${league.id}`)"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm hover:border-indigo-500 transition-all cursor-pointer"
          >
            <h4 class="text-lg font-bold mb-2">{{ league.name }}</h4>
            <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2">
              {{ league.description || 'Torneo oficial de fútbol amateur.' }}
            </p>
            <div
              class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs font-bold text-indigo-600"
            >
              <span>Ver detalles y equipos</span>
              <span>&rarr;</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Sección de Próximos Partidos -->
      <section>
        <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
          ⚡ Partidos Recientes y Próximos
        </h3>
        <div
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm"
        >
          <div v-if="matches.length === 0" class="text-center py-8 text-slate-500">
            No hay partidos programados por el momento.
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="match in matches"
              :key="match.id"
              class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl"
            >
              <span class="font-bold text-sm">{{ match.local_team_name || 'Local' }}</span>
              <span
                class="px-3 py-1 bg-indigo-500/10 text-indigo-600 rounded-lg font-mono text-xs font-bold"
                >VS</span
              >
              <span class="font-bold text-sm">{{ match.visitor_team_name || 'Visitante' }}</span>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>
