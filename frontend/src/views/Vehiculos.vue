<template>
  <div class="flex h-screen bg-gray-50">
    <!-- SIDEBAR -->
    <sidebar />

    <!-- CONTENIDO -->
    <main class="flex-1 p-8 overflow-y-auto">
      <header class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
          <Car :size="24" class="text-blue-600" />
          Inventario de vehículos
        </h1>
        <p class="text-gray-500 text-sm mt-1">
          Gestiona los autos disponibles para la venta.
        </p>
      </header>

      <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-5">
          <h2 class="text-lg font-bold text-slate-900">Catálogo actual</h2>

          <button
            @click="abrirModalNuevo"
            class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
          >
            <Plus :size="16" />
            Nuevo vehículo
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 text-gray-500">
                <th class="text-left font-medium p-3">Vehículo</th>
                <th class="text-left font-medium p-3">Año</th>
                <th class="text-left font-medium p-3">Precio</th>
                <th class="text-left font-medium p-3">Stock</th>
                <th class="text-right font-medium p-3">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="vehiculo in vehiculos"
                :key="vehiculo.id"
                class="border-b border-gray-50 hover:bg-gray-50 transition"
              >
                <td class="p-3 font-medium text-slate-900">
                  {{ vehiculo.marca }} {{ vehiculo.modelo }}
                </td>

                <td class="p-3 text-gray-600">
                  <div class="flex items-center gap-1.5">
                    <CalendarDays :size="14" class="text-gray-400" />
                    {{ vehiculo.anio }}
                  </div>
                </td>

                <td class="p-3 text-gray-600">
                  <div class="flex items-center gap-1.5">
                    <DollarSign :size="14" class="text-gray-400" />
                    S/ {{ vehiculo.precio }}
                  </div>
                </td>

                <td class="p-3">
                  <div class="flex items-center gap-1.5 text-gray-600">
                    <Package :size="14" class="text-gray-400" />
                    <span
                      class="font-semibold"
                      :class="
                        vehiculo.stock_disponible > 0
                          ? 'text-slate-900'
                          : 'text-red-600'
                      "
                    >
                      {{ vehiculo.stock_disponible }}
                    </span>
                    / {{ vehiculo.stock }} disponibles
                  </div>

                  <span
                    v-if="vehiculo.stock_disponible === 0"
                    class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"
                  >
                    Agotado
                  </span>
                </td>

                <td class="p-3">
                  <div class="flex items-center justify-end gap-1">
                    <button
                      @click="abrirModalEditar(vehiculo)"
                      class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                      aria-label="Editar vehículo"
                    >
                      <Pencil :size="16" />
                    </button>

                    <!--
                    <button
                      @click="eliminarVehiculo(vehiculo.id)"
                      class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                      aria-label="Eliminar vehículo"
                    >
                      <Trash2 :size="16" />
                    </button>
                    -->
                  </div>
                </td>
              </tr>

              <tr v-if="vehiculos.length === 0">
                <td colspan="5" class="text-center p-10 text-gray-400">
                  <CarFront :size="28" class="mx-auto mb-2" />
                  No existen vehículos registrados.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- MODAL -->
      <Transition name="fade">
        <div
          v-if="mostrarModal"
          class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4"
        >
          <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-5">
              <h3 class="text-lg font-bold text-slate-900">
                {{ modoEdicion ? "Editar vehículo" : "Nuevo vehículo" }}
              </h3>

              <button
                @click="mostrarModal = false"
                class="text-gray-400 hover:text-gray-600 transition"
                aria-label="Cerrar"
              >
                <X :size="20" />
              </button>
            </div>

            <form @submit.prevent="guardarVehiculo" class="space-y-3.5">
              <div class="grid grid-cols-2 gap-3">
                <input
                  v-model="formulario.marca"
                  placeholder="Marca"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  required
                />

                <input
                  v-model="formulario.modelo"
                  placeholder="Modelo"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  required
                />
              </div>

              <div class="relative">
                <CalendarDays
                  :size="16"
                  class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                />
                <input
                  v-model.number="formulario.anio"
                  type="number"
                  placeholder="Año"
                  class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  required
                />
              </div>

              <div class="relative">
                <DollarSign
                  :size="16"
                  class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                />
                <input
                  v-model.number="formulario.precio"
                  type="number"
                  placeholder="Precio"
                  class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  required
                />
              </div>

              <div class="relative">
                <Package
                  :size="16"
                  class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                />
                <input
                  v-model.number="formulario.stock"
                  type="number"
                  :min="ventasRealizadasVehiculo"
                  placeholder="Stock"
                  class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  required
                />
              </div>

              <p
                v-if="modoEdicion && ventasRealizadasVehiculo > 0"
                class="flex items-center gap-1.5 text-xs text-gray-500"
              >
                <Info :size="14" />
                Stock mínimo permitido: {{ ventasRealizadasVehiculo }} (ventas
                ya realizadas)
              </p>

              <div class="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  @click="mostrarModal = false"
                  class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                >
                  Cancelar
                </button>

                <button
                  class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition disabled:opacity-60 disabled:cursor-not-allowed"
                  :disabled="cargando"
                >
                  <Loader2 v-if="cargando" :size="16" class="animate-spin" />
                  {{ cargando ? "Guardando..." : "Guardar" }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import {
  Car,
  CarFront,
  Plus,
  Pencil,
  Trash2,
  CalendarDays,
  DollarSign,
  Package,
  Info,
  X,
  Loader2,
} from "@lucide/vue";
import api from "../services/api";
import Sidebar from "@/components/Sidebar.vue";

const router = useRouter();
const toast = useToast();

const vehiculos = ref([]);

const mostrarModal = ref(false);
const modoEdicion = ref(false);
const cargando = ref(false);

const formulario = ref({
  id: null,
  marca: "",
  modelo: "",
  anio: null,
  precio: null,
  stock: null,
});

onMounted(() => {
  if (!localStorage.getItem("token")) {
    router.push("/login");
    return;
  }

  cargarVehiculos();
});

const cargarVehiculos = async () => {
  try {
    const response = await api.get("/vehiculos");
    vehiculos.value = response.data;
  } catch (error) {
    console.error(error);
    toast.error("No se pudo cargar el catálogo de vehículos.");
  }
};

const abrirModalNuevo = () => {
  modoEdicion.value = false;

  formulario.value = {
    id: null,
    marca: "",
    modelo: "",
    anio: null,
    precio: null,
    stock: null,
  };

  mostrarModal.value = true;
};

const abrirModalEditar = (vehiculo) => {
  modoEdicion.value = true;

  formulario.value = { ...vehiculo };

  mostrarModal.value = true;
};

const guardarVehiculo = async () => {
  cargando.value = true;

  try {
    if (modoEdicion.value) {
      await api.put(`/vehiculos/${formulario.value.id}`, formulario.value);
      toast.success("Vehículo actualizado correctamente.");
    } else {
      await api.post("/vehiculos", formulario.value);
      toast.success("Vehículo registrado correctamente.");
    }

    mostrarModal.value = false;
    await cargarVehiculos();
  } catch (error) {
    console.error(error);
    toast.error(error.response?.data?.mensaje || "Error guardando vehículo");
  } finally {
    cargando.value = false;
  }
};

const ventasRealizadasVehiculo = computed(() => {
  if (
    !modoEdicion.value ||
    formulario.value.stock == null ||
    formulario.value.stock_disponible == null
  ) {
    return 0;
  }
  return formulario.value.stock - formulario.value.stock_disponible;
});

// Eliminación deshabilitada temporalmente (botón comentado en el template)
const eliminarVehiculo = async (id) => {
  if (!confirm("¿Eliminar vehículo?")) return;

  try {
    await api.delete(`/vehiculos/${id}`);
    toast.success("Vehículo eliminado correctamente.");
    await cargarVehiculos();
  } catch (error) {
    console.error(error);
    toast.error(
      error.response?.data?.mensaje || "No se pudo eliminar el vehículo",
    );
  }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
