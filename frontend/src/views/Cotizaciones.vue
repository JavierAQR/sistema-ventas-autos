<template>
  <div class="flex h-screen bg-gray-100">
    <!-- SIDEBAR -->
    <sidebar />

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 p-8 overflow-y-auto">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
          Gestión de Cotizaciones 📋
        </h1>

        <p class="text-gray-500 mt-2">
          Administra las negociaciones y propuestas comerciales.
        </p>
      </header>

      <!-- CARD PRINCIPAL -->
      <section class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-bold text-gray-700">
            Cotizaciones Generadas
          </h2>

          <button
            @click="abrirModalNuevo"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold transition"
          >
            + Nueva Cotización
          </button>
        </div>

        <!-- TABLA -->

        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="border-b bg-gray-50">
                <th class="p-4">Prospecto</th>

                <th class="p-4">Vehículo</th>

                <th class="p-4">Precio Final</th>

                <th class="p-4">Estado</th>

                <th class="p-4">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="cotizaciones.length === 0">
                <td colspan="5" class="text-center py-6 text-gray-500">
                  No hay cotizaciones registradas aún.
                </td>
              </tr>

              <tr
                v-for="cotizacion in cotizaciones"
                :key="cotizacion.id"
                class="border-b hover:bg-gray-50"
              >
                <td class="p-4">
                  <p class="font-semibold">
                    {{ cotizacion.prospecto?.nombre }}
                    {{ cotizacion.prospecto?.apellido }}
                  </p>

                  <p class="text-sm text-gray-500">
                    {{ cotizacion.prospecto?.email }}
                  </p>
                </td>

                <td class="p-4">
                  {{ cotizacion.vehiculo?.marca }}
                  {{ cotizacion.vehiculo?.modelo }}
                </td>

                <td class="p-4 font-semibold">
                  S/ {{ cotizacion.precio_final }}
                </td>

                <td class="p-4">
                  <span
                    class="px-3 py-1 rounded-full text-xs font-bold"
                    :class="{
                      'bg-yellow-100 text-yellow-700':
                        cotizacion.estado === 'pendiente',

                      'bg-green-100 text-green-700':
                        cotizacion.estado === 'aprobada',

                      'bg-red-100 text-red-700':
                        cotizacion.estado === 'rechazada',
                    }"
                  >
                    {{ cotizacion.estado }}
                  </span>
                </td>

                <td class="p-4 space-x-3">
                  <button
                    @click="abrirModalEditar(cotizacion)"
                    class="text-blue-600 hover:text-blue-800"
                  >
                    ✏️
                  </button>

                  <button
                    @click="eliminarCotizacion(cotizacion.id)"
                    class="text-red-600 hover:text-red-800"
                  >
                    🗑️
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- MODAL -->

      <div
        v-if="mostrarModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
          <h3 class="text-xl font-bold mb-6 text-gray-800">
            {{ modoEdicion ? "Editar Cotización" : "Generar Nueva Cotización" }}
          </h3>

          <form @submit.prevent="guardarCotizacion" class="space-y-4">
            <div>
              <label class="block text-sm font-semibold mb-1">
                Prospecto
              </label>

              <select
                v-model="formulario.prospecto_id"
                required
                class="w-full border rounded-lg p-2"
              >
                <option value="" disabled>Selecciona un cliente</option>

                <option
                  v-for="prospecto in prospectos"
                  :key="prospecto.id"
                  :value="prospecto.id"
                >
                  {{ prospecto.nombre }}
                  {{ prospecto.apellido }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold mb-1"> Vehículo </label>

              <select
                v-model="formulario.vehiculo_id"
                required
                class="w-full border rounded-lg p-2"
              >
                <option value="" disabled>Selecciona vehículo</option>

                <option
                  v-for="vehiculo in vehiculos"
                  :key="vehiculo.id"
                  :value="vehiculo.id"
                >
                  {{ vehiculo.marca }}
                  {{ vehiculo.modelo }}
                  - S/ {{ vehiculo.precio }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold mb-1">
                Precio negociado
              </label>

              <input
                v-model.number="formulario.precio_final"
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
                <option value="pendiente">Pendiente</option>

                <option value="aprobada">Aprobada</option>

                <option value="rechazada">Rechazada</option>
              </select>
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

            <div class="flex justify-end gap-3 pt-4">
              <button
                type="button"
                @click="mostrarModal = false"
                class="px-4 py-2 border rounded-lg"
              >
                Cancelar
              </button>

              <button
                type="submit"
                :disabled="cargando"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg"
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
import { ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import Sidebar from "@/components/Sidebar.vue";

const router = useRouter();

/*
|--------------------------------------------------------------------------
| Axios configurado
|--------------------------------------------------------------------------
*/

const api = axios.create({
  baseURL: "http://127.0.0.1:8001/api",
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  config.headers.Accept = "application/json";

  return config;
});

/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

const cotizaciones = ref([]);

const prospectos = ref([]);

const vehiculos = ref([]);

const mostrarModal = ref(false);

const cargando = ref(false);

const modoEdicion = ref(false);

const formulario = ref({
  id: null,

  prospecto_id: "",

  vehiculo_id: "",

  precio_final: "",

  estado: "pendiente",

  observaciones: "",
});

/*
|--------------------------------------------------------------------------
| Autocompletar vehículo según prospecto
|--------------------------------------------------------------------------
*/

watch(
  () => formulario.value.prospecto_id,

  (nuevoId) => {
    if (modoEdicion.value || !nuevoId) {
      return;
    }

    const prospectoSeleccionado = prospectos.value.find((p) => p.id == nuevoId);

    if (!prospectoSeleccionado || !prospectoSeleccionado.vehiculo_interes) {
      return;
    }

    const vehiculoCoincidente = vehiculos.value.find((v) => {
      const nombreVehiculo = `${v.marca} ${v.modelo}`.trim().toLowerCase();

      return (
        nombreVehiculo ===
        prospectoSeleccionado.vehiculo_interes.trim().toLowerCase()
      );
    });

    if (vehiculoCoincidente) {
      formulario.value.vehiculo_id = vehiculoCoincidente.id;

      formulario.value.precio_final = vehiculoCoincidente.precio;
    } else {
      formulario.value.vehiculo_id = "";

      formulario.value.precio_final = "";
    }
  },
);

/*
|--------------------------------------------------------------------------
| Inicialización
|--------------------------------------------------------------------------
*/

onMounted(() => {
  const vendedor = localStorage.getItem("vendedor_data");

  if (!vendedor) {
    router.push("/login");

    return;
  }

  cargarCotizaciones();

  cargarListas();
});

/*
|--------------------------------------------------------------------------
| Obtener cotizaciones
|--------------------------------------------------------------------------
*/

const cargarCotizaciones = async () => {
  try {
    const response = await api.get("/cotizaciones");

    cotizaciones.value = response.data;
  } catch (error) {
    console.error("Error cargando cotizaciones:", error);
  }
};

/*
|--------------------------------------------------------------------------
| Obtener prospectos y vehículos
|--------------------------------------------------------------------------
*/

const cargarListas = async () => {
  try {
    const [prospectosResponse, vehiculosResponse] = await Promise.all([
      api.get("/prospectos"),

      api.get("/vehiculos"),
    ]);

    prospectos.value = prospectosResponse.data;

    vehiculos.value = vehiculosResponse.data;
  } catch (error) {
    console.error("Error cargando listas:", error);
  }
};

/*
|--------------------------------------------------------------------------
| Abrir modal nuevo
|--------------------------------------------------------------------------
*/

const abrirModalNuevo = () => {
  modoEdicion.value = false;

  formulario.value = {
    id: null,

    prospecto_id: "",

    vehiculo_id: "",

    precio_final: "",

    estado: "pendiente",

    observaciones: "",
  };

  mostrarModal.value = true;
};

/*
|--------------------------------------------------------------------------
| Editar
|--------------------------------------------------------------------------
*/

const abrirModalEditar = (cotizacion) => {
  modoEdicion.value = true;

  formulario.value = {
    ...cotizacion,

    prospecto_id: cotizacion.prospecto_id,

    vehiculo_id: cotizacion.vehiculo_id,
  };

  mostrarModal.value = true;
};

/*
|--------------------------------------------------------------------------
| Crear / actualizar
|--------------------------------------------------------------------------
*/

const guardarCotizacion = async () => {
  cargando.value = true;

  try {
    if (modoEdicion.value) {
      await api.put(
        `/cotizaciones/${formulario.value.id}`,

        formulario.value,
      );
    } else {
      await api.post(
        "/cotizaciones",

        formulario.value,
      );
    }

    mostrarModal.value = false;

    await cargarCotizaciones();
  } catch (error) {
    console.error("Error guardando cotización:", error);

    alert(error.response?.data?.message || "Error al guardar la cotización");
  } finally {
    cargando.value = false;
  }
};

/*
|--------------------------------------------------------------------------
| Eliminar
|--------------------------------------------------------------------------
*/

const eliminarCotizacion = async (id) => {
  if (!confirm("¿Seguro que deseas eliminar esta cotización?")) {
    return;
  }

  try {
    await api.delete(`/cotizaciones/${id}`);

    cargarCotizaciones();
  } catch (error) {
    console.error("Error eliminando:", error);
  }
};
</script>
