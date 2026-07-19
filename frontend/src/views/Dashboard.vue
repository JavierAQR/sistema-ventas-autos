<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col">
      <div class="p-6 border-b border-slate-700">
        <h2 class="text-xl font-bold">🚗 AutoVentas</h2>
      </div>

      <nav class="flex-1 p-4 space-y-2">
        <router-link
          to="/dashboard"
          class="block px-4 py-3 rounded-lg hover:bg-slate-700"
        >
          📊 Dashboard
        </router-link>

        <router-link
          to="/vehiculos"
          class="block px-4 py-3 rounded-lg hover:bg-slate-700"
        >
          🚙 Vehículos
        </router-link>

        <router-link
          to="/cotizaciones"
          class="block px-4 py-3 rounded-lg hover:bg-slate-700"
        >
          📋 Cotizaciones
        </router-link>

        <router-link
          to="/seguros"
          class="block px-4 py-3 rounded-lg hover:bg-slate-700"
        >
          🛡️ Seguros
        </router-link>
      </nav>

      <button
        @click="cerrarSesion"
        class="m-4 bg-red-600 hover:bg-red-700 py-2 rounded-lg"
      >
        🚪 Cerrar sesión
      </button>
    </aside>

    <!-- Contenido -->
    <main class="flex-1 p-8 overflow-auto">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
          Hola, {{ vendedor.nombre }} 👋
        </h1>

        <p class="text-gray-500">Resumen de gestión comercial</p>
      </header>

      <!-- KPIs -->
      <section class="grid grid-cols-1 md:grid-cols-5 gap-5 mb-8">
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
          <p class="text-gray-500">Prospectos</p>

          <h2 class="text-3xl font-bold">
            {{ kpis.total_prospectos }}
          </h2>
        </div>

        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
          <p class="text-gray-500">Ventas realizadas</p>

          <h2 class="text-3xl font-bold">
            {{ kpis.ventas_realizadas }}
          </h2>
        </div>

        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
          <p class="text-gray-500">Ventas fallidas</p>

          <h2 class="text-3xl font-bold">
            {{ kpis.ventas_fallidas }}
          </h2>
        </div>

        <div
          class="bg-white rounded-xl shadow p-5 border-l-4 border-yellow-500"
        >
          <p class="text-gray-500">Conversión</p>

          <h2 class="text-3xl font-bold">{{ kpis.tasa_conversion }}%</h2>
        </div>

        <div
          class="bg-white rounded-xl shadow p-5 border-l-4 border-purple-500"
        >
          <p class="text-gray-500">Seguros</p>

          <h2 class="text-3xl font-bold">
            {{ kpis.seguros_vinculados }}
          </h2>
        </div>
      </section>

      <!-- Embudo -->
      <section class="bg-white rounded-xl shadow p-6 mb-8">
        <h2 class="text-xl font-bold mb-5">Embudo de ventas</h2>

        <div class="space-y-3">
          <div class="bg-blue-500 text-white p-3 rounded-lg w-full">
            Prospección:
            {{ kpis.embudo.prospeccion }}
          </div>

          <div class="bg-yellow-500 text-white p-3 rounded-lg w-3/4 mx-auto">
            Calificación:
            {{ kpis.embudo.calificacion }}
          </div>

          <div class="bg-orange-500 text-white p-3 rounded-lg w-1/2 mx-auto">
            Negociación:
            {{ kpis.embudo.negociacion }}
          </div>

          <div class="bg-green-500 text-white p-3 rounded-lg w-1/3 mx-auto">
            Cierre:
            {{ kpis.embudo.cierre }}
          </div>
        </div>
      </section>

      <!-- Prospectos -->
      <section class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-5">
          <h2 class="text-xl font-bold">Prospectos recientes</h2>

          <button
            @click="nuevoProspecto"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
          >
            + Nuevo
          </button>
        </div>

        <table class="w-full">
          <thead>
            <tr class="border-b">
              <th class="text-left p-3">Nombre</th>

              <th class="text-left p-3">Vehículo</th>

              <th class="text-left p-3">Estado</th>

              <th class="text-left p-3">Email</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="item in prospectos"
              :key="item.id"
              class="border-b hover:bg-gray-50"
            >
              <td class="p-3">{{ item.nombre }} {{ item.apellido }}</td>

              <td class="p-3">
                {{ item.vehiculo_interes }}
              </td>

              <td class="p-3">
                <span class="px-3 py-1 rounded-full text-sm bg-gray-200">
                  {{ item.estado }}
                </span>
              </td>

              <td class="p-3">
                {{ item.email }}
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();

const vendedor = ref({
  nombre: "Vendedor",
});

const prospectos = ref([]);

const kpis = ref({
  total_prospectos: 0,
  ventas_realizadas: 0,
  ventas_fallidas: 0,
  tasa_conversion: 0,
  seguros_vinculados: 0,

  embudo: {
    prospeccion: 0,
    calificacion: 0,
    negociacion: 0,
    cierre: 0,
  },
});

const api = axios.create({
  baseURL: "http://localhost:8001/api",
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

const cargarDatos = async () => {
  try {
    const [dashboard, listaProspectos] = await Promise.all([
      api.get("/dashboard-kpis"),

      api.get("/prospectos"),
    ]);

    kpis.value = dashboard.data;

    prospectos.value = listaProspectos.data;
  } catch (error) {
    console.error(error);

    if (error.response?.status === 401) {
      cerrarSesion();
    }
  }
};

const nuevoProspecto = () => {
  router.push("/prospectos");
};

const cerrarSesion = () => {
  localStorage.removeItem("token");

  localStorage.removeItem("vendedor_data");

  router.push("/login");
};

onMounted(() => {
  const data = localStorage.getItem("vendedor_data");

  if (!data) {
    router.push("/login");

    return;
  }

  vendedor.value = JSON.parse(data);

  cargarDatos();
});
</script>
