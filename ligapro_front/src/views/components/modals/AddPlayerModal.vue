<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <h3>{{ isEditing ? 'Editar Jugador' : `Agregar Jugador a "${teamName}"` }}</h3>
      <form @submit.prevent="submitPlayer" class="form">
        <div class="form-group">
          <label>Nombre del Jugador *</label>
          <input v-model="playerForm.name" type="text" required placeholder="Ej. Juan Pérez" />
        </div>

        <div class="form-group">
          <label>Número de Camiseta / Dorsal</label>
          <input v-model="playerForm.number" type="number" placeholder="Ej. 10" />
        </div>

        <div class="form-group">
          <label>Posición</label>
          <input
            v-model="playerForm.position"
            type="text"
            placeholder="Ej. Delantero, Portero..."
          />
        </div>

        <div class="form-group">
          <label>Edad</label>
          <input v-model="playerForm.age" type="number" min="0" max="100" placeholder="Ej. 22" />
        </div>

        <!-- CAMPO DE PARTIDOS SUSPENDIDOS (SANCIÓN) -->
        <div class="form-group">
          <label>Partidos Suspendidos (Sanción)</label>
          <input
            v-model.number="playerForm.suspended_matches"
            type="number"
            min="0"
            placeholder="Ej. 1"
          />
        </div>

        <div class="form-group">
          <label>Fotografía del Jugador (Opcional)</label>
          <p v-if="isEditing && playerToEdit?.photo" class="text-xs text-indigo-600 mb-1">
            ✓ Este jugador ya cuenta con una foto registrada (sube otra solo si deseas cambiarla).
          </p>
          <input
            @change="handlePhotoChange"
            type="file"
            accept="image/*"
            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
          />
        </div>

        <div class="modal-actions">
          <button type="button" @click="$emit('close')" class="btn-cancel">Cancelar</button>
          <button type="submit" :disabled="saving" class="btn-primary">
            {{ saving ? 'Guardando...' : isEditing ? 'Actualizar Jugador' : 'Guardar Jugador' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed, watch } from 'vue'
import api from '../../../services/api'

const props = defineProps<{
  teamId: number
  teamName: string
  playerToEdit?: any | null
}>()

const emit = defineEmits(['close', 'created', 'updated'])
const saving = ref(false)

const isEditing = computed(() => !!props.playerToEdit)

const playerForm = reactive({
  name: '',
  number: '',
  position: '',
  age: '',
  suspended_matches: 0,
  photo: null as File | null,
})

watch(
  () => props.playerToEdit,
  (newPlayer) => {
    if (newPlayer) {
      playerForm.name = newPlayer.name || newPlayer.nombre || ''
      playerForm.number =
        newPlayer.number !== undefined && newPlayer.number !== null ? newPlayer.number : ''
      playerForm.position = newPlayer.position || newPlayer.posicion || ''
      playerForm.age = newPlayer.age !== undefined && newPlayer.age !== null ? newPlayer.age : ''
      playerForm.suspended_matches =
        newPlayer.suspended_matches !== undefined && newPlayer.suspended_matches !== null
          ? Number(newPlayer.suspended_matches)
          : 0
      playerForm.photo = null
    } else {
      playerForm.name = ''
      playerForm.number = ''
      playerForm.position = ''
      playerForm.age = ''
      playerForm.suspended_matches = 0
      playerForm.photo = null
    }
  },
  { immediate: true },
)

const handlePhotoChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    playerForm.photo = target.files[0]!
  }
}

const submitPlayer = async () => {
  try {
    saving.value = true
    const formData = new FormData()

    formData.append('name', playerForm.name)
    formData.append('team_id', String(props.teamId))
    formData.append('suspended_matches', String(playerForm.suspended_matches))

    if (playerForm.number !== '') {
      formData.append('number', String(playerForm.number))
    }
    if (playerForm.position) {
      formData.append('position', playerForm.position)
    }
    if (playerForm.age !== '') {
      formData.append('age', String(playerForm.age))
    }
    if (playerForm.photo) {
      formData.append('photo', playerForm.photo)
    }

    if (isEditing.value && props.playerToEdit) {
      formData.append('_method', 'PUT')
      const res = await api.post(`/players/${props.playerToEdit.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      alert(res.data.message || '¡Jugador actualizado correctamente!')
      emit('updated', res.data.data)
    } else {
      const res = await api.post('/players', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      alert(res.data.message || '¡Jugador registrado correctamente!')
      emit('created', res.data.data)
    }
  } catch (error: any) {
    alert(error.response?.data?.message || 'Error al guardar el jugador')
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.form-group label {
  color: #334155;
  font-weight: 600;
  font-size: 13px;
}
.form-group input[type='text'],
.form-group input[type='number'] {
  padding: 8px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 14px;
  color: #0f172a;
  background: white;
}
.btn-primary {
  background: #2563eb;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
}
.btn-primary:disabled {
  background: #93c5fd;
  cursor: not-allowed;
}
.btn-cancel {
  background: #e2e8f0;
  color: #475569;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}
.modal-content {
  background: white;
  padding: 24px;
  border-radius: 8px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.modal-content h3 {
  color: #0f172a;
  margin-top: 0;
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}
</style>
