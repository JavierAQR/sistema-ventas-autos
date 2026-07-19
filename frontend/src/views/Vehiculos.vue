<template>
  <div class="flex h-screen bg-gray-100">
    <!-- SIDEBAR -->
    <sidebar />

    <!-- CONTENIDO -->

    <main class="flex-1 p-8 overflow-y-auto">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
          Inventario de Vehículos 🚙
        </h1>

        <p class="text-gray-500">
          Gestiona los autos disponibles para la venta.
        </p>
      </header>

      <section class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-5">
          <h2 class="text-xl font-bold">Catálogo Actual</h2>

          <button
            @click="abrirModalNuevo"
            class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700"
          >
            + Nuevo Vehículo
          </button>
        </div>

        <table class="w-full">
          <thead class="border-b">
            <tr class="text-left">
              <th class="p-3">Vehículo</th>

              <th class="p-3">Año</th>

              <th class="p-3">Precio</th>

              <th class="p-3">Stock</th>

              <th class="p-3">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="vehiculo in vehiculos"
              :key="vehiculo.id"
              class="border-b hover:bg-gray-50"
            >
              <td class="p-3">
                <b>{{ vehiculo.marca }}</b>
                {{ vehiculo.modelo }}
              </td>

              <td class="p-3">
                {{ vehiculo.anio }}
              </td>

              <td class="p-3">S/ {{ vehiculo.precio }}</td>

              <td class="p-3">
                {{ vehiculo.stock_disponible }} /
                {{ vehiculo.stock }} disponibles
              </td>

              <td class="p-3">
                <button @click="abrirModalEditar(vehiculo)" class="mr-3">
                  ✏️
                </button>

                <button @click="eliminarVehiculo(vehiculo.id)">🗑️</button>
              </td>
            </tr>

            <tr v-if="vehiculos.length === 0">
              <td colspan="5" class="text-center p-5 text-gray-500">
                No existen vehículos registrados.
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- MODAL -->

      <div
        v-if="mostrarModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center"
      >
        <div class="bg-white rounded-xl p-6 w-96">
          <h3 class="text-xl font-bold mb-5">
            {{ modoEdicion ? "Editar Vehículo" : "Nuevo Vehículo" }}
          </h3>

          <form @submit.prevent="guardarVehiculo" class="space-y-3">
            <input
              v-model="formulario.marca"
              placeholder="Marca"
              class="w-full border p-2 rounded"
              required
            />

            <input
              v-model="formulario.modelo"
              placeholder="Modelo"
              class="w-full border p-2 rounded"
              required
            />

            <input
              v-model.number="formulario.anio"
              type="number"
              placeholder="Año"
              class="w-full border p-2 rounded"
              required
            />

            <input
              v-model.number="formulario.precio"
              type="number"
              placeholder="Precio"
              class="w-full border p-2 rounded"
              required
            />

            <input
              v-model.number="formulario.stock"
              type="number"
              :min="ventasRealizadasVehiculo"
              placeholder="Stock"
              class="w-full border p-2 rounded"
              required
            />

            <p
              v-if="modoEdicion && ventasRealizadasVehiculo > 0"
              class="text-xs text-gray-500"
            >
              Stock mínimo permitido: {{ ventasRealizadasVehiculo }} (ventas ya
              realizadas)
            </p>

            <div class="flex justify-end gap-3 pt-4">
              <button
                type="button"
                @click="mostrarModal = false"
                class="px-4 py-2 border rounded"
              >
                Cancelar
              </button>

              <button class="bg-blue-600 text-white px-4 py-2 rounded">
                Guardar
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import api from "../services/api";
import Sidebar from "@/components/Sidebar.vue";

const router = useRouter();

const vehiculos = ref([]);

const mostrarModal = ref(false);

const modoEdicion = ref(false);

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
  const response = await api.get("/vehiculos");

  vehiculos.value = response.data;
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
  try {
    if (modoEdicion.value) {
      await api.put(`/vehiculos/${formulario.value.id}`, formulario.value);
    } else {
      await api.post("/vehiculos", formulario.value);
    }

    mostrarModal.value = false;
    await cargarVehiculos();
  } catch (error) {
    console.error(error);
    alert(error.response?.data?.mensaje || "Error guardando vehículo");
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

const eliminarVehiculo = async (id) => {
  if (!confirm("¿Eliminar vehículo?")) return;

  await api.delete(`/vehiculos/${id}`);

  cargarVehiculos();
};
</script>
