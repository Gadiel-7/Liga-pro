<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Modificar Partido</h3>
        <button class="btn-close" @click="$emit('close')">✕</button>
      </div>

      <div class="modal-body text-center" v-if="loading">
        <p class="loading-text">⌛ Cargando información del partido...</p>
      </div>

      <div class="modal-body" v-else-if="match">
        <section class="edit-section">
          <h4>Datos del Encuentro</h4>
          <div class="form-row">
            <div class="form-group">
              <label>Fecha y Hora</label>
              <input type="datetime-local" v-model="formData.date" class="form-input" />
            </div>
            <div class="form-group">
              <label>Lugar / Sede</label>
              <input
                type="text"
                v-model="formData.location"
                placeholder="Ej: Campo Principal"
                class="form-input"
              />
            </div>
          </div>
          <div class="form-group">
            <label>Estado del Partido</label>
            <select v-model="formData.status" class="form-input">
              <option value="scheduled">Programado</option>
              <option value="live">En Vivo</option>
              <option value="completed">Finalizado</option>
            </select>
          </div>
        </section>

        <section class="edit-section score-section">
          <h4>Marcador Final</h4>
          <div class="teams-score">
            <div class="team-block">
              <span>{{ match.teamA?.name || match.team_a?.name || 'Local' }}</span>
              <input type="number" v-model.number="formData.score_a" min="0" class="score-input" />
            </div>
            <div class="vs-text">VS</div>
            <div class="team-block">
              <input type="number" v-model.number="formData.score_b" min="0" class="score-input" />
              <span>{{ match.teamB?.name || match.team_b?.name || 'Visitante' }}</span>
            </div>
          </div>
        </section>

        <section class="edit-section events-section">
          <div class="events-header">
            <h4>Incidencias (Goles y Tarjetas)</h4>
            <button @click="addEvent" class="btn-sm btn-secondary">+ Añadir Incidencia</button>
          </div>

          <div v-if="formData.events.length === 0" class="no-events">
            Sin incidencias registradas
          </div>

          <div v-for="(event, index) in formData.events" :key="index" class="event-row">
            <!-- Tipo de Evento -->
            <select v-model="event.type" class="event-input small">
              <option value="goal">⚽ Gol</option>
              <option value="yellow_card">🟨 Tarjeta Amarilla</option>
              <option value="red_card">🟥 Tarjeta Roja</option>
            </select>

            <!-- Equipo -->
            <select v-model="event.team_id" @change="event.player_id = null" class="event-input">
              <option :value="match.team_a_id || match.teamA?.id">
                {{ match.teamA?.name || match.team_a?.name }}
              </option>
              <option :value="match.team_b_id || match.teamB?.id">
                {{ match.teamB?.name || match.team_b?.name }}
              </option>
            </select>

            <!-- Jugador -->
            <select v-model="event.player_id" class="event-input">
              <option :value="null">Selecciona Jugador...</option>
              <option
                v-for="player in getPlayersForTeam(event.team_id)"
                :key="player.id"
                :value="player.id"
              >
                #{{ player.number || '-' }} {{ player.name }}
              </option>
            </select>

            <!-- Minuto -->
            <input
              type="number"
              v-model.number="event.minute"
              placeholder="Min"
              class="event-input min"
              min="1"
              max="120"
            />

            <button @click="removeEvent(index)" class="btn-delete-event" title="Eliminar">✕</button>
          </div>
        </section>
      </div>

      <div class="modal-body text-center" v-else>
        <p class="error-text">❌ No se pudieron cargar los datos del partido.</p>
      </div>

      <div class="modal-footer">
        <button class="btn-cancel" @click="$emit('close')">Cancelar</button>
        <button class="btn-save" @click="saveMatch" :disabled="saving || loading || !match">
          {{ saving ? 'Guardando...' : 'Guardar Partido' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
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
      if (!isNaN(d.getTime())) {
        formattedDate = d.toISOString().slice(0, 16)
      }
    }

    formData.value = {
      date: formattedDate,
      location: match.value.location || '',
      status: match.value.status || 'scheduled',
      score_a: match.value.score_a ?? match.value.home_score ?? 0,
      score_b: match.value.score_b ?? match.value.away_score ?? 0,
      events: Array.isArray(resData?.timeline)
        ? resData.timeline.map((e: any) => ({
            type: e.type,
            team_id: e.team_id,
            player_id: e.player_id || e.player?.id,
            minute: e.minute,
          }))
        : Array.isArray(match.value.events)
          ? match.value.events.map((e: any) => ({
              type: e.type,
              team_id: e.team_id,
              player_id: e.player_id || e.player?.id,
              minute: e.minute,
            }))
          : [],
    }
  } catch (error) {
    console.error('Error al obtener datos del partido:', error)
  } finally {
    loading.value = false
  }
}

const getPlayersForTeam = (teamId: number) => {
  if (!match.value) return []
  const teamAId = match.value.team_a_id || match.value.teamA?.id
  const teamBId = match.value.team_b_id || match.value.teamB?.id

  if (teamId === teamAId) return match.value.teamA?.players || match.value.team_a?.players || []
  if (teamId === teamBId) return match.value.teamB?.players || match.value.team_b?.players || []
  return []
}

const addEvent = () => {
  const defaultTeamId = match.value?.team_a_id || match.value?.teamA?.id
  formData.value.events.push({
    type: 'goal',
    team_id: defaultTeamId,
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
    emit('updated')
  } catch (error: any) {
    alert(error.response?.data?.message || 'Error al actualizar el partido')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchMatchData()
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  padding: 0;
  border-radius: 12px;
  width: 100%;
  max-width: 650px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}
.modal-header {
  padding: 16px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}
.modal-header h3 {
  margin: 0;
  font-size: 18px;
  color: #0f172a;
}
.btn-close {
  background: transparent;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #64748b;
}
.modal-body {
  padding: 24px;
  overflow-y: auto;
  flex-grow: 1;
}

.loading-text {
  color: #64748b;
  padding: 30px 0;
  font-size: 15px;
}
.error-text {
  color: #ef4444;
  padding: 30px 0;
  font-size: 15px;
}
.text-center {
  text-align: center;
}

.edit-section {
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f1f5f9;
}
.edit-section h4 {
  margin: 0 0 16px 0;
  color: #334155;
  font-size: 15px;
  border-left: 4px solid #2563eb;
  padding-left: 8px;
}

.form-row {
  display: flex;
  gap: 16px;
  margin-bottom: 12px;
}
.form-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
}
.form-input {
  padding: 10px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 14px;
}

.score-section {
  background: #f8fafc;
  padding: 16px;
  border-radius: 8px;
}
.teams-score {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 20px;
}
.team-block {
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: bold;
  font-size: 16px;
  color: #0f172a;
}
.score-input {
  width: 60px;
  padding: 8px;
  text-align: center;
  font-size: 18px;
  font-weight: bold;
  border: 2px solid #cbd5e1;
  border-radius: 8px;
}
.vs-text {
  color: #94a3b8;
  font-weight: bold;
}

.events-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.btn-secondary {
  background: #e0f2fe;
  color: #0284c7;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
}
.event-row {
  display: flex;
  gap: 8px;
  margin-bottom: 10px;
  align-items: center;
  background: #f8fafc;
  padding: 8px;
  border-radius: 6px;
  border: 1px dashed #cbd5e1;
}
.event-input {
  padding: 6px;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  font-size: 13px;
  flex: 1;
}
.event-input.small {
  flex: 0.7;
}
.event-input.min {
  width: 60px;
  flex: none;
  text-align: center;
}
.btn-delete-event {
  background: transparent;
  border: none;
  color: #ef4444;
  font-weight: bold;
  cursor: pointer;
  padding: 4px;
}
.no-events {
  text-align: center;
  color: #94a3b8;
  font-size: 13px;
  padding: 12px;
  font-style: italic;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: #f8fafc;
}
.btn-cancel {
  background: white;
  border: 1px solid #cbd5e1;
  padding: 10px 16px;
  border-radius: 6px;
  cursor: pointer;
  color: #475569;
  font-weight: 600;
}
.btn-save {
  background: #2563eb;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}
.btn-save:disabled {
  background: #93c5fd;
  cursor: not-allowed;
}
</style>
