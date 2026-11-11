<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4"
  >
    <div
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-3xl w-full max-h-[92vh] flex flex-col shadow-2xl overflow-hidden"
    >
      <!-- HEADER PREMIUM -->
      <div
        class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/70 dark:bg-slate-900/50"
      >
        <div class="flex items-center gap-3">
          <div
            class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-lg"
          >
            ⚡
          </div>
          <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">
              Administración de Partido
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Configura marcadores, incidencias y alineaciones oficiales.
            </p>
          </div>
        </div>
        <button
          @click="$emit('close')"
          class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold transition-all cursor-pointer"
        >
          ✕
        </button>
      </div>

      <!-- BODY -->
      <div
        class="p-6 overflow-y-auto space-y-6 flex-1 bg-white dark:bg-slate-900 text-slate-900 dark:text-white"
      >
        <div v-if="loading" class="flex flex-col items-center justify-center py-16">
          <div
            class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-3"
          ></div>
          <p class="text-slate-500 text-sm font-medium">Cargando datos del encuentro...</p>
        </div>

        <template v-else-if="match">
          <!-- 1. GENERALES Y ESTADO -->
          <div
            class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 dark:bg-slate-950/50 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800"
          >
            <div>
              <label
                class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"
                >Fecha y Hora</label
              >
              <input
                type="datetime-local"
                v-model="formData.date"
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div>
              <label
                class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"
                >Sede</label
              >
              <input
                type="text"
                v-model="formData.location"
                placeholder="Campo Principal"
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div>
              <label
                class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"
                >Estado</label
              >
              <select
                v-model="formData.status"
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              >
                <option value="scheduled">Programado</option>
                <option value="live">En Vivo</option>
                <option value="completed">Finalizado</option>
              </select>
            </div>
          </div>

          <!-- 2. MARCADOR FINAL -->
          <div
            class="bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 p-5 rounded-2xl text-center"
          >
            <span
              class="text-xs font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block mb-3"
              >Marcador Oficial</span
            >
            <div class="flex items-center justify-center gap-4">
              <span class="font-extrabold text-sm sm:text-base truncate max-w-35 text-right">{{
                match.teamA?.name || match.team_a?.name
              }}</span>
              <input
                type="number"
                v-model.number="formData.score_a"
                min="0"
                class="w-16 text-center bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2 text-xl font-black shadow-inner"
              />
              <span class="text-slate-400 font-bold text-sm">VS</span>
              <input
                type="number"
                v-model.number="formData.score_b"
                min="0"
                class="w-16 text-center bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2 text-xl font-black shadow-inner"
              />
              <span class="font-extrabold text-sm sm:text-base truncate max-w-35 text-left">{{
                match.teamB?.name || match.team_b?.name
              }}</span>
            </div>
          </div>

          <!-- 3. ALINEACIONES (LIGUILLA) -->
          <div class="space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-2">
              <h4
                class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider"
              >
                Alineaciones y Elegibilidad (Liguilla)
              </h4>
              <p class="text-xs text-slate-500">
                Selecciona los jugadores para acumular sus partidos jugados reglamentarios.
              </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Local Lineup -->
              <div
                class="bg-slate-50 dark:bg-slate-950/50 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 space-y-3"
              >
                <h5
                  class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2"
                >
                  <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                  {{ match.teamA?.name || match.team_a?.name }}
                </h5>
                <div>
                  <span
                    class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 block mb-1"
                    >Titulares</span
                  >
                  <div
                    class="max-h-32 overflow-y-auto space-y-1 bg-white dark:bg-slate-900 p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs shadow-xs"
                  >
                    <label
                      v-for="p in getPlayersForTeam(teamAId)"
                      :key="p.id"
                      class="flex items-center gap-2 cursor-pointer p-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    >
                      <input
                        type="checkbox"
                        :value="p.id"
                        v-model="lineformData.home_starters"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                      />
                      <span class="font-medium">#{{ p.number || '-' }} {{ p.name }}</span>
                    </label>
                  </div>
                </div>
                <div>
                  <span
                    class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 block mb-1"
                    >Suplentes que ingresaron</span
                  >
                  <div
                    class="max-h-28 overflow-y-auto space-y-1 bg-white dark:bg-slate-900 p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs shadow-xs"
                  >
                    <label
                      v-for="p in getPlayersForTeam(teamAId).filter(
                        (x: { id: number }) => !lineformData.home_starters.includes(x.id),
                      )"
                      :key="p.id"
                      class="flex items-center gap-2 cursor-pointer p-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    >
                      <input
                        type="checkbox"
                        :value="p.id"
                        v-model="lineformData.home_substitutes"
                        class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                      />
                      <span class="font-medium">#{{ p.number || '-' }} {{ p.name }}</span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Visitor Lineup -->
              <div
                class="bg-slate-50 dark:bg-slate-950/50 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 space-y-3"
              >
                <h5
                  class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2"
                >
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                  {{ match.teamB?.name || match.team_b?.name }}
                </h5>
                <div>
                  <span
                    class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 block mb-1"
                    >Titulares</span
                  >
                  <div
                    class="max-h-32 overflow-y-auto space-y-1 bg-white dark:bg-slate-900 p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs shadow-xs"
                  >
                    <label
                      v-for="p in getPlayersForTeam(teamBId)"
                      :key="p.id"
                      class="flex items-center gap-2 cursor-pointer p-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    >
                      <input
                        type="checkbox"
                        :value="p.id"
                        v-model="lineformData.away_starters"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                      />
                      <span class="font-medium">#{{ p.number || '-' }} {{ p.name }}</span>
                    </label>
                  </div>
                </div>
                <div>
                  <span
                    class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 block mb-1"
                    >Suplentes que ingresaron</span
                  >
                  <div
                    class="max-h-28 overflow-y-auto space-y-1 bg-white dark:bg-slate-900 p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs shadow-xs"
                  >
                    <label
                      v-for="p in getPlayersForTeam(teamBId).filter(
                        (x: { id: number }) => !lineformData.away_starters.includes(x.id),
                      )"
                      :key="p.id"
                      class="flex items-center gap-2 cursor-pointer p-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    >
                      <input
                        type="checkbox"
                        :value="p.id"
                        v-model="lineformData.away_substitutes"
                        class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                      />
                      <span class="font-medium">#{{ p.number || '-' }} {{ p.name }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 4. INCIDENCIAS -->
          <div class="space-y-3 pt-2">
            <div
              class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2"
            >
              <h4
                class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider"
              >
                Incidencias (Goles y Tarjetas)
              </h4>
              <button
                @click="addEvent"
                class="text-xs bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 px-3 py-1.5 rounded-xl font-bold hover:bg-indigo-100 transition-colors cursor-pointer"
              >
                + Añadir Incidencia
              </button>
            </div>

            <div
              v-if="formData.events.length === 0"
              class="text-center py-6 text-xs text-slate-400 italic bg-slate-50 dark:bg-slate-950/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800"
            >
              Sin incidencias registradas en este encuentro
            </div>

            <div
              v-for="(event, index) in formData.events"
              :key="index"
              class="flex items-center gap-2 bg-slate-50 dark:bg-slate-950/50 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-800"
            >
              <select
                v-model="event.type"
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-xs font-semibold flex-1"
              >
                <option value="goal">⚽ Gol</option>
                <option value="yellow_card">🟨 Amarilla</option>
                <option value="red_card">🟥 Roja</option>
              </select>

              <select
                v-model="event.team_id"
                @change="event.player_id = null"
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-xs font-semibold flex-1"
              >
                <option :value="teamAId">{{ match.teamA?.name || match.team_a?.name }}</option>
                <option :value="teamBId">{{ match.teamB?.name || match.team_b?.name }}</option>
              </select>

              <select
                v-model="event.player_id"
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-xs font-semibold flex-1"
              >
                <option :value="null">Selecciona Jugador...</option>
                <option v-for="p in getPlayersForTeam(event.team_id)" :key="p.id" :value="p.id">
                  #{{ p.number || '-' }} {{ p.name }}
                </option>
              </select>

              <input
                type="number"
                v-model.number="event.minute"
                placeholder="Min"
                class="w-16 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-xs font-semibold text-center"
                min="1"
                max="120"
              />

              <button
                @click="removeEvent(index)"
                class="text-red-500 hover:text-red-700 font-bold px-2 cursor-pointer text-sm"
              >
                ✕
              </button>
            </div>
          </div>
        </template>
      </div>

      <!-- FOOTER -->
      <div
        class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 bg-slate-50/70 dark:bg-slate-900/50"
      >
        <button
          @click="$emit('close')"
          class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200/50 transition-colors cursor-pointer"
        >
          Cancelar
        </button>
        <button
          @click="saveMatch"
          :disabled="saving || loading || !match"
          class="px-6 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-600/30 transition-all cursor-pointer disabled:opacity-50"
        >
          {{ saving ? 'Guardando cambios...' : 'Guardar Partido' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '../../../services/api'

const props = defineProps<{ matchId: number }>()
const emit = defineEmits(['close', 'updated'])

const match = ref<any>(null)
const loading = ref<boolean>(true)
const saving = ref<boolean>(false)

const formData = ref({
  date: '',
  location: '',
  status: 'scheduled',
  score_a: 0,
  score_b: 0,
  events: [] as any[],
})

const lineformData = ref({
  home_starters: [] as number[],
  home_substitutes: [] as number[],
  away_starters: [] as number[],
  away_substitutes: [] as number[],
})

const teamAId = computed(() => match.value?.team_a_id || match.value?.teamA?.id)
const teamBId = computed(() => match.value?.team_b_id || match.value?.teamB?.id)

const fetchMatchData = async () => {
  try {
    loading.value = true
    const res = await api.get(`/matches/${props.matchId}`)
    const resData = res.data?.data || res.data
    match.value = resData?.match || resData

    if (!match.value) return

    let formattedDate = ''
    if (match.value.date) {
      const d = new Date(match.value.date)
      if (!isNaN(d.getTime())) formattedDate = d.toISOString().slice(0, 16)
    }

    formData.value = {
      date: formattedDate,
      location: match.value.location || '',
      status: match.value.status || 'scheduled',
      score_a: match.value.score_a ?? 0,
      score_b: match.value.score_b ?? 0,
      events: Array.isArray(resData?.timeline)
        ? resData.timeline.map((e: any) => ({
            type: e.type,
            team_id: e.team_id,
            player_id: e.player_id || e.player?.id,
            minute: e.minute,
          }))
        : [],
    }

    if (match.value.match_players || resData?.match_players) {
      const players = match.value.match_players || resData.match_players
      players.forEach((mp: any) => {
        if (mp.team_id === teamAId.value) {
          if (mp.type === 'starter') lineformData.value.home_starters.push(mp.player_id)
          else if (mp.type === 'substitute') lineformData.value.home_substitutes.push(mp.player_id)
        } else if (mp.team_id === teamBId.value) {
          if (mp.type === 'starter') lineformData.value.away_starters.push(mp.player_id)
          else if (mp.type === 'substitute') lineformData.value.away_substitutes.push(mp.player_id)
        }
      })
    }
  } catch (error) {
    console.error('Error al obtener datos:', error)
  } finally {
    loading.value = false
  }
}

const getPlayersForTeam = (teamId: number) => {
  if (!match.value) return []
  if (teamId === teamAId.value)
    return match.value.teamA?.players || match.value.team_a?.players || []
  if (teamId === teamBId.value)
    return match.value.teamB?.players || match.value.team_b?.players || []
  return []
}

const addEvent = () => {
  formData.value.events.push({
    type: 'goal',
    team_id: teamAId.value,
    player_id: null,
    minute: null,
  })
}

const removeEvent = (index: number) => {
  formData.value.events.splice(index, 1)
}

const saveMatch = async () => {
  saving.value = true
  try {
    const payload = {
      ...formData.value,
      date: formData.value.date ? formData.value.date.replace('T', ' ') + ':00' : null,
    }
    await api.put(`/matches/${props.matchId}`, payload)

    await api.post(`/matches/${props.matchId}/lineup`, {
      team_id: teamAId.value,
      starters: lineformData.value.home_starters,
      substitutes: lineformData.value.home_substitutes,
    })

    await api.post(`/matches/${props.matchId}/lineup`, {
      team_id: teamBId.value,
      starters: lineformData.value.away_starters,
      substitutes: lineformData.value.away_substitutes,
    })

    emit('updated')
  } catch (error: any) {
    alert(error.response?.data?.message || 'Error al actualizar')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchMatchData()
})
</script>
