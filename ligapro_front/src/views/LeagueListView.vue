<template>
  <div
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full antialiased selection:bg-indigo-500 selection:text-white"
  >
    <!-- ENCABEZADO DE LA SECCIÓN CON DISEÑO DE CLASE MUNDIAL -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
      <div>
        <div
          class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 dark:bg-indigo-500/20 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs font-bold uppercase tracking-wider mb-3 backdrop-blur-md"
        >
          <span class="relative flex h-2 w-2">
            <span
              class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"
            ></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
          </span>
          <span>Panel de Administración Global</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
          Mis Ligas Activas
        </h1>
        <p
          class="text-slate-500 dark:text-slate-400 text-base mt-2 max-w-2xl font-normal leading-relaxed"
        >
          Supervisa torneos, administra plantillas y mantén el control absoluto del rendimiento y
          disciplina de tus clubes.
        </p>
      </div>

      <!-- BOTÓN DE ACCIÓN PRINCIPAL PREMIUM -->
      <button
        @click="showCreateLeagueModal = true"
        class="inline-flex items-center justify-center gap-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold px-7 py-4 rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-white/5 hover:bg-indigo-600 dark:hover:bg-indigo-50 dark:hover:text-indigo-600 hover:shadow-indigo-500/25 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 text-sm shrink-0 cursor-pointer"
      >
        <span class="text-lg font-black">＋</span> Crear Nueva Liga
      </button>
    </div>

    <!-- BANNER DE MÉTRICAS (ESTILO SAAS MODERNO) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-10">
      <div
        class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm flex items-center gap-5 relative overflow-hidden group hover:border-indigo-500/30 transition-all"
      >
        <div
          class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition-all"
        ></div>
        <div
          class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-center text-2xl text-indigo-600 dark:text-indigo-400 font-bold shrink-0 shadow-inner"
        >
          🏆
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
            Ligas Totales
          </p>
          <p class="text-3xl font-black text-slate-900 dark:text-white mt-1">
            {{ leagues.length }}
          </p>
        </div>
      </div>

      <div
        class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm flex items-center gap-5 relative overflow-hidden group hover:border-emerald-500/30 transition-all"
      >
        <div
          class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-all"
        ></div>
        <div
          class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-900/50 flex items-center justify-center text-2xl text-emerald-600 dark:text-emerald-400 font-bold shrink-0 shadow-inner"
        >
          🛡️
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
            Estado del Sistema
          </p>
          <p
            class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-2"
          >
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> Operativo
            99.9%
          </p>
        </div>
      </div>

      <div
        class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm flex items-center gap-5 relative overflow-hidden group hover:border-violet-500/30 transition-all"
      >
        <div
          class="absolute -right-6 -bottom-6 w-24 h-24 bg-violet-500/5 rounded-full blur-2xl group-hover:bg-violet-500/10 transition-all"
        ></div>
        <div
          class="w-14 h-14 rounded-2xl bg-violet-50 dark:bg-violet-950/60 border border-violet-100 dark:border-violet-900/50 flex items-center justify-center text-2xl text-violet-600 dark:text-violet-400 font-bold shrink-0 shadow-inner"
        >
          ⚡
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
            Arquitectura
          </p>
          <p class="text-3xl font-black text-slate-900 dark:text-white mt-1">v2.0 Pro</p>
        </div>
      </div>
    </div>

    <!-- ESTADO DE CARGA -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-24">
      <div
        class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin shadow-lg shadow-indigo-500/20"
      ></div>
      <p class="mt-4 text-slate-500 dark:text-slate-400 font-semibold text-sm tracking-wide">
        Cargando ecosistema de ligas...
      </p>
    </div>

    <!-- ESTADO VACÍO -->
    <div
      v-else-if="leagues.length === 0"
      class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-20 text-center shadow-sm"
    >
      <div
        class="w-24 h-24 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-3xl flex items-center justify-center mx-auto mb-6 text-5xl font-bold shadow-inner border border-indigo-100 dark:border-indigo-900/50"
      >
        ⚽
      </div>
      <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-3">
        No hay torneos registrados
      </h3>
      <p
        class="text-slate-500 dark:text-slate-400 text-base max-w-md mx-auto mb-8 font-normal leading-relaxed"
      >
        Empieza creando tu primera liga profesional para configurar calendarios, equipos y tablas de
        posiciones.
      </p>
      <button
        @click="showCreateLeagueModal = true"
        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-4 rounded-2xl text-sm transition-all shadow-lg shadow-indigo-600/30 cursor-pointer"
      >
        + Crear mi primera liga
      </button>
    </div>

    <!-- GRID DE LIGAS (TARJETAS DE ALTO DISEÑO) -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="item in leagues"
        :key="item.id"
        @click="$emit('select-league', item)"
        class="group bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-7 shadow-sm hover:shadow-2xl hover:border-indigo-500/50 dark:hover:border-indigo-500/50 transition-all duration-300 cursor-pointer relative overflow-hidden flex flex-col justify-between"
      >
        <!-- Brillo superior dinámico en hover -->
        <div
          class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity"
        ></div>

        <div>
          <div class="flex items-start justify-between gap-4 mb-6">
            <!-- LOGO O ICONO -->
            <div
              class="w-16 h-16 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-900/60 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black text-2xl shrink-0 overflow-hidden shadow-md group-hover:scale-105 transition-transform"
            >
              <img
                v-if="item.logo"
                :src="item.logo"
                :alt="item.name"
                class="w-full h-full object-cover"
              />
              <span v-else>⚽</span>
            </div>

            <!-- BADGE DE ESTADO -->
            <span
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold border border-emerald-100 dark:border-emerald-900/50"
            >
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Activa
            </span>
          </div>

          <!-- TÍTULO Y DESCRIPCIÓN -->
          <h3
            class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors mb-2"
          >
            {{ item.name }}
          </h3>
          <p
            class="text-slate-500 dark:text-slate-400 text-sm line-clamp-2 leading-relaxed mb-6 font-normal"
          >
            {{
              item.description ||
              'Torneo oficial gestionado mediante LigaPro con control avanzado de equipos y estadísticas.'
            }}
          </p>
        </div>

        <!-- PIE DE TARJETA -->
        <div
          class="pt-5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between"
        >
          <span
            class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider"
          >
            Gestión Completa
          </span>
          <span
            class="inline-flex items-center gap-1.5 text-sm font-bold text-indigo-600 dark:text-indigo-400 group-hover:translate-x-1.5 transition-transform"
          >
            Entrar a la liga &rarr;
          </span>
        </div>
      </div>
    </div>

    <CreateLeagueModal
      v-if="showCreateLeagueModal"
      :league-id="leagues[0]?.id ?? 0"
      @close="showCreateLeagueModal = false"
      @created="onLeagueCreated"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../services/api'
import CreateLeagueModal from './components/modals/CreateLeagueModal.vue'

interface Category {
  id: number
  name: string
}
interface League {
  id: number
  name: string
  description?: string
  logo?: string
  categories?: Category[]
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const emit = defineEmits(['select-league'])

const leagues = ref<League[]>([])
const loading = ref<boolean>(true)
const showCreateLeagueModal = ref<boolean>(false)

const fetchLeagues = async () => {
  try {
    loading.value = true
    const response = await api.get('/leagues')

    if (Array.isArray(response.data)) {
      leagues.value = response.data
    } else if (Array.isArray(response.data.data)) {
      leagues.value = response.data.data
    } else if (response.data.data) {
      leagues.value = [response.data.data]
    }
  } catch (error) {
    console.error('Error al obtener las ligas:', error)
  } finally {
    loading.value = false
  }
}

const onLeagueCreated = (newLeague: League) => {
  leagues.value.push(newLeague)
  showCreateLeagueModal.value = false
}

onMounted(() => {
  fetchLeagues()
})
</script>
