<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <sidebar />

    <!-- Contenido -->
    <main class="flex-1 p-8 overflow-auto">
      <header class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">
            Hola, {{ vendedor.nombre }} 👋
          </h1>
          <p class="text-gray-500 text-sm mt-1">
            Resumen de tu gestión comercial
          </p>
        </div>

        <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500">
          <CalendarDays :size="16" />
          {{ fechaHoy }}
        </div>
      </header>

      <!-- KPIs -->
      <section
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8"
      >
        <div
          v-for="kpi in tarjetasKpi"
          :key="kpi.label"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
        >
          <div class="flex items-start justify-between mb-3">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center"
              :class="kpi.bgIcon"
            >
              <component :is="kpi.icon" :size="20" :class="kpi.colorIcon" />
            </div>
          </div>

          <p class="text-gray-500 text-sm">{{ kpi.label }}</p>

          <h2 class="text-2xl font-bold text-slate-900 mt-0.5">
            <span v-if="!cargando">{{ kpi.valor }}</span>
            <span
              v-else
              class="inline-block w-12 h-6 bg-gray-200 rounded animate-pulse"
            />
          </h2>
        </div>
      </section>

      <!-- Embudo -->
      <section
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8"
      >
        <div class="flex items-center gap-2 mb-6">
          <Filter :size="18" class="text-slate-500" />
          <h2 class="text-lg font-bold text-slate-900">Embudo de ventas</h2>
        </div>

        <div class="space-y-4">
          <div
            v-for="etapa in etapasEmbudo"
            :key="etapa.key"
            class="flex items-center gap-4"
          >
            <span class="w-24 shrink-0 text-sm font-medium text-slate-600">
              {{ etapa.label }}
            </span>

            <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="etapa.color"
                :style="{ width: `${anchoBarra(etapa.valor)}%` }"
              />
            </div>

            <span
              class="w-8 shrink-0 text-right text-sm font-semibold text-slate-900"
            >
              {{ etapa.valor }}
            </span>
          </div>
        </div>
      </section>

      <!-- Prospectos -->
      <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-5">
          <div class="flex items-center gap-2">
            <Users :size="18" class="text-slate-500" />
            <h2 class="text-lg font-bold text-slate-900">
              Prospectos recientes
            </h2>
          </div>

          <button
            @click="nuevoProspecto"
            class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
          >
            <Plus :size="16" />
            Nuevo
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 text-gray-500">
                <th class="text-left font-medium p-3">Nombre</th>
                <th class="text-left font-medium p-3">Vehículo</th>
                <th class="text-left font-medium p-3">Estado</th>
                <th class="text-left font-medium p-3">Email</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="item in prospectos"
                :key="item.id"
                class="border-b border-gray-50 hover:bg-gray-50 transition"
              >
                <td class="p-3 font-medium text-slate-900">
                  {{ item.nombre }} {{ item.apellido }}
                </td>

                <td class="p-3 text-gray-600">
                  {{ item.vehiculo_interes }}
                </td>

                <td class="p-3">
                  <span
                    class="px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="
                      estadoClases[item.estado] ?? 'bg-gray-100 text-gray-600'
                    "
                  >
                    {{ estadoLabels[item.estado] ?? item.estado }}
                  </span>
                </td>

                <td class="p-3 text-gray-600">
                  {{ item.email }}
                </td>
              </tr>

              <tr v-if="!cargando && prospectos.length === 0">
                <td colspan="4" class="text-center p-8 text-gray-400">
                  <UserX :size="28" class="mx-auto mb-2" />
                  Aún no hay prospectos registrados.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import {
  Users,
  TrendingUp,
  TrendingDown,
  Percent,
  Shield,
  Filter,
  Plus,
  UserX,
  CalendarDays,
} from "@lucide/vue";
import Sidebar from "@/components/Sidebar.vue";
import api from "../services/api";

const router = useRouter();
const toast = useToast();

const vendedor = ref({ nombre: "Vendedor" });
const prospectos = ref([]);
const cargando = ref(true);

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

const fechaHoy = new Date().toLocaleDateString("es-PE", {
  weekday: "long",
  day: "numeric",
  month: "long",
});

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

const tarjetasKpi = computed(() => [
  {
    label: "Prospectos",
    valor: kpis.value.total_prospectos,
    icon: Users,
    bgIcon: "bg-blue-50",
    colorIcon: "text-blue-600",
  },
  {
    label: "Ventas realizadas",
    valor: kpis.value.ventas_realizadas,
    icon: TrendingUp,
    bgIcon: "bg-green-50",
    colorIcon: "text-green-600",
  },
  {
    label: "Ventas fallidas",
    valor: kpis.value.ventas_fallidas,
    icon: TrendingDown,
    bgIcon: "bg-red-50",
    colorIcon: "text-red-600",
  },
  {
    label: "Conversión",
    valor: `${kpis.value.tasa_conversion}%`,
    icon: Percent,
    bgIcon: "bg-yellow-50",
    colorIcon: "text-yellow-600",
  },
  {
    label: "Seguros",
    valor: kpis.value.seguros_vinculados,
    icon: Shield,
    bgIcon: "bg-purple-50",
    colorIcon: "text-purple-600",
  },
]);

const etapasEmbudo = computed(() => [
  {
    key: "prospeccion",
    label: "Prospección",
    valor: kpis.value.embudo.prospeccion,
    color: "bg-blue-500",
  },
  {
    key: "calificacion",
    label: "Calificación",
    valor: kpis.value.embudo.calificacion,
    color: "bg-yellow-500",
  },
  {
    key: "negociacion",
    label: "Negociación",
    valor: kpis.value.embudo.negociacion,
    color: "bg-orange-500",
  },
  {
    key: "cierre",
    label: "Cierre",
    valor: kpis.value.embudo.cierre,
    color: "bg-green-500",
  },
]);

// Ancho proporcional de cada barra respecto a la etapa con más prospectos (mínimo 6% para que siempre sea visible)
const anchoBarra = (valor) => {
  const maximo = Math.max(...etapasEmbudo.value.map((e) => e.valor), 1);
  return Math.max((valor / maximo) * 100, valor > 0 ? 6 : 0);
};

const cargarDatos = async () => {
  cargando.value = true;

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
      toast.error("Tu sesión expiró. Inicia sesión nuevamente.");
      cerrarSesion();
    } else {
      toast.error("No se pudo cargar la información del panel.");
    }
  } finally {
    cargando.value = false;
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
