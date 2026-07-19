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
          <h1>Administración de Seguros 🛡️</h1>
          <p>Vincula pólizas vehiculares a las ventas cerradas.</p>
        </div>
      </header>

      <section class="recent-prospects">
        <div class="section-header">
          <h2>Pólizas Activas</h2>
          <button @click="abrirModalNuevo" class="btn-primary">+ Registrar Póliza</button>
        </div>
        
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Vehículo Asegurado</th>
                <th>Aseguradora</th>
                <th>Cobertura</th>
                <th>Costo Anual</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="seguros.length === 0">
                <td colspan="6" class="text-center">No hay seguros vinculados aún.</td>
              </tr>
              <tr v-for="seguro in seguros" :key="seguro.id">
                <td>{{ seguro.cotizacion?.prospecto?.nombre }} {{ seguro.cotizacion?.prospecto?.apellido }}</td>
                <td>{{ seguro.cotizacion?.vehiculo?.marca }} {{ seguro.cotizacion?.vehiculo?.modelo }}</td>
                <td><strong>{{ seguro.aseguradora }}</strong></td>
                <td>{{ seguro.tipo_cobertura }}</td>
                <td>S/ {{ seguro.costo_anual }}</td>
                <td>
                  <button @click="eliminarSeguro(seguro.id)" class="btn-icon delete">🗑️</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <div v-if="mostrarModal" class="modal-overlay">
        <div class="modal">
          <h3>Vincular Nueva Póliza</h3>
          <form @submit.prevent="guardarSeguro">
            
            <label>Selecciona la Venta Cerrada (Cotización Aprobada):</label>
            <select v-model="formulario.cotizacion_id" required>
              <option value="" disabled>Elige una venta exitosa...</option>
              <option v-for="venta in cotizacionesAprobadas" :key="venta.id" :value="venta.id">
                {{ venta.prospecto?.nombre }} - {{ venta.vehiculo?.marca }} {{ venta.vehiculo?.modelo }}
              </option>
            </select>

            <label>Empresa Aseguradora:</label>
            <select v-model="formulario.aseguradora" required>
              <option value="" disabled>Selecciona...</option>
              <option value="Rimac">Rimac Seguros</option>
              <option value="Pacífico">Pacífico Seguros</option>
              <option value="Mapfre">Mapfre</option>
              <option value="La Positiva">La Positiva</option>
            </select>

            <label>Tipo de Cobertura:</label>
            <select v-model="formulario.tipo_cobertura" required>
              <option value="" disabled>Selecciona...</option>
              <option value="Todo Riesgo">Todo Riesgo (Full Cover)</option>
              <option value="Daños a Terceros">Daños a Terceros</option>
              <option value="Pérdida Total">Pérdida Total</option>
            </select>

            <label>Costo Anual (S/):</label>
            <input v-model.number="formulario.costo_anual" type="number" step="0.01" placeholder="Ej: 1500.00" required />

            <div class="modal-actions">
              <button type="button" @click="mostrarModal = false" class="btn-cancel">Cancelar</button>
              <button type="submit" class="btn-primary" :disabled="cargando">
                {{ cargando ? 'Guardando...' : 'Vincular Seguro' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const seguros = ref([]);
const cotizaciones = ref([]);
const mostrarModal = ref(false);
const cargando = ref(false);

const formulario = ref({ cotizacion_id: '', aseguradora: '', tipo_cobertura: '', costo_anual: '' });

// Propiedad computada: Solo mostramos cotizaciones "aprobadas" en el select
const cotizacionesAprobadas = computed(() => {
  return cotizaciones.value.filter(c => c.estado === 'aprobada');
});

onMounted(() => {
  const data = localStorage.getItem('vendedor_data');
  if (!data) return router.push('/login');
  
  cargarSeguros();
  cargarCotizaciones();
});

const cargarSeguros = async () => {
  const token = localStorage.getItem('token');
  const res = await fetch('http://127.0.0.1:8001/api/seguros', {
    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
  });
  if (res.ok) seguros.value = await res.json();
};

const cargarCotizaciones = async () => {
  const token = localStorage.getItem('token');
  const res = await fetch('http://127.0.0.1:8001/api/cotizaciones', {
    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
  });
  if (res.ok) cotizaciones.value = await res.json();
};

const abrirModalNuevo = () => {
  formulario.value = { cotizacion_id: '', aseguradora: '', tipo_cobertura: '', costo_anual: '' };
  mostrarModal.value = true;
};

const guardarSeguro = async () => {
  cargando.value = true;
  const token = localStorage.getItem('token');
  
  try {
    const res = await fetch('http://127.0.0.1:8001/api/seguros', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify(formulario.value)
    });

    if (res.ok) {
      mostrarModal.value = false;
      cargarSeguros();
    } else {
      alert("Hubo un error al guardar el seguro.");
    }
  } catch (error) {
    console.error("Error:", error);
  } finally {
    cargando.value = false;
  }
};

const eliminarSeguro = async (id) => {
  if (!confirm('¿Seguro que deseas desvincular este seguro?')) return;
  const token = localStorage.getItem('token');
  try {
    const res = await fetch(`http://127.0.0.1:8001/api/seguros/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    if (res.ok) cargarSeguros();
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
/* Los mismos estilos compartidos del Dashboard (kpi-grid, sidebar, modal, tables, etc.) */
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
.btn-primary { background-color: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;}
table { width: 100%; border-collapse: collapse; }
th, td { padding: 15px; text-align: left; border-bottom: 1px solid #ecf0f1; }
.text-center { text-align: center; color: #7f8c8d; }
.btn-icon { background: none; border: none; font-size: 18px; cursor: pointer; margin-right: 10px; opacity: 0.7; transition: opacity 0.3s; }
.btn-icon:hover { opacity: 1; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: white; padding: 30px; border-radius: 10px; width: 400px; display: flex; flex-direction: column; gap: 10px; }
.modal label { font-size: 13px; font-weight: bold; color: #34495e; margin-top: 5px; }
.modal input, .modal select { padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 100%; box-sizing: border-box; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; }
.btn-cancel { background: white; border: 1px solid #ccc; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
</style>