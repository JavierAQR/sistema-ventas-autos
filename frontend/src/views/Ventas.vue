<template>
  <div class="flex h-screen bg-gray-100">
    <!-- SIDEBAR -->
    <sidebar />

    <!-- CONTENIDO -->
    <main class="flex-1 p-8 overflow-y-auto">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Gestión de Ventas 💰</h1>

        <p class="text-gray-600 mt-2">
          Registra las operaciones cerradas con los clientes.
        </p>
      </header>

      <section class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-5">
          <h2 class="text-xl font-bold">Ventas Registradas</h2>

          <button
            @click="abrirModalNuevo"
            class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg"
          >
            + Nueva Venta
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="border-b">
                <th class="p-3 text-left">Cliente</th>

                <th class="p-3 text-left">Vehículo</th>

                <th class="p-3 text-left">Monto</th>

                <th class="p-3 text-left">Estado</th>

                <th class="p-3 text-left">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="ventas.length === 0">
                <td colspan="5" class="text-center p-5 text-gray-500">
                  No hay ventas registradas.
                </td>
              </tr>

              <tr
                v-for="venta in ventas"
                :key="venta.id"
                class="border-b hover:bg-gray-50"
              >
                <td class="p-3">
                  {{ venta.prospecto?.nombre }}
                  {{ venta.prospecto?.apellido }}
                </td>

                <td class="p-3">
                  {{ venta.vehiculo?.marca }}
                  {{ venta.vehiculo?.modelo }}
                </td>

                <td class="p-3">S/ {{ venta.monto_venta }}</td>

                <td class="p-3">
                  <span
                    class="px-3 py-1 rounded-full text-sm font-bold"
                    :class="
                      venta.estado === 'realizada'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700'
                    "
                  >
                    {{ venta.estado }}
                  </span>
                </td>

                <td class="p-3">
                  <button @click="abrirModalEditar(venta)" class="mr-3">
                    ✏️
                  </button>

                  <button @click="eliminarVenta(venta.id)">🗑️</button>
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
            {{ modoEdicion ? "Editar Venta" : "Registrar Venta" }}
          </h3>

          <form @submit.prevent="guardarVenta" class="space-y-4">
            <!-- Modo creación: selector de cotización -->
            <div v-if="!modoEdicion">
              <label class="font-semibold block mb-1"
                >Cotización aprobada</label
              >

              <select
                v-model="formulario.cotizacion_id"
                @change="seleccionarCotizacion"
                class="w-full border p-2 rounded"
                required
              >
                <option value="" disabled>Seleccionar...</option>

                <option
                  v-for="cotizacion in cotizacionesAprobadas"
                  :key="cotizacion.id"
                  :value="cotizacion.id"
                >
                  {{ cotizacion.prospecto?.nombre }}
                  -
                  {{ cotizacion.vehiculo?.marca }}
                  {{ cotizacion.vehiculo?.modelo }}
                </option>
              </select>

              <p
                v-if="cotizacionesAprobadas.length === 0"
                class="text-sm text-gray-500 mt-1"
              >
                No hay cotizaciones aprobadas disponibles.
              </p>
            </div>

            <!-- Modo edición: solo lectura, no se puede reasignar -->
            <div v-else class="bg-gray-50 border rounded p-3">
              <label class="font-semibold block mb-1 text-gray-600 text-sm">
                Cliente y vehículo
              </label>

              <p class="text-gray-800 font-medium">
                {{ ventaEditando?.prospecto?.nombre }}
                {{ ventaEditando?.prospecto?.apellido }}
              </p>

              <p class="text-gray-600 text-sm">
                {{ ventaEditando?.vehiculo?.marca }}
                {{ ventaEditando?.vehiculo?.modelo }}
              </p>

              <p class="text-xs text-gray-400 mt-1">
                El cliente y vehículo no se pueden modificar una vez creada la
                venta.
              </p>
            </div>

            <input
              v-model.number="formulario.monto_venta"
              type="number"
              placeholder="Monto venta"
              class="w-full border p-2 rounded"
              required
            />

            <div>
              <label class="font-semibold block mb-1">Estado</label>

              <select
                v-model="formulario.estado"
                class="w-full border p-2 rounded"
              >
                <option value="realizada">Realizada</option>
                <option value="fallida">Fallida</option>
              </select>
            </div>

            <!-- Solo aparece si la venta es fallida -->
            <div v-if="formulario.estado === 'fallida'">
              <label class="font-semibold block mb-1">
                Motivo de pérdida <span class="text-red-500">*</span>
              </label>

              <textarea
                v-model="formulario.motivo_perdida"
                placeholder="Ej: El cliente no calificó para el crédito"
                class="w-full border p-2 rounded"
                required
              ></textarea>
            </div>

            <div class="flex justify-end gap-3">
              <button
                type="button"
                @click="mostrarModal = false"
                class="border px-4 py-2 rounded"
              >
                Cancelar
              </button>

              <button
                class="bg-blue-500 text-white px-4 py-2 rounded"
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
import Sidebar from "@/components/Sidebar.vue";
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const ventas = ref([]);
const cotizaciones = ref([]);

const mostrarModal = ref(false);
const cargando = ref(false);
const modoEdicion = ref(false);

const formulario = ref({
  id: null,

  cotizacion_id: "",

  prospecto_id: "",
  vehiculo_id: "",

  vendedor_id: 1,

  monto_venta: "",

  estado: "realizada",

  motivo_perdida: "",
});

const ventaEditando = ref(null);

/*
|--------------------------------------------------------------------------
| Cotizaciones aprobadas
|--------------------------------------------------------------------------
*/

const cotizacionesAprobadas = computed(() => {
  return cotizaciones.value.filter(
    (cotizacion) => cotizacion.estado === "aprobada",
  );
});

/*
|--------------------------------------------------------------------------
| Inicialización
|--------------------------------------------------------------------------
*/

onMounted(() => {
  const data = localStorage.getItem("vendedor_data");

  if (!data) {
    router.push("/login");
    return;
  }

  cargarVentas();

  cargarCotizaciones();
});

/*
|--------------------------------------------------------------------------
| Obtener ventas
|--------------------------------------------------------------------------
*/

const cargarVentas = async () => {
  const token = localStorage.getItem("token");

  try {
    const res = await fetch("http://127.0.0.1:8001/api/ventas", {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    });

    if (res.ok) {
      ventas.value = await res.json();
    }
  } catch (error) {
    console.error("Error cargando ventas:", error);
  }
};

/*
|--------------------------------------------------------------------------
| Obtener cotizaciones
|--------------------------------------------------------------------------
*/

const cargarCotizaciones = async () => {
  const token = localStorage.getItem("token");

  try {
    const res = await fetch("http://127.0.0.1:8001/api/cotizaciones", {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    });

    if (res.ok) {
      cotizaciones.value = await res.json();
    }
  } catch (error) {
    console.error("Error cargando cotizaciones:", error);
  }
};

/*
|--------------------------------------------------------------------------
| Seleccionar cotización aprobada
|--------------------------------------------------------------------------
*/

const seleccionarCotizacion = () => {
  const cotizacion = cotizaciones.value.find(
    (c) => c.id == formulario.value.cotizacion_id,
  );

  if (!cotizacion) return;

  formulario.value.prospecto_id = cotizacion.prospecto_id;

  formulario.value.vehiculo_id = cotizacion.vehiculo_id;

  formulario.value.monto_venta = cotizacion.precio_final;
};

/*
|--------------------------------------------------------------------------
| Abrir nuevo
|--------------------------------------------------------------------------
*/

const abrirModalNuevo = () => {
  modoEdicion.value = false;
  ventaEditando.value = null;

  formulario.value = {
    id: null,
    cotizacion_id: "",
    prospecto_id: "",
    vehiculo_id: "",
    vendedor_id: 1,
    monto_venta: "",
    estado: "realizada",
    motivo_perdida: "",
  };

  mostrarModal.value = true;
};

/*
|--------------------------------------------------------------------------
| Editar venta
|--------------------------------------------------------------------------
*/

const abrirModalEditar = (venta) => {
  modoEdicion.value = true;
  ventaEditando.value = venta;

  formulario.value = {
    id: venta.id,
    cotizacion_id: "",
    prospecto_id: venta.prospecto_id,
    vehiculo_id: venta.vehiculo_id,
    vendedor_id: venta.vendedor_id,
    monto_venta: venta.monto_venta,
    estado: venta.estado,
    motivo_perdida: venta.motivo_perdida ?? "",
  };

  mostrarModal.value = true;
};

/*
|--------------------------------------------------------------------------
| Guardar venta
|--------------------------------------------------------------------------
*/

const guardarVenta = async () => {
  cargando.value = true;

  const token = localStorage.getItem("token");

  const url = modoEdicion.value
    ? `http://127.0.0.1:8001/api/ventas/${formulario.value.id}`
    : "http://127.0.0.1:8001/api/ventas";

  const metodo = modoEdicion.value ? "PUT" : "POST";

  const datos = {
    prospecto_id: formulario.value.prospecto_id,

    vendedor_id: formulario.value.vendedor_id,

    vehiculo_id: formulario.value.vehiculo_id,

    monto_venta: formulario.value.monto_venta,

    estado: formulario.value.estado,

    motivo_perdida: formulario.value.motivo_perdida || null,
  };

  try {
    const res = await fetch(
      url,

      {
        method: metodo,

        headers: {
          "Content-Type": "application/json",

          Authorization: `Bearer ${token}`,

          Accept: "application/json",
        },

        body: JSON.stringify(datos),
      },
    );

    if (res.ok) {
      mostrarModal.value = false;

      await cargarVentas();
    } else {
      const error = await res.json();

      alert(error.mensaje || "Error al guardar venta");
    }
  } catch (error) {
    console.error("Error:", error);
  } finally {
    cargando.value = false;
  }
};

/*
|--------------------------------------------------------------------------
| Eliminar venta
|--------------------------------------------------------------------------
*/

const eliminarVenta = async (id) => {
  if (!confirm("¿Seguro que deseas eliminar esta venta?")) return;

  const token = localStorage.getItem("token");

  try {
    const res = await fetch(
      `http://127.0.0.1:8001/api/ventas/${id}`,

      {
        method: "DELETE",

        headers: {
          Authorization: `Bearer ${token}`,

          Accept: "application/json",
        },
      },
    );

    if (res.ok) {
      cargarVentas();
    }
  } catch (error) {
    console.error("Error eliminando venta:", error);
  }
};

watch(() => formulario.value.estado, (nuevoEstado) => {
  if (nuevoEstado === "realizada") {
    formulario.value.motivo_perdida = "";
  }
});
</script>
