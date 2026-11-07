<template>
  <section class="module-section">
    <div class="section-header">
      <h2>Gestión de Categorías</h2>
      <button @click="showModal = true" class="btn-primary">+ Nueva Categoría</button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cat in categories" :key="cat.id">
            <td>{{ cat.id }}</td>
            <td>
              <strong>{{ cat.name }}</strong>
            </td>
            <td>{{ cat.description || 'Sin descripción' }}</td>
            <td>
              <button @click="deleteCategory(cat.id)" class="btn-danger">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="modal-overlay">
      <div class="modal-card">
        <h3>Nueva Categoría</h3>
        <input v-model="form.name" placeholder="Nombre (Ej. Sub-15)" class="input-field" />
        <textarea
          v-model="form.description"
          placeholder="Descripción"
          class="input-field"
        ></textarea>
        <div class="modal-actions">
          <button @click="saveCategory" class="btn-success">Guardar</button>
          <button @click="showModal = false" class="btn-danger">Cancelar</button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../services/api'

interface Category {
  id: number
  name: string
  description?: string
}

const categories = ref<Category[]>([])
const showModal = ref(false)
const form = ref({ name: '', description: '' })

const loadCategories = async () => {
  try {
    const res = await api.get('/categories')
    categories.value = res.data.data
  } catch (error) {
    console.error('Error al cargar categorías', error)
  }
}

const saveCategory = async () => {
  try {
    await api.post('/categories', form.value)
    showModal.value = false
    form.value = { name: '', description: '' }
    loadCategories()
  } catch (error) {
    alert('Error al guardar categoría')
  }
}

const deleteCategory = async (id: number) => {
  if (window.confirm('¿Eliminar categoría?')) {
    await api.delete(`/categories/${id}`)
    loadCategories()
  }
}

onMounted(() => {
  loadCategories()
})
</script>

<style scoped>
.module-section {
  background: white;
  padding: 25px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}
.section-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}
table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
th,
td {
  padding: 12px;
  border-bottom: 1px solid #e2e8f0;
}
.btn-primary {
  background-color: #2563eb;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
}
.btn-danger {
  background-color: #ef4444;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}
.btn-success {
  background-color: #10b981;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}
.modal-card {
  background: white;
  padding: 30px;
  border-radius: 10px;
  width: 400px;
}
.input-field {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  box-sizing: border-box;
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
