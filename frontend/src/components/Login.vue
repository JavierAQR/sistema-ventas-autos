<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">

      <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
        Sistema de Ventas - Login
      </h2>

      <form 
        @submit.prevent="iniciarSesion"
        class="space-y-4"
      >

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Correo
          </label>

          <input
            v-model="email"
            type="email"
            placeholder="Correo"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>


        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Contraseña
          </label>

          <input
            v-model="password"
            type="password"
            placeholder="Contraseña"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>


        <button
          type="submit"
          :disabled="cargando"
          class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
        >
          {{ cargando ? 'Ingresando...' : 'Entrar' }}
        </button>


        <p
          v-if="error"
          class="text-red-600 text-sm text-center"
        >
          {{ error }}
        </p>

      </form>

    </div>

  </div>
</template>


<script setup>

import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

const email = ref('javier@test.com')
const password = ref('12345678')

const error = ref('')
const cargando = ref(false)

const iniciarSesion = async () => {

  error.value = ''
  cargando.value = true

  try {

    const respuesta = await api.post('/login', {
      email: email.value,
      password: password.value
    })

    const datos = respuesta.data

    localStorage.setItem(
      'token',
      datos.access_token
    )

    localStorage.setItem(
      'vendedor_data',
      JSON.stringify(datos.vendedor)
    )

    router.push('/dashboard')

  } catch (e) {
    console.error(e)
    error.value =
      e.response?.data?.mensaje ||
      'Credenciales inválidas'
  } finally {
    cargando.value = false
  }
}

</script>
