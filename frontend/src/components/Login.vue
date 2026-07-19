<template>
  <div class="login-container">
    <h2>Sistema de Ventas - Login</h2>
    <form @submit.prevent="iniciarSesion">
      <input v-model="email" type="email" placeholder="Correo" required />
      <input v-model="password" type="password" placeholder="Contraseña" required />
      <button type="submit">Entrar</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router'; // 1. Importamos el router

const router = useRouter(); // 2. Inicializamos el router

const email = ref('carlos@autos.com');
const password = ref('password');

const iniciarSesion = async () => {
  try {
    const respuesta = await fetch('http://127.0.0.1:8001/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value })
    });

    const datos = await respuesta.json();
    
    if (respuesta.ok) {
      // 3. Guardamos ambos datos (token y los datos del vendedor)
      localStorage.setItem('token', datos.access_token);
      localStorage.setItem('vendedor_data', JSON.stringify({ nombre: datos.nombre || 'Vendedor' }));

      // 4. Redirección automática
      router.push('/dashboard');
    } else {
      alert('Error: ' + (datos.mensaje || 'Credenciales inválidas'));
    }
  } catch (error) {
    console.error('Error de conexión:', error);
    alert('No se pudo conectar al servidor.');
  }
};
</script>

<style scoped>
.login-container { max-width: 300px; margin: 50px auto; display: flex; flex-direction: column; gap: 10px; }
input { padding: 10px; }
button { padding: 10px; background: #2c3e50; color: white; cursor: pointer; }
</style>