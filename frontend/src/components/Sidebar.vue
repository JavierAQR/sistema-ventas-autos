<template>
  <aside class="w-64 bg-slate-900 text-white flex flex-col">
    <div class="p-5 text-center border-b border-slate-700">
      <h2 class="text-xl font-bold">🚗 AutoVentas</h2>
    </div>

    <nav class="flex-1 py-5 flex flex-col">
      <router-link
        to="/dashboard"
        active-class="bg-slate-700 border-l-4 border-blue-500"
        class="px-5 py-3 text-gray-300 hover:bg-slate-800 transition"
      >
        📊 Panel Principal
      </router-link>

      <router-link
        to="/prospectos"
        active-class="bg-slate-700 border-l-4 border-blue-500"
        class="px-5 py-3 text-gray-300 hover:bg-slate-800 transition"
      >
        🧑‍💼 Prospectos
      </router-link>

      <router-link
        to="/vehiculos"
        active-class="bg-slate-700 border-l-4 border-blue-500"
        class="px-5 py-3 text-gray-300 hover:bg-slate-800 transition"
      >
        🚙 Mis Vehículos
      </router-link>

      <router-link
        to="/cotizaciones"
        active-class="bg-slate-700 border-l-4 border-blue-500"
        class="px-5 py-3 text-gray-300 hover:bg-slate-800 transition"
      >
        📋 Cotizaciones
      </router-link>

      <router-link
        to="/ventas"
        active-class="bg-slate-700 border-l-4 border-blue-500"
        class="px-5 py-3 text-gray-300 hover:bg-slate-800 transition"
      >
        💰 Ventas
      </router-link>

      <router-link
        to="/seguros"
        active-class="bg-slate-700 border-l-4 border-blue-500"
        class="px-5 py-3 text-gray-300 hover:bg-slate-800 transition"
      >
        🛡️ Seguros
      </router-link>
    </nav>

    <div class="p-5">
      <button
        @click="cerrarSesion"
        class="w-full bg-red-600 hover:bg-red-700 py-3 rounded-lg transition"
      >
        🚪 Cerrar Sesión
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRouter } from "vue-router";

const router = useRouter();

const cerrarSesion = () => {
  localStorage.removeItem("token");
  localStorage.removeItem("vendedor_data");
  router.push("/login");
};
</script>
