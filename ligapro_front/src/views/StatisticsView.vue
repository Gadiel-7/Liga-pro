<template>
  <div class="space-y-6 mt-4">
    <!-- Encabezado y Sub-pestañas -->
    <div
      class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4"
    >
      <div>
        <h3 class="text-lg font-bold text-slate-900">Estadísticas de Jugadores</h3>
        <p class="text-slate-500 text-sm">
          Líderes de goleo y acumulación de tarjetas en el torneo.
        </p>
      </div>

      <div class="flex gap-2 bg-slate-100 p-1 rounded-xl">
        <button
          @click="subTab = 'goles'"
          :class="
            subTab === 'goles'
              ? 'bg-white text-indigo-600 shadow-xs'
              : 'text-slate-600 hover:text-slate-900'
          "
          class="px-4 py-2 rounded-lg text-xs font-bold transition-all"
        >
          ⚽ Tabla de Goleo
        </button>
        <button
          @click="subTab = 'tarjetas'"
          :class="
            subTab === 'tarjetas'
              ? 'bg-white text-indigo-600 shadow-xs'
              : 'text-slate-600 hover:text-slate-900'
          "
          class="px-4 py-2 rounded-lg text-xs font-bold transition-all"
        >
          🟨 Fair Play (Tarjetas)
        </button>
      </div>
    </div>

    <!-- Cargando -->
    <div v-if="loading" class="text-center py-12 text-slate-400 text-sm">
      Cargando estadísticas...
    </div>

    <!-- Contenido de las Tablas -->
    <div v-else class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
      <!-- 1. TABLA DE GOLES -->
      <table v-if="subTab === 'goles'" class="w-full text-left text-sm">
        <thead
          class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs font-bold uppercase"
        >
          <tr>
            <th class="py-3 px-6">#</th>
            <th class="py-3 px-6">Jugador</th>
            <th class="py-3 px-6">Equipo</th>
            <th class="py-3 px-6 text-center">Goles</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="topScorers.length === 0">
            <td colspan="4" class="py-8 text-center text-slate-400 italic">
              No hay goles registrados todavía.
            </td>
          </tr>
          <tr
            v-for="(player, index) in topScorers"
            :key="player.id"
            class="hover:bg-slate-50/80 transition-colors"
          >
            <td class="py-4 px-6 font-black text-slate-400 w-16">{{ index + 1 }}</td>
            <td class="py-4 px-6 font-bold text-slate-900 flex items-center gap-3">
              <div
                class="w-9 h-9 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center font-bold text-indigo-700 text-xs shrink-0 overflow-hidden"
              >
                <img
                  v-if="player.photo || player.foto"
                  :src="getImageUrl(player.photo || player.foto)"
                  class="w-full h-full object-cover"
                />
                <span v-else>{{
                  (player.name || player.nombre || 'J').charAt(0).toUpperCase()
                }}</span>
              </div>
              {{ player.name || player.nombre }}
            </td>
            <td class="py-4 px-6 text-slate-600 font-medium">{{ player.team_name }}</td>
            <td class="py-4 px-6 text-center">
              <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-black rounded-lg text-sm">{{
                player.goals || 0
              }}</span>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- 2. TABLA DE TARJETAS -->
      <table v-else class="w-full text-left text-sm">
        <thead
          class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs font-bold uppercase"
        >
          <tr>
            <th class="py-4 px-6">#</th>
            <th class="py-4 px-6">Jugador</th>
            <th class="py-4 px-6">Equipo</th>
            <th class="py-4 px-6 text-center">🟨 Amarillas</th>
            <th class="py-4 px-6 text-center">🟥 Rojas</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="cardLeaders.length === 0">
            <td colspan="5" class="py-8 text-center text-slate-400 italic">
              No hay tarjetas registradas todavía.
            </td>
          </tr>
          <tr
            v-for="(player, index) in cardLeaders"
            :key="player.id"
            class="hover:bg-slate-50/80 transition-colors"
          >
            <td class="py-4 px-6 font-black text-slate-400 w-16">{{ index + 1 }}</td>
            <td class="py-4 px-6 font-bold text-slate-900 flex items-center gap-3">
              <div
                class="w-9 h-9 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center font-bold text-indigo-700 text-xs shrink-0 overflow-hidden"
              >
                <img
                  v-if="player.photo || player.foto"
                  :src="getImageUrl(player.photo || player.foto)"
                  class="w-full h-full object-cover"
                />
                <span v-else>{{
                  (player.name || player.nombre || 'J').charAt(0).toUpperCase()
                }}</span>
              </div>
              {{ player.name || player.nombre }}
            </td>
            <td class="py-4 px-6 text-slate-600 font-medium">{{ player.team_name }}</td>
            <td class="py-4 px-6 text-center font-black text-amber-500">
              {{ player.yellow_cards || 0 }}
            </td>
            <td class="py-4 px-6 text-center font-black text-red-500">
              {{ player.red_cards || 0 }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'

const props = defineProps<{ leagueId: string | number }>()

const subTab = ref<'goles' | 'tarjetas'>('goles')
const players = ref<any[]>([])
const loading = ref(true)

const getImageUrl = (path: string | null) => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return `http://localhost:8000/storage/${path}`
}

const fetchStatistics = async () => {
  try {
    loading.value = true
    const res = await api.get(`/leagues/${props.leagueId}`)
    const data = res.data
    const equipos = data.equipos || data.teams || data.liga?.equipos || data.league?.teams || []

    const allPlayers: any[] = []
    equipos.forEach((eq: any) => {
      const teamName = eq.nombre || eq.name || 'Equipo'
      const roster = eq.jugadores || eq.players || []
      roster.forEach((p: any) => {
        allPlayers.push({
          ...p,
          team_name: teamName,
        })
      })
    })

    players.value = allPlayers
  } catch (error) {
    console.error('Error al cargar estadísticas de jugadores:', error)
  } finally {
    loading.value = false
  }
}

const topScorers = computed(() => {
  return [...players.value]
    .filter((p) => (p.goals || 0) > 0)
    .sort((a, b) => (b.goals || 0) - (a.goals || 0))
})

const cardLeaders = computed(() => {
  return [...players.value]
    .filter((p) => (p.yellow_cards || 0) > 0 || (p.red_cards || 0) > 0)
    .sort((a, b) => {
      const totalB = (b.yellow_cards || 0) + (b.red_cards || 0)
      const totalA = (a.yellow_cards || 0) + (a.red_cards || 0)
      return totalB - totalA
    })
})

onMounted(() => {
  if (props.leagueId) fetchStatistics()
})
</script>
