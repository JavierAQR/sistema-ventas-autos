<template>
  <div class="flex h-screen bg-gray-100">
    <!-- SIDEBAR -->
    <sidebar />

    <!-- CONTENIDO -->
    <main class="flex-1 p-8 overflow-y-auto">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Prospectos 🧑‍💼</h1>

        <p class="text-gray-500">
          Administra los clientes potenciales interesados en tus vehículos.
        </p>
      </header>

      <section class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-5">
          <h2 class="text-xl font-bold">Listado de Prospectos</h2>

          <button
            @click="abrirModalNuevo"
            class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700"
          >
            + Nuevo Prospecto
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="border-b">
              <tr class="text-left text-gray-600">
                <th class="p-3">Nombre</th>
                <th class="p-3">Contacto</th>
                <th class="p-3">Vehículo de interés</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="prospecto in prospectos"
                :key="prospecto.id"
                class="border-b hover:bg-gray-50"
              >
                <td class="p-3">
                  <b>{{ prospecto.nombre }}</b> {{ prospecto.apellido }}
                </td>

                <td class="p-3 text-sm text-gray-600">
                  <div>{{ prospecto.email }}</div>
                  <div v-if="prospecto.telefono">{{ prospecto.telefono }}</div>
                </td>

                <td class="p-3">
                  {{ prospecto.vehiculo_interes }}
                </td>

                <td class="p-3">
                  <span
                    class="px-3 py-1 rounded-full text-sm font-semibold"
                    :class="estadoClases[prospecto.estado]"
                  >
                    {{ estadoLabels[prospecto.estado] }}
                  </span>
                </td>

                <td class="p-3">
                  <button @click="abrirModalEditar(prospecto)" class="mr-3">
                    ✏️
                  </button>

                  <button @click="eliminarProspecto(prospecto.id)">🗑️</button>
                </td>
              </tr>

              <tr v-if="prospectos.length === 0">
                <td colspan="5" class="text-center p-5 text-gray-500">
                  No hay prospectos registrados.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- MODAL -->
      <div
        v-if="mostrarModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center"
      >
        <div class="bg-white rounded-xl p-6 w-96">
          <h3 class="text-xl font-bold mb-5">
            {{ modoEdicion ? "Editar Prospecto" : "Nuevo Prospecto" }}
          </h3>

          <form @submit.prevent="guardarProspecto" class="space-y-3">
            <input
              v-model="formulario.nombre"
              placeholder="Nombre"
              class="w-full border p-2 rounded"
              required
            />

            <input
              v-model="formulario.apellido"
              placeholder="Apellido"
              class="w-full border p-2 rounded"
              required
            />

            <input
              v-model="formulario.email"
              type="email"
              placeholder="Email"
              class="w-full border p-2 rounded"
              required
            />

            <input
              v-model="formulario.telefono"
              placeholder="Teléfono (opcional)"
              class="w-full border p-2 rounded"
            />

            <input
              v-model="formulario.vehiculo_interes"
              placeholder="Vehículo de interés (ej: Toyota Corolla)"
              class="w-full border p-2 rounded"
              required
            />

            <div>
              <label class="text-sm font-semibold block mb-1">Estado</label>

              <select
                v-model="formulario.estado"
                class="w-full border p-2 rounded"
              >
                <option value="prospeccion">Prospección</option>
                <option value="calificacion">Calificación</option>
                <option value="negociacion">Negociación</option>
                <option value="cierre">Cierre</option>
              </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
              <button
                type="button"
                @click="mostrarModal = false"
                class="px-4 py-2 border rounded"
              >
                Cancelar
              </button>

              <button
                class="bg-blue-600 text-white px-4 py-2 rounded"
                :disabled="cargando"
              >
                {{ cargando ? "Guardando..." : "Guardar" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../services/api";
import Sidebar from "@/components/Sidebar.vue";

const router = useRouter();

const prospectos = ref([]);

const mostrarModal = ref(false);
const modoEdicion = ref(false);
const cargando = ref(false);

const formulario = ref({
  id: null,
  nombre: "",
  apellido: "",
  email: "",
  telefono: "",
  vehiculo_interes: "",
  estado: "prospeccion",
});

// Etiquetas y estilos visuales para cada estado
const estadoLabels = {
  prospeccion: "Prospección",
  calificacion: "Calificación",
  negociacion: "Negociación",
  cierre: "Cierre",
};

const estadoClases = {
  prospeccion: "bg-gray-100 text-gray-700",
  calificacion: "bg-yellow-100 text-yellow-700",
  negociacion: "bg-blue-100 text-blue-700",
  cierre: "bg-green-100 text-green-700",
};

onMounted(() => {
  if (!localStorage.getItem("token")) {
    router.push("/login");
    return;
  }

  cargarProspectos();
});

const cargarProspectos = async () => {
  try {
    const response = await api.get("/prospectos");
    prospectos.value = response.data;
  } catch (error) {
    console.error("Error cargando prospectos:", error);
  }
};

const abrirModalNuevo = () => {
  modoEdicion.value = false;

  formulario.value = {
    id: null,
    nombre: "",
    apellido: "",
    email: "",
    telefono: "",
    vehiculo_interes: "",
    estado: "prospeccion",
  };

  mostrarModal.value = true;
};

const abrirModalEditar = (prospecto) => {
  modoEdicion.value = true;

  formulario.value = { ...prospecto };

  mostrarModal.value = true;
};

const guardarProspecto = async () => {
  cargando.value = true;

  try {
    const datos = {
      nombre: formulario.value.nombre,
      apellido: formulario.value.apellido,
      email: formulario.value.email,
      telefono: formulario.value.telefono || null,
      vehiculo_interes: formulario.value.vehiculo_interes,
      estado: formulario.value.estado,
    };

    if (modoEdicion.value) {
      await api.put(`/prospectos/${formulario.value.id}`, datos);
    } else {
      await api.post("/prospectos", datos);
    }

    mostrarModal.value = false;
    await cargarProspectos();
  } catch (error) {
    console.error("Error guardando prospecto:", error);
    alert(error.response?.data?.mensaje || "Error al guardar el prospecto");
  } finally {
    cargando.value = false;
  }
};

const eliminarProspecto = async (id) => {
  if (!confirm("¿Seguro que deseas eliminar este prospecto?")) return;

  try {
    await api.delete(`/prospectos/${id}`);
    await cargarProspectos();
  } catch (error) {
    console.error("Error eliminando prospecto:", error);
    alert(error.response?.data?.mensaje || "No se pudo eliminar el prospecto");
  }
};
</script>