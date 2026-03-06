<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useFetchApi } from '../../composables/useFetchApi'
import UiCard from '../../components/ui/UiCard.vue'
import UiButton from '../../components/ui/UiButton.vue'
import UiModal from '../../components/ui/UiModal.vue'
import UiInput from '../../components/ui/UiInput.vue'
import UiTable from '../../components/ui/UiTable.vue'

const api = useFetchApi()

interface ThematicAxis {
  id: number
  name: string
  description: string | null
  keywords: string | null
  is_active: boolean
}

const axes = ref<ThematicAxis[]>([])
const modalOpen = ref(false)
const editingId = ref<number | null>(null)
const formName = ref('')
const formDescription = ref('')
const formKeywords = ref('')
const formActive = ref(true)
const formError = ref('')

async function loadAxes() {
  const data = await api.get<ThematicAxis[]>('/admin/thematic-axes')
  if (data) axes.value = Array.isArray(data) ? data : []
}

function openCreate() {
  editingId.value = null
  formName.value = ''
  formDescription.value = ''
  formKeywords.value = ''
  formActive.value = true
  formError.value = ''
  modalOpen.value = true
}

function openEdit(axis: ThematicAxis) {
  editingId.value = axis.id
  formName.value = axis.name
  formDescription.value = axis.description ?? ''
  formKeywords.value = axis.keywords ?? ''
  formActive.value = axis.is_active
  formError.value = ''
  modalOpen.value = true
}

async function save() {
  formError.value = ''
  if (editingId.value) {
    const data = await api.put<ThematicAxis>(`/admin/thematic-axes/${editingId.value}`, {
      name: formName.value,
      description: formDescription.value || null,
      keywords: formKeywords.value || null,
      is_active: formActive.value,
    })
    if (data) {
      modalOpen.value = false
      await loadAxes()
    } else {
      formError.value = api.error.value?.message ?? 'Error'
    }
  } else {
    const data = await api.post<ThematicAxis>('/admin/thematic-axes', {
      name: formName.value,
      description: formDescription.value || null,
      keywords: formKeywords.value || null,
      is_active: formActive.value,
    })
    if (data) {
      modalOpen.value = false
      await loadAxes()
    } else {
      formError.value = api.error.value?.message ?? 'Error'
    }
  }
}

async function remove(id: number) {
  if (!confirm('¿Eliminar este eje temático?')) return
  await api.delete(`/admin/thematic-axes/${id}`)
  if (!api.error.value) await loadAxes()
}

onMounted(loadAxes)
</script>

<template>
  <div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-white">Ejes temáticos</h1>
      <UiButton variant="primary" size="sm" @click="openCreate">Nuevo eje</UiButton>
    </div>

    <p v-if="api.error?.value" class="mb-4 text-sm text-red-400">{{ api.error.value?.message }}</p>

    <div v-if="api.loading.value" class="text-center py-12 text-cgr-muted">Cargando…</div>

    <UiCard v-else class="overflow-hidden">
      <UiTable>
        <template #header>
          <tr>
            <th class="px-6 py-4 text-left">Nombre</th>
            <th class="px-6 py-4 text-left">Descripción</th>
            <th class="px-6 py-4 text-left">Estado</th>
            <th class="px-6 py-4 text-right">Acciones</th>
          </tr>
        </template>
        <tr
          v-for="a in axes"
          :key="a.id"
          class="bg-cgr-card hover:bg-cgr-section/50 transition-colors"
        >
          <td class="px-6 py-4 text-white">{{ a.name }}</td>
          <td class="px-6 py-4 text-cgr-muted text-sm max-w-xs truncate">{{ a.description ?? '—' }}</td>
          <td class="px-6 py-4">
            <span :class="a.is_active ? 'text-green-400' : 'text-cgr-subtle'">
              {{ a.is_active ? 'Activo' : 'Inactivo' }}
            </span>
          </td>
          <td class="px-6 py-4 text-right flex gap-2 justify-end">
            <UiButton variant="ghost" size="sm" @click="openEdit(a)">Editar</UiButton>
            <UiButton variant="danger" size="sm" @click="remove(a.id)">Eliminar</UiButton>
          </td>
        </tr>
      </UiTable>
    </UiCard>

    <UiModal v-model="modalOpen" :title="editingId ? 'Editar eje' : 'Nuevo eje'">
      <div class="space-y-4">
        <UiInput v-model="formName" label="Nombre" required />
        <UiInput v-model="formDescription" label="Descripción" />
        <UiInput v-model="formKeywords" label="Palabras clave" />
        <div class="flex items-center gap-2">
          <input
            v-model="formActive"
            type="checkbox"
            id="active"
            class="rounded border-cgr-border bg-cgr-section text-cgr-purple focus:ring-cgr-purple"
          />
          <label for="active" class="text-sm text-cgr-muted">Activo</label>
        </div>
        <p v-if="formError" class="text-sm text-red-400">{{ formError }}</p>
      </div>
      <template #footer>
        <UiButton variant="secondary" @click="modalOpen = false">Cancelar</UiButton>
        <UiButton :loading="api.loading.value" @click="save">Guardar</UiButton>
      </template>
    </UiModal>
  </div>
</template>
