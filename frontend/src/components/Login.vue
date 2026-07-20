<template>
  <div class="min-h-screen flex bg-gray-50">
    <!-- Panel de marca -->
    <div
      class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-slate-900 flex-col justify-between p-12"
    >
      <!-- Patrón de fondo sutil -->
      <div class="absolute inset-0 opacity-[0.07]">
        <div
          v-for="i in 6"
          :key="i"
          class="absolute border-t border-white"
          :style="{
            top: `${i * 16}%`,
            left: 0,
            right: 0,
            transform: `rotate(-6deg)`,
          }"
        />
      </div>

      <div class="relative z-10 flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center"
        >
          <Car :size="22" class="text-white" />
        </div>
        <span class="text-white font-bold text-lg tracking-tight"
          >AutoVentas</span
        >
      </div>

      <div class="relative z-10">
        <p
          class="text-blue-400 text-sm font-semibold tracking-wide uppercase mb-3"
        >
          Gestión comercial
        </p>
        <h1 class="text-4xl font-bold text-white leading-tight mb-4">
          Cada prospecto,<br />un paso más cerca del cierre.
        </h1>
        <p class="text-slate-400 text-base leading-relaxed max-w-md">
          Administra prospectos, cotizaciones, ventas y seguros desde un solo
          lugar.
        </p>
      </div>

      <div class="relative z-10 text-slate-500 text-sm">
        © {{ anioActual }} AutoVentas. Todos los derechos reservados.
      </div>
    </div>

    <!-- Panel de formulario -->
    <div class="flex-1 flex items-center justify-center px-6 py-12">
      <div class="w-full max-w-sm">
        <!-- Marca visible solo en mobile -->
        <div class="flex lg:hidden items-center gap-3 mb-10 justify-center">
          <div
            class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center"
          >
            <Car :size="22" class="text-white" />
          </div>
          <span class="text-slate-900 font-bold text-lg tracking-tight"
            >AutoVentas</span
          >
        </div>

        <div class="mb-8">
          <h2 class="text-2xl font-bold text-slate-900 mb-1">Inicia sesión</h2>
          <p class="text-gray-500 text-sm">
            Ingresa tus credenciales para acceder al panel.
          </p>
        </div>

        <form @submit.prevent="iniciarSesion" class="space-y-5" novalidate>
          <!-- Correo -->
          <div>
            <label
              for="email"
              class="block text-sm font-medium text-slate-700 mb-1.5"
            >
              Correo
            </label>

            <div class="relative">
              <Mail
                :size="18"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
              />

              <input
                id="email"
                v-model="email"
                type="email"
                autocomplete="username"
                placeholder="nombre@empresa.com"
                required
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm text-slate-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              />
            </div>
          </div>

          <!-- Contraseña -->
          <div>
            <label
              for="password"
              class="block text-sm font-medium text-slate-700 mb-1.5"
            >
              Contraseña
            </label>

            <div class="relative">
              <Lock
                :size="18"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
              />

              <input
                id="password"
                v-model="password"
                :type="mostrarPassword ? 'text' : 'password'"
                autocomplete="current-password"
                placeholder="••••••••"
                required
                class="w-full pl-10 pr-11 py-2.5 border border-gray-300 rounded-lg text-sm text-slate-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              />

              <button
                type="button"
                @click="mostrarPassword = !mostrarPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-slate-600 transition"
                :aria-label="
                  mostrarPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'
                "
                tabindex="-1"
              >
                <EyeOff v-if="mostrarPassword" :size="18" />
                <Eye v-else :size="18" />
              </button>
            </div>
          </div>

          <button
            type="submit"
            :disabled="cargando"
            class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-lg hover:bg-blue-700 active:bg-blue-800 transition disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <Loader2 v-if="cargando" :size="18" class="animate-spin" />
            {{ cargando ? "Ingresando..." : "Entrar" }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { Car, Mail, Lock, Eye, EyeOff, Loader2 } from "@lucide/vue";
import api from "../services/api";

const router = useRouter();
const toast = useToast();

const email = ref("");
const password = ref("");
const mostrarPassword = ref(false);
const cargando = ref(false);

const anioActual = new Date().getFullYear();

const iniciarSesion = async () => {
  cargando.value = true;

  try {
    const respuesta = await api.post("/login", {
      email: email.value,
      password: password.value,
    });

    const datos = respuesta.data;

    localStorage.setItem("token", datos.access_token);
    localStorage.setItem("vendedor_data", JSON.stringify(datos.vendedor));

    toast.success(`Bienvenido, ${datos.vendedor?.nombre ?? "vendedor"}`);

    router.push("/dashboard");
  } catch (e) {
    console.error(e);
    toast.error(e.response?.data?.mensaje || "Credenciales inválidas");
  } finally {
    cargando.value = false;
  }
};
</script>
