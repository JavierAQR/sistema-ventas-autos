<template>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="brand"><h2>🚗 AutoVentas</h2></div>
      <nav class="menu">
        <router-link to="/dashboard" active-class="active">📊 Panel Principal</router-link>
        <router-link to="/vehiculos" active-class="active">🚙 Mis Vehículos</router-link>
        <router-link to="/cotizaciones" active-class="active">📋 Cotizaciones</router-link>
        <router-link to="/seguros" active-class="active">🛡️ Seguros</router-link>
      </nav>
      <div class="sidebar-footer">
        <button @click="cerrarSesion" class="btn-logout">🚪 Cerrar Sesión</button>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <div class="welcome-text">
          <h1>Gestión de Cotizaciones 📋</h1>
          <p>Cierra ventas asignando vehículos a tus prospectos.</p>
        </div>
      </header>

      <section class="recent-prospects">
        <div class="section-header">
          <h2>Cotizaciones Generadas</h2>
          <button @click="abrirModalNuevo" class="btn-primary">+ Nueva Cotización</button>
        </div>
        
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Prospecto</th>
                <th>Vehículo</th>
                <th>Precio Final</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="cotizaciones.length === 0">
                <td colspan="5" class="text-center">No hay cotizaciones registradas aún.</td>
              </tr>
              <tr v-for="cotizacion in cotizaciones" :key="cotizacion.id">
                <td>{{ cotizacion.prospecto?.nombre }} {{ cotizacion.prospecto?.apellido }}</td>
                <td>{{ cotizacion.vehiculo?.marca }} {{ cotizacion.vehiculo?.modelo }}</td>
                <td>S/ {{ cotizacion.precio_final }}</td>
                <td><span class="badge" :class="cotizacion.estado">{{ cotizacion.estado }}</span></td>
                <td>
                  <button @click="abrirModalEditar(cotizacion)" class="btn-icon edit">✏️</button>
                  <button @click="eliminarCotizacion(cotizacion.id)" class="btn-icon delete">🗑️</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <div v-if="mostrarModal" class="modal-overlay">
        <div class="modal">
          <h3>{{ modoEdicion ? 'Editar Cotización' : 'Generar Nueva Cotización' }}</h3>
          <form @submit.prevent="guardarCotizacion">
            
            <label>Selecciona el Prospecto:</label>
            <select v-model="formulario.prospecto_id" required>
              <option value="" disabled>Elige un cliente...</option>
              <option v-for="prospecto in prospectos" :key="prospecto.id" :value="prospecto.id">
                {{ prospecto.nombre }} {{ prospecto.apellido }} (Interés: {{ prospecto.vehiculo_interes }})
              </option>
            </select>

            <label>Selecciona el Vehículo:</label>
            <select v-model="formulario.vehiculo_id" required>
              <option value="" disabled>Elige un auto del inventario...</option>
              <option v-for="vehiculo in vehiculos" :key="vehiculo.id" :value="vehiculo.id">
                {{ vehiculo.marca }} {{ vehiculo.modelo }} - S/ {{ vehiculo.precio }}
              </option>
            </select>

            <label>Precio Negociado (S/):</label>
            <input v-model.number="formulario.precio_final" type="number" step="0.01" placeholder="Ej: 50000" required />

            <label>Estado de la Negociación:</label>
            <select v-model="formulario.estado" required>
              <option value="pendiente">Pendiente</option>
              <option value="aprobada">Aprobada</option>
              <option value="rechazada">Rechazada</option>
            </select>

            <label>Observaciones (Opcional):</label>
            <textarea v-model="formulario.observaciones" rows="2" placeholder="Detalles extra..."></textarea>

            <div class="modal-actions">
              <button type="button" @click="mostrarModal = false" class="btn-cancel">Cancelar</button>
              <button type="submit" class="btn-primary" :disabled="cargando">
                {{ cargando ? 'Guardando...' : 'Guardar Cotización' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'; // <-- Importamos 'watch' para observar cambios
import { useRouter } from 'vue-router';

const router = useRouter();
const cotizaciones = ref([]);
const prospectos = ref([]);
const vehiculos = ref([]);

const mostrarModal = ref(false);
const cargando = ref(false);
const modoEdicion = ref(false);

const formulario = ref({ id: null, prospecto_id: '', vehiculo_id: '', precio_final: '', estado: 'pendiente', observaciones: '' });

// OBSERVADOR (WATCHER): Monitorea la selección del prospecto
watch(() => formulario.value.prospecto_id, (nuevoId) => {
  // Solo queremos autocompletar si estamos creando una cotización nueva (no al editar una existente)
  if (modoEdicion.value || !nuevoId) return;

  // 1. Buscamos el prospecto seleccionado en la lista cargada
  const prospectoSeleccionado = prospectos.value.find(p => p.id === nuevoId);
  
  if (prospectoSeleccionado && prospectoSeleccionado.vehiculo_interes) {
    // 2. Buscamos en nuestro catálogo el auto que coincida con su "vehiculo_interes"
    const vehiculoCoincidente = vehiculos.value.find(v => {
      const nombreCompletoAuto = `${v.marca} ${v.modelo}`.trim().toLowerCase();
      return nombreCompletoAuto === prospectoSeleccionado.vehiculo_interes.trim().toLowerCase();
    });

    // 3. Si lo encontramos, rellenamos automáticamente el ID y el precio original de lista
    if (vehiculoCoincidente) {
      formulario.value.vehiculo_id = vehiculoCoincidente.id;
      formulario.value.precio_final = vehiculoCoincidente.precio;
    } else {
      // Si no hay coincidencia exacta (por registros antiguos hechos a mano), limpiamos los campos
      formulario.value.vehiculo_id = '';
      formulario.value.precio_final = '';
    }
  }
});

onMounted(() => {
  const data = localStorage.getItem('vendedor_data');
  if (!data) return router.push('/login');
  
  cargarCotizaciones();
  cargarListasDesplegables();
});

const cargarCotizaciones = async () => {
  const token = localStorage.getItem('token');
  const res = await fetch('http://127.0.0.1:8001/api/cotizaciones', {
    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
  });
  if (res.ok) cotizaciones.value = await res.json();
};

const cargarListasDesplegables = async () => {
  const token = localStorage.getItem('token');
  const config = { headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' } };
  
  const [resProspectos, resVehiculos] = await Promise.all([
    fetch('http://127.0.0.1:8001/api/prospectos', config),
    fetch('http://127.0.0.1:8001/api/vehiculos', config)
  ]);

  if (resProspectos.ok) prospectos.value = await resProspectos.json();
  if (resVehiculos.ok) vehiculos.value = await resVehiculos.json();
};

const abrirModalNuevo = () => {
  modoEdicion.value = false;
  formulario.value = { id: null, prospecto_id: '', vehiculo_id: '', precio_final: '', estado: 'pendiente', observaciones: '' };
  mostrarModal.value = true;
};

const abrirModalEditar = (cotizacion) => {
  modoEdicion.value = true;
  formulario.value = { ...cotizacion };
  mostrarModal.value = true;
};

const guardarCotizacion = async () => {
  cargando.value = true;
  const token = localStorage.getItem('token');
  const url = modoEdicion.value ? `http://127.0.0.1:8001/api/cotizaciones/${formulario.value.id}` : 'http://127.0.0.1:8001/api/cotizaciones';
  const metodo = modoEdicion.value ? 'PUT' : 'POST';

  try {
    const res = await fetch(url, {
      method: metodo,
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify(formulario.value)
    });

    if (res.ok) {
      mostrarModal.value = false;
      cargarCotizaciones();
    } else {
      alert("Hubo un error al guardar.");
    }
  } catch (error) {
    console.error("Error:", error);
  } finally {
    cargando.value = false;
  }
};

const eliminarCotizacion = async (id) => {
  if (!confirm('¿Seguro que deseas eliminar esta cotización?')) return;
  const token = localStorage.getItem('token');
  try {
    const res = await fetch(`http://127.0.0.1:8001/api/cotizaciones/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    if (res.ok) cargarCotizaciones();
  } catch (error) {
    console.error("Error al eliminar:", error);
  }
};

const cerrarSesion = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('vendedor_data');
  router.push('/login');
};
</script>

<style scoped>
/* Estilos existentes... */
.dashboard-layout { display: flex; height: 100vh; background-color: #f4f7f6; font-family: sans-serif; }
.sidebar { width: 250px; background-color: #1a252f; color: white; display: flex; flex-direction: column; }
.brand { padding: 20px; text-align: center; border-bottom: 1px solid #2c3e50; }
.menu { flex: 1; display: flex; flex-direction: column; padding: 20px 0; }
.menu a { color: #bdc3c7; text-decoration: none; padding: 15px 20px; }
.menu a.active { background-color: #2c3e50; color: white; border-left: 4px solid #3498db; }
.sidebar-footer { padding: 20px; }
.btn-logout { width: 100%; padding: 12px; background-color: #e74c3c; color: white; border: none; cursor: pointer; border-radius: 4px;}
.main-content { flex: 1; padding: 30px; overflow-y: auto; }
.topbar { margin-bottom: 30px; }
.recent-prospects { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.section-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
.btn-primary { background-color: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 15px; text-align: left; border-bottom: 1px solid #ecf0f1; }
.text-center { text-align: center; color: #7f8c8d; }
.badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: capitalize; }
.badge.pendiente { background-color: #fcf3cf; color: #f1c40f; }
.badge.aprobada { background-color: #d1f2eb; color: #1abc9c; }
.badge.rechazada { background-color: #fadbd8; color: #e74c3c; }
.btn-icon { background: none; border: none; font-size: 18px; cursor: pointer; margin-right: 10px; opacity: 0.7; transition: opacity 0.3s; }
.btn-icon:hover { opacity: 1; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: white; padding: 30px; border-radius: 10px; width: 400px; display: flex; flex-direction: column; gap: 10px; }
.modal label { font-size: 13px; font-weight: bold; color: #34495e; margin-top: 5px; }
.modal input, .modal select, .modal textarea { padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 100%; box-sizing: border-box; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; }
.btn-cancel { background: white; border: 1px solid #ccc; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
</style>