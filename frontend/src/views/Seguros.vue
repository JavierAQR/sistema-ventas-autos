<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <sidebar />

    <!-- Contenido -->
    <main class="flex-1 p-8 overflow-y-auto">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
          Administración de Seguros 🛡️
        </h1>

        <p class="text-gray-600 mt-2">
          Gestiona seguros prospectados y vendidos asociados a ventas
          realizadas.
        </p>
      </header>

      <section class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-bold text-gray-800">Seguros Registrados</h2>

          <button
            @click="abrirModalNuevo"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold"
          >
            + Registrar Seguro
          </button>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b text-left text-gray-600">
                <th class="p-3">Cliente</th>

                <th class="p-3">Vehículo</th>

                <th class="p-3">Aseguradora</th>

                <th class="p-3">Tipo Seguro</th>

                <th class="p-3">Prima Esperada</th>

                <th class="p-3">Estado</th>

                <th class="p-3">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="seguros.length === 0">
                <td colspan="7" class="p-5 text-center text-gray-500">
                  No hay seguros registrados.
                </td>
              </tr>

              <tr
                v-for="seguro in seguros"
                :key="seguro.id"
                class="border-b hover:bg-gray-50"
              >
                <td class="p-3">
                  {{ seguro.venta?.prospecto?.nombre || "Sin cliente" }}
                  {{ seguro.venta?.prospecto?.apellido || "" }}
                </td>

                <td class="p-3">
                  {{ seguro.venta?.vehiculo?.marca || "Sin vehículo" }}
                  {{ seguro.venta?.vehiculo?.modelo || "" }}
                </td>

                <td class="p-3 font-semibold">
                  {{ seguro.aseguradora }}
                </td>

                <td class="p-3">
                  {{ seguro.tipo_seguro }}
                </td>

                <td class="p-3">S/ {{ seguro.prima_esperada }}</td>

                <td class="p-3">
                  <span
                    v-if="seguro.estado === 'vendido'"
                    class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700 font-semibold"
                  >
                    Vendido
                  </span>

                  <span
                    v-else
                    class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-700 font-semibold"
                  >
                    Prospectado
                  </span>
                </td>

                <td class="p-3 flex gap-3">
                  <button
                    @click="abrirModalEditar(seguro)"
                    class="text-blue-600 hover:text-blue-800 text-xl"
                    title="Editar"
                  >
                    ✏️
                  </button>

                  <button
                    @click="eliminarSeguro(seguro.id)"
                    class="text-red-600 hover:text-red-800 text-xl"
                    title="Eliminar"
                  >
                    🗑️
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Modal -->

      <div
        v-if="mostrarModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-xl shadow-xl p-6 w-[450px]">
          <form @submit.prevent="guardarSeguro" class="space-y-4">
            <div>
              <label class="block text-sm font-semibold mb-1">
                Venta realizada
              </label>

              <select
                v-model="formulario.venta_id"
                required
                class="w-full border rounded-lg p-2"
              >
                <option value="" disabled>Seleccione una venta</option>

                <option
                  v-for="venta in ventasRealizadas"
                  :key="venta.id"
                  :value="venta.id"
                >
                  {{ venta.prospecto?.nombre }}
                  {{ venta.prospecto?.apellido }}
                  -
                  {{ venta.vehiculo?.marca }}
                  {{ venta.vehiculo?.modelo }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold mb-1">
                Aseguradora
              </label>

              <input
                v-model="formulario.aseguradora"
                required
                placeholder="Ej: Rimac"
                class="w-full border rounded-lg p-2"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold mb-1">
                Tipo de seguro
              </label>

              <select
                v-model="formulario.tipo_seguro"
                required
                class="w-full border rounded-lg p-2"
              >
                <option value="">Seleccione</option>

                <option value="Todo Riesgo">Todo Riesgo</option>

                <option value="Daños a terceros">Daños a terceros</option>

                <option value="Pérdida Total">Pérdida Total</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold mb-1">
                Prima esperada
              </label>

              <input
                v-model.number="formulario.prima_esperada"
                type="number"
                step="0.01"
                required
                class="w-full border rounded-lg p-2"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold mb-1"> Estado </label>

              <select
                v-model="formulario.estado"
                class="w-full border rounded-lg p-2"
              >
                <option value="prospectado">Prospectado</option>

                <option value="vendido">Vendido</option>
              </select>
            </div>

            <div v-if="formulario.estado === 'vendido'">
              <label class="block text-sm font-semibold mb-1">
                Prima real
              </label>

              <input
                v-model.number="formulario.prima_real"
                type="number"
                step="0.01"
                required
                class="w-full border rounded-lg p-2"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold mb-1">
                Observaciones
              </label>

              <textarea
                v-model="formulario.observaciones"
                rows="3"
                class="w-full border rounded-lg p-2"
              ></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-3">
              <button
                type="button"
                @click="cerrarModal"
                class="px-4 py-2 border rounded-lg"
              >
                Cancelar
              </button>

              <button
                type="submit"
                :disabled="cargando"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg"
              >
                {{
                  cargando
                    ? "Guardando..."
                    : modoEdicion
                      ? "Actualizar Seguro"
                      : "Guardar Seguro"
                }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import Sidebar from "@/components/Sidebar.vue";

const router = useRouter();

const api = axios.create({
  baseURL: "http://127.0.0.1:8001/api",
});

// Agregar token automáticamente
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  config.headers.Accept = "application/json";

  return config;
});

const seguros = ref([]);
const ventas = ref([]);

const mostrarModal = ref(false);
const cargando = ref(false);
const modoEdicion = ref(false);
const seguroEditandoId = ref(null);

const formulario = ref({
  venta_id: "",
  aseguradora: "",
  tipo_seguro: "",
  prima_esperada: "",
  prima_real: null,
  estado: "prospectado",
  observaciones: "",
});

// Solo mostrar ventas realizadas
const ventasRealizadas = computed(() => {
  return ventas.value.filter((venta) => venta.estado === "realizada");
});

onMounted(async () => {
  const data = localStorage.getItem("vendedor_data");

  if (!data) {
    router.push("/login");
    return;
  }

  await cargarSeguros();
  await cargarVentas();
});

const cargarSeguros = async () => {
  try {
    const response = await api.get("/seguros");

    seguros.value = response.data;
  } catch (error) {
    console.error("Error cargando seguros:", error);
  }
};

const cargarVentas = async () => {
  try {
    const response = await api.get("/ventas");

    ventas.value = response.data;
  } catch (error) {
    console.error("Error cargando ventas:", error);
  }
};

const abrirModalNuevo = () => {
  modoEdicion.value = false;
  seguroEditandoId.value = null;

  formulario.value = {
    venta_id: "",
    aseguradora: "",
    tipo_seguro: "",
    prima_esperada: "",
    prima_real: null,
    estado: "prospectado",
    observaciones: "",
  };

  mostrarModal.value = true;
};

const abrirModalEditar = (seguro) => {
  modoEdicion.value = true;
  seguroEditandoId.value = seguro.id;

  formulario.value = {
    venta_id: seguro.venta_id,
    aseguradora: seguro.aseguradora,
    tipo_seguro: seguro.tipo_seguro,
    prima_esperada: seguro.prima_esperada,
    prima_real: seguro.prima_real,
    estado: seguro.estado,
    observaciones: seguro.observaciones,
  };

  mostrarModal.value = true;
};

const guardarSeguro = async () => {
  cargando.value = true;

  try {
    const data = {
      venta_id: formulario.value.venta_id,
      aseguradora: formulario.value.aseguradora,
      tipo_seguro: formulario.value.tipo_seguro,
      prima_esperada: formulario.value.prima_esperada,
      prima_real:
        formulario.value.estado === "vendido"
          ? formulario.value.prima_real
          : null,
      estado: formulario.value.estado,
      observaciones: formulario.value.observaciones,
    };

    if (modoEdicion.value) {
      await api.put(`/seguros/${seguroEditandoId.value}`, data);
    } else {
      await api.post("/seguros", data);
    }

    mostrarModal.value = false;

    await cargarSeguros();
  } catch (error) {
    console.error("Error guardando seguro:", error);

    if (error.response?.data?.message) {
      alert(error.response.data.message);
    } else {
      alert("Error al guardar seguro");
    }
  } finally {
    cargando.value = false;
  }
};

const eliminarSeguro = async (id) => {
  const confirmar = confirm("¿Seguro que deseas eliminar este seguro?");

  if (!confirmar) return;

  try {
    await api.delete(`/seguros/${id}`);

    await cargarSeguros();
  } catch (error) {
    console.error("Error eliminando seguro:", error);

    alert("No se pudo eliminar el seguro");
  }
};

const cerrarModal = () => {
  mostrarModal.value = false;
  modoEdicion.value = false;
  seguroEditandoId.value = null;
};
</script>
