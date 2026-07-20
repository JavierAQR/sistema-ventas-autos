<template>
  <div class="flex h-screen bg-gray-50">
    <!-- SIDEBAR -->
    <sidebar />

    <!-- CONTENIDO -->
    <main class="flex-1 p-8 overflow-y-auto">
      <header class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
            <Users :size="24" class="text-blue-600" />
            Prospectos
          </h1>
          <p class="text-gray-500 text-sm mt-1">
            Administra los clientes potenciales interesados en tus vehículos.
          </p>
        </div>
      </header>

      <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-5">
          <h2 class="text-lg font-bold text-slate-900">
            Listado de prospectos
          </h2>

          <button
            @click="abrirModalNuevo"
            class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
          >
            <Plus :size="16" />
            Nuevo prospecto
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 text-gray-500">
                <th class="text-left font-medium p-3">Nombre</th>
                <th class="text-left font-medium p-3">Contacto</th>
                <th class="text-left font-medium p-3">Vehículo de interés</th>
                <th class="text-left font-medium p-3">Estado</th>
                <th class="text-right font-medium p-3">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="prospecto in prospectos"
                :key="prospecto.id"
                class="border-b border-gray-50 hover:bg-gray-50 transition"
              >
                <td class="p-3 font-medium text-slate-900">
                  {{ prospecto.nombre }} {{ prospecto.apellido }}
                </td>

                <td class="p-3 text-gray-600">
                  <div class="flex items-center gap-1.5">
                    <Mail :size="14" class="text-gray-400 shrink-0" />
                    {{ prospecto.email }}
                  </div>
                  <div
                    v-if="prospecto.telefono"
                    class="flex items-center gap-1.5 mt-0.5"
                  >
                    <Phone :size="14" class="text-gray-400 shrink-0" />
                    {{ prospecto.telefono }}
                  </div>
                </td>

                <td class="p-3 text-gray-600">
                  <div class="flex items-center gap-1.5">
                    <Car :size="14" class="text-gray-400 shrink-0" />
                    {{ prospecto.vehiculo_interes }}
                  </div>
                </td>

                <td class="p-3">
                  <span
                    class="px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="estadoClases[prospecto.estado]"
                  >
                    {{ estadoLabels[prospecto.estado] }}
                  </span>
                </td>

                <td class="p-3">
                  <div class="flex items-center justify-end gap-1">
                    <button
                      @click="abrirModalEditar(prospecto)"
                      class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                      aria-label="Editar prospecto"
                    >
                      <Pencil :size="16" />
                    </button>

                    <button
                      @click="eliminarProspecto(prospecto.id)"
                      class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                      aria-label="Eliminar prospecto"
                    >
                      <Trash2 :size="16" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="prospectos.length === 0">
                <td colspan="5" class="text-center p-10 text-gray-400">
                  <UserX :size="28" class="mx-auto mb-2" />
                  No hay prospectos registrados.
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
                {{ modoEdicion ? "Editar prospecto" : "Nuevo prospecto" }}
              </h3>

              <button
                @click="mostrarModal = false"
                class="text-gray-400 hover:text-gray-600 transition"
                aria-label="Cerrar"
              >
                <X :size="20" />
              </button>
            </div>

            <form @submit.prevent="guardarProspecto" class="space-y-3.5">
              <div class="grid grid-cols-2 gap-3">
                <div class="relative">
                  <User
                    :size="16"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                  />
                  <input
                    v-model="formulario.nombre"
                    placeholder="Nombre"
                    class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    required
                  />
                </div>

                <input
                  v-model="formulario.apellido"
                  placeholder="Apellido"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  required
                />
              </div>

              <div class="relative">
                <Mail
                  :size="16"
                  class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                />
                <input
                  v-model="formulario.email"
                  type="email"
                  placeholder="Correo electrónico"
                  class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  required
                />
              </div>

              <div class="relative">
                <Phone
                  :size="16"
                  class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                />
                <input
                  v-model="formulario.telefono"
                  placeholder="Teléfono (opcional)"
                  class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                />
              </div>

              <div class="relative">
                <Car
                  :size="16"
                  class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                />
                <input
                  v-model="formulario.vehiculo_interes"
                  placeholder="Vehículo de interés (ej: Toyota Corolla)"
                  class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  required
                />
              </div>

              <div>
                <label class="text-sm font-medium text-slate-700 block mb-1.5">
                  Estado
                </label>

                <select
                  v-model="formulario.estado"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
                  <option value="prospeccion">Prospección</option>
                  <option value="calificacion">Calificación</option>
                  <option value="negociacion">Negociación</option>
                  <option value="cierre">Cierre</option>
                </select>
              </div>

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
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import {
  Users,
  Plus,
  Pencil,
  Trash2,
  Mail,
  Phone,
  Car,
  User,
  X,
  Loader2,
  UserX,
} from "@lucide/vue";
import api from "../services/api";
import Sidebar from "@/components/Sidebar.vue";

const router = useRouter();
const toast = useToast();

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
    toast.error("No se pudieron cargar los prospectos.");
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
      toast.success("Prospecto actualizado correctamente.");
    } else {
      await api.post("/prospectos", datos);
      toast.success("Prospecto registrado correctamente.");
    }

    mostrarModal.value = false;
    await cargarProspectos();
  } catch (error) {
    console.error("Error guardando prospecto:", error);
    toast.error(
      error.response?.data?.mensaje || "Error al guardar el prospecto",
    );
  } finally {
    cargando.value = false;
  }
};

const eliminarProspecto = async (id) => {
  if (!confirm("¿Seguro que deseas eliminar este prospecto?")) return;

  try {
    await api.delete(`/prospectos/${id}`);
    toast.success("Prospecto eliminado correctamente.");
    await cargarProspectos();
  } catch (error) {
    console.error("Error eliminando prospecto:", error);
    toast.error(
      error.response?.data?.mensaje || "No se pudo eliminar el prospecto",
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
