<template>
  <div>
    <div class="welcome-banner">
      <h1>Panel de Control</h1>
      <p>Resumen general del estado de la liga de fútbol.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Equipos</h3>
        <p class="stat-number">{{ stats.total_teams }}</p>
      </div>
      <div class="stat-card">
        <h3>Total Jugadores</h3>
        <p class="stat-number">{{ stats.total_players }}</p>
      </div>
      <div class="stat-card">
        <h3>Total Partidos</h3>
        <p class="stat-number">{{ stats.total_matches }}</p>
      </div>
      <div class="stat-card">
        <h3>Partidos Completados</h3>
        <p class="stat-number">{{ stats.completed_matches }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../services/api'

interface Stats {
  total_teams: number
  total_players: number
  total_matches: number
  completed_matches: number
}

const stats = ref<Stats>({
  total_teams: 0,
  total_players: 0,
  total_matches: 0,
  completed_matches: 0,
})

const loadStats = async () => {
  try {
    const res = await api.get('/dashboard')
    stats.value = res.data.data.stats
  } catch (error) {
    console.error('Error al cargar dashboard', error)
  }
}

onMounted(() => {
  loadStats()
})
</script>

<style scoped>
.welcome-banner {
  background: white;
  padding: 25px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  margin-bottom: 25px;
}
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}
.stat-card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  text-align: center;
}
.stat-number {
  font-size: 28px;
  font-weight: bold;
  color: #2563eb;
  margin: 0;
}
</style>
