<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const route = useRoute()

interface League {
  id: number | string
  name: string
  description?: string | null
  logo?: string | null
  shield_url?: string | null
}

interface Match {
  id: number | string
  local_team_name?: string | null
  visitor_team_name?: string | null
  teamA?: { name: string; shield_url?: string; logo?: string }
  teamB?: { name: string; shield_url?: string; logo?: string }
  homeTeam?: { name: string; shield_url?: string; logo?: string }
  awayTeam?: { name: string; shield_url?: string; logo?: string }
  team_a?: { name: string; nombre?: string; logo?: string; shield_url?: string }
  team_b?: { name: string; nombre?: string; logo?: string; shield_url?: string }
  score_a?: number
  score_b?: number
  status?: string
  date?: string
  match_date?: string
}

const leagues = ref<League[]>([])
const matches = ref<Match[]>([])
const loading = ref(true)

const fetchData = async () => {
  try {
    loading.value = true
    const [leaguesRes, matchesRes] = await Promise.all([
      api.get('/leagues'),
      api.get('/aficionado/matches'),
    ])
    leagues.value = leaguesRes.data.data || leaguesRes.data
    matches.value = matchesRes.data.matches || matchesRes.data.data || matchesRes.data
  } catch (error) {
    console.error('Error al cargar datos del aficionado:', error)
  } finally {
    loading.value = false
  }
}

const getImageUrl = (logoPath: string | null): string => {
  if (!logoPath) return ''
  if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) {
    if (logoPath.startsWith('http://localhost/storage'))
      return path.replace('http://localhost', 'http://localhost:8000') // Fixed potential typo if needed, keeping original logic below
    return logoPath
  }
  return `http://localhost:8000/storage/${logoPath}`
}

const handleLogout = async () => {
  try {
    await api.post('/logout')
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
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
  <div
    class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white antialiased selection:bg-indigo-500 selection:text-white pb-24"
  >
    <!-- Navbar Superior (Opcional, mantenemos título y botón de salir) -->
    <header
      class="border-b border-slate-200/80 dark:border-slate-800/80 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl sticky top-0 z-30 transition-all"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div
            class="w-11 h-11 rounded-2xl bg-indigo-600/10 dark:bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-2xl shadow-inner"
          >
            ⚽
          </div>
          <div>
            <h1 class="text-xl font-black tracking-tight flex items-center gap-2">
              LigaPro
              <span
                class="text-indigo-600 dark:text-indigo-400 text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/80 border border-indigo-200 dark:border-indigo-900"
              >
                Aficionado
              </span>
            </h1>
            <p class="text-xs text-slate-400 font-medium">Portal de Consulta Oficial</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <button
            @click="handleLogout"
            class="inline-flex items-center gap-2 text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-900/50 px-4 py-2.5 rounded-xl transition-all cursor-pointer"
          >
            <span>🚪</span> Cerrar Sesión
          </button>
        </div>
      </div>
    </header>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
      <!-- Banner de Bienvenida -->
      <div
        class="relative overflow-hidden bg-gradient-to-r from-indigo-900 via-slate-900 to-indigo-950 text-white rounded-3xl p-8 sm:p-12 shadow-2xl border border-indigo-500/20"
      >
        <div class="relative z-10 max-w-2xl">
          <span
            class="inline-block px-3.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold uppercase tracking-widest border border-indigo-500/30 mb-4 backdrop-blur-md"
          >
            Temporada Activa 2026
          </span>
          <h2 class="text-3xl sm:text-4xl font-black tracking-tight mb-3">
            Bienvenido a tu comunidad de fútbol
          </h2>
          <p class="text-slate-300 text-sm sm:text-base font-normal leading-relaxed">
            Explora los torneos oficiales, consulta los calendarios de partidos en tiempo real y
            sigue de cerca el rendimiento de cada equipo y jugador.
          </p>
        </div>
        <div
          class="absolute right-[-20px] bottom-[-40px] text-9xl opacity-10 select-none pointer-events-none"
        >
          ⚽
        </div>
      </div>

      <!-- Sección de Ligas Disponibles -->
      <section>
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-extrabold tracking-tight flex items-center gap-2">
            Ligas Disponibles
          </h3>
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
            {{ leagues.length }} torneos
          </span>
        </div>

        <div
          v-if="loading"
          class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6"
        >
          <div
            v-for="i in 5"
            :key="i"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 aspect-square animate-pulse"
          ></div>
        </div>

        <div
          v-else-if="leagues.length === 0"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-12 text-center text-slate-500"
        >
          No hay ligas registradas por el momento.
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
          <div
            v-for="league in leagues"
            :key="league.id"
            @click="router.push(`/aficionado/leagues/${league.id}`)"
            class="group bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 aspect-square shadow-sm hover:shadow-xl hover:border-indigo-500/50 transition-all cursor-pointer flex flex-col items-center justify-center text-center relative overflow-hidden"
          >
            <div
              class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-2xl font-black mb-4 group-hover:scale-110 transition-transform overflow-hidden shadow-inner shrink-0"
            >
              <img
                v-if="league.logo || league.shield_url"
                :src="getImageUrl(league.logo || league.shield_url || null)"
                class="w-full h-full object-cover"
                @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
              />
              <span v-else>🏆</span>
            </div>

            <h4
              class="text-sm font-extrabold text-slate-900 dark:text-white group-hover:text-indigo-600 transition-colors line-clamp-2"
            >
              {{ league.name }}
            </h4>
            <span class="text-[11px] text-slate-400 font-medium mt-1">Torneo Oficial</span>
          </div>
        </div>
      </section>

      <!-- Sección de Próximos Partidos -->
      <section>
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-extrabold tracking-tight flex items-center gap-2">
            Próximos Partidos
          </h3>
        </div>

        <div
          class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm"
        >
          <div v-if="loading" class="text-center py-10 text-slate-400 font-medium">
            Cargando partidos...
          </div>
          <div
            v-else-if="matches.length === 0"
            class="text-center py-10 text-slate-400 font-medium"
          >
            No hay partidos programados por el momento.
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="match in matches.slice(0, 6)"
              :key="match.id"
              class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl hover:border-indigo-500/30 transition-all gap-3"
            >
              <div class="flex items-center gap-2.5 flex-1 justify-end text-right overflow-hidden">
                <span class="font-bold text-xs sm:text-sm truncate">
                  {{
                    match.teamA?.name ||
                    match.homeTeam?.name ||
                    match.team_a?.name ||
                    match.team_a?.nombre ||
                    match.local_team_name ||
                    'Local'
                  }}
                </span>
                <div
                  class="w-7 h-7 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs"
                >
                  <img
                    v-if="
                      match.teamA?.shield_url ||
                      match.teamA?.logo ||
                      match.homeTeam?.shield_url ||
                      match.homeTeam?.logo ||
                      match.team_a?.logo ||
                      match.team_a?.shield_url
                    "
                    :src="
                      getImageUrl(
                        match.teamA?.shield_url ||
                          match.teamA?.logo ||
                          match.homeTeam?.shield_url ||
                          match.homeTeam?.logo ||
                          match.team_a?.logo ||
                          match.team_a?.shield_url ||
                          null,
                      )
                    "
                    class="w-full h-full object-cover"
                    @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
                  />
                  <span v-else class="text-[9px] font-bold">L</span>
                </div>
              </div>

              <div
                class="px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-black shrink-0 shadow-2xs"
              >
                <span v-if="match.status === 'completed' || match.status === 'live'">
                  {{ match.score_a ?? 0 }} - {{ match.score_b ?? 0 }}
                </span>
                <span v-else class="text-indigo-600 dark:text-indigo-400 tracking-wider">VS</span>
              </div>

              <div class="flex items-center gap-2.5 flex-1 justify-start text-left overflow-hidden">
                <div
                  class="w-7 h-7 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs"
                >
                  <img
                    v-if="
                      match.teamB?.shield_url ||
                      match.teamB?.logo ||
                      match.awayTeam?.shield_url ||
                      match.awayTeam?.logo ||
                      match.team_b?.logo ||
                      match.team_b?.shield_url
                    "
                    :src="
                      getImageUrl(
                        match.teamB?.shield_url ||
                          match.teamB?.logo ||
                          match.awayTeam?.shield_url ||
                          match.awayTeam?.logo ||
                          match.team_b?.logo ||
                          match.team_b?.shield_url ||
                          null,
                      )
                    "
                    class="w-full h-full object-cover"
                    @error="(e: Event) => ((e.target as HTMLImageElement).style.display = 'none')"
                  />
                  <span v-else class="text-[9px] font-bold">V</span>
                </div>
                <span class="font-bold text-xs sm:text-sm truncate">
                  {{
                    match.teamB?.name ||
                    match.awayTeam?.name ||
                    match.team_b?.name ||
                    match.team_b?.nombre ||
                    match.visitor_team_name ||
                    'Visitante'
                  }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- BARRA DE NAVEGACIÓN INFERIOR (ESTILO APP MÓVIL) -->
    <nav
      class="fixed bottom-0 left-0 right-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800 px-6 py-3 z-40 shadow-lg flex justify-around items-center max-w-lg mx-auto sm:rounded-t-3xl"
    >
      <!-- Botón Inicio (Explorer) -->
      <router-link
        to="/explorer"
        class="flex flex-col items-center gap-1 transition-all cursor-pointer group"
        :class="
          route.path === '/explorer'
            ? 'text-indigo-600 dark:text-indigo-400 font-bold'
            : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-medium'
        "
      >
        <span class="text-xl group-hover:scale-110 transition-transform">🏠</span>
        <span class="text-xs">Inicio</span>
      </router-link>

      <!-- Botón Partidos -->
      <router-link
        to="/aficionado/matches"
        class="flex flex-col items-center gap-1 transition-all cursor-pointer group"
        :class="
          route.path === '/aficionado/matches'
            ? 'text-indigo-600 dark:text-indigo-400 font-bold'
            : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-medium'
        "
      >
        <span class="text-xl group-hover:scale-110 transition-transform">⚽</span>
        <span class="text-xs">Partidos</span>
      </router-link>
    </nav>
  </div>
</template>
