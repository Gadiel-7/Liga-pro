<template>
  <DashboardLayout>
    <!-- Encabezado del Dashboard -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <div
          class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs font-bold uppercase tracking-wider mb-2"
        >
          <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span> Panel de Control
          Técnico
        </div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Gestión de Torneos</h1>
        <p class="text-slate-500 text-sm mt-1">
          Supervisa el rendimiento de tus ligas, controla calendarios y administra plantillas de
          equipos en tiempo real.
        </p>
      </div>
    </div>

    <!-- Banner de Métricas del Administrador -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
      <div
        class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center gap-4"
      >
        <div
          class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold"
        >
          🏆
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Torneos Activos</p>
          <p class="text-2xl font-black text-slate-900 mt-0.5">{{ ligas.length }}</p>
        </div>
      </div>

      <div
        class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center gap-4"
      >
        <div
          class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold"
        >
          ⚽
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Estado de Sede</p>
          <p class="text-sm font-black text-emerald-600 mt-1 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Operativo 100%
          </p>
        </div>
      </div>

      <div
        class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center gap-4"
      >
        <div
          class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl font-bold"
        >
          ⚡
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sistema</p>
          <p class="text-2xl font-black text-slate-900 mt-0.5">LigaPro v2</p>
        </div>
      </div>
    </div>

    <!-- Estado: Cargando -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div
        class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"
      ></div>
      <p class="mt-4 text-slate-500 font-medium text-sm">Sincronizando torneos y estadísticas...</p>
    </div>

    <!-- Estado: Error -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-2xl text-center"
    >
      <p class="font-semibold mb-2">{{ error }}</p>
      <button
        @click="fetchLeagues"
        class="text-xs bg-red-100 hover:bg-red-200 text-red-800 font-bold px-4 py-2 rounded-xl transition-colors inline-block mt-2 cursor-pointer"
      >
        Reintentar conexión
      </button>
    </div>

    <!-- Estado: Vacío -->
    <div
      v-else-if="ligas.length === 0"
      class="bg-white border border-slate-200 rounded-2xl p-12 text-center shadow-sm"
    >
      <div
        class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold"
      >
        ⚽
      </div>
      <h3 class="text-lg font-bold text-slate-900 mb-1">No hay torneos registrados</h3>
      <p class="text-slate-500 text-sm max-w-md mx-auto mb-6">
        Empieza creando tu primera liga de fútbol para configurar jornadas, tablas de posiciones y
        clubes participantes.
      </p>
    </div>

    <!-- Grid de Ligas (Diseño de Tarjetas del Dashboard) -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="liga in ligas"
        :key="liga.id"
        class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:border-indigo-500/50 transition-all flex flex-col justify-between group"
      >
        <div>
          <div class="flex items-start justify-between gap-4 mb-4">
            <div
              class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 text-2xl shrink-0 overflow-hidden shadow-sm group-hover:scale-105 transition-transform"
            >
              <img
                v-if="liga.logo"
                :src="liga.logo"
                :alt="liga.name"
                class="w-full h-full object-cover"
              />
              <span v-else>⚽</span>
            </div>

            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-100"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Torneo
              Activo
            </span>
          </div>

          <h3
            class="font-black text-slate-900 text-lg truncate group-hover:text-indigo-600 transition-colors"
          >
            {{ liga.name }}
          </h3>
          <p class="text-slate-500 text-xs mt-2 line-clamp-2 leading-relaxed">
            {{
              liga.description ||
              'Torneo oficial de fútbol administrado mediante LigaPro con control de estadísticas y jornadas.'
            }}
          </p>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400"
            >Panel Directivo</span
          >
          <router-link
            :to="`/leagues/${liga.id}`"
            class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors"
          >
            Administrar liga &rarr;
          </router-link>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import DashboardLayout from './components/modals/DashboardLayout.vue'
import api from '../services/api'

interface League {
  id: number
  name: string
  logo?: string | null
  description?: string | null
}

const ligas = ref<League[]>([])
const loading = ref<boolean>(true)
const error = ref<string | null>(null)

const fetchLeagues = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await api.get('/leagues')

    const data = response.data
    ligas.value = Array.isArray(data) ? data : data.data || []
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
  } catch (err: any) {
    console.error('Error al cargar las ligas:', err)
    error.value = err.response?.data?.message || 'No se pudieron obtener las ligas.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchLeagues()
})
</script>
