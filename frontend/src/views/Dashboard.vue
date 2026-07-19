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
          <h1>Hola, {{ nombreVendedor }} 👋</h1>
          <p>Aquí tienes el resumen de tu gestión comercial al día de hoy.</p>
        </div>
      </header>

      <!-- Menú actualizado (Añade esta línea en tu <nav> de todos tus archivos) -->
      <!-- <router-link to="/seguros" active-class="active">🛡️ Seguros</router-link> -->

      <section class="kpi-grid">
        <div class="kpi-card azul">
          <div class="kpi-info"><h3>{{ kpis.total_prospectos }}</h3><p>Prospectos en Proceso</p></div>
        </div>
        <div class="kpi-card verde">
          <div class="kpi-info"><h3>{{ kpis.ventas_realizadas }}</h3><p>Ventas Realizadas</p></div>
        </div>
        <div class="kpi-card rojo">
          <div class="kpi-info"><h3>{{ kpis.ventas_fallidas }}</h3><p>Ventas Fallidas</p></div>
        </div>
        <div class="kpi-card amarillo">
          <div class="kpi-info"><h3>{{ kpis.tasa_conversion }}%</h3><p>Tasa de Conversión</p></div>
        </div>
        <div class="kpi-card morado">
          <div class="kpi-info"><h3>{{ kpis.seguros_vinculados }}</h3><p>Seguros Vinculados</p></div>
        </div>
      </section>

      <!-- Embudo de Ventas (Requerimiento de la rúbrica) -->
      <section class="recent-prospects" style="margin-bottom: 30px;">
        <h2>Funnel / Embudo de Ventas</h2>
        <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
          <div style="background: #3498db; color: white; padding: 10px; border-radius: 5px; width: 100%;">
            1. Total Leads Registrados ({{ kpis.embudo?.leads_totales || 0 }})
          </div>
          <div style="background: #f1c40f; color: white; padding: 10px; border-radius: 5px; width: 75%; margin: 0 auto;">
            2. Contactados y Negociando ({{ kpis.embudo?.contactados || 0 }})
          </div>
          <div style="background: #2ecc71; color: white; padding: 10px; border-radius: 5px; width: 50%; margin: 0 auto;">
            3. Ventas Cerradas Exitosamente ({{ kpis.embudo?.ventas || 0 }})
          </div>
        </div>
      </section>

      <section class="recent-prospects">
        <div class="section-header">
          <h2>Últimos Prospectos Registrados</h2>
          <button @click="abrirModalNuevo" class="btn-primary">+ Nuevo Prospecto</button>
        </div>
        
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Vehículo de Interés</th>
                <th>Estado</th>
                <th>Email</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="prospectos.length === 0">
                <td colspan="5" class="text-center">No hay prospectos registrados aún.</td>
              </tr>
              <tr v-for="prospecto in prospectos" :key="prospecto.id">
                <td>{{ prospecto.nombre }} {{ prospecto.apellido }}</td>
                <td>{{ prospecto.vehiculo_interes }}</td>
                <td><span class="badge" :class="prospecto.estado">{{ prospecto.estado }}</span></td>
                <td>{{ prospecto.email }}</td>
                <td>
                  <button @click="abrirModalEditar(prospecto)" class="btn-icon edit">✏️</button>
                  <button @click="eliminarProspecto(prospecto.id)" class="btn-icon delete">🗑️</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <div v-if="mostrarModal" class="modal-overlay">
        <div class="modal">
          <h3>{{ modoEdicion ? 'Editar Prospecto' : 'Registrar Nuevo Prospecto' }}</h3>
          <form @submit.prevent="guardarProspecto">
            <input v-model="formulario.nombre" placeholder="Nombre" required />
            <input v-model="formulario.apellido" placeholder="Apellido" required />
            <input v-model="formulario.email" type="email" placeholder="Correo Electrónico" required />
            
            <select v-model="formulario.vehiculo_interes" required>
              <option value="" disabled>Selecciona el Vehículo de Interés...</option>
              <option v-for="vehiculo in vehiculos" :key="vehiculo.id" :value="vehiculo.marca + ' ' + vehiculo.modelo">
                {{ vehiculo.marca }} {{ vehiculo.modelo }} ({{ vehiculo.stock }} disponibles)
              </option>
            </select>
            
            <select v-if="modoEdicion" v-model="formulario.estado" required>
              <option value="nuevo">Nuevo</option>
              <option value="en_contacto">En Contacto</option>
              <option value="cerrado">Cerrado</option>
            </select>

            <div class="modal-actions">
              <button type="button" @click="mostrarModal = false" class="btn-cancel">Cancelar</button>
              <button type="submit" class="btn-primary" :disabled="cargando">
                {{ cargando ? 'Guardando...' : 'Guardar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const nombreVendedor = ref('Vendedor');
const prospectos = ref([]);
const vehiculos = ref([]);
const mostrarModal = ref(false);
const cargando = ref(false);
const modoEdicion = ref(false);

// KPIs reactivos inicializados en cero
const kpis = ref({ total_prospectos: 0, prospectos_nuevos: 0, autos_disponibles: 0, total_ventas: 0, cotizaciones_pendientes: 0 });

const formulario = ref({ id: null, nombre: '', apellido: '', email: '', vehiculo_interes: '', estado: 'nuevo' });

onMounted(() => {
  const data = localStorage.getItem('vendedor_data');
  if (!data) return router.push('/login');
  nombreVendedor.value = JSON.parse(data).nombre || 'Vendedor';
  
  cargarDashboard();
});

// Cargar todo en una sola tanda
const cargarDashboard = async () => {
  await cargarProspectos();
  await cargarVehiculos();
  await cargarKPIs(); // Carga las métricas reales
};

const cargarKPIs = async () => {
  const token = localStorage.getItem('token');
  const res = await fetch('http://127.0.0.1:8001/api/dashboard-kpis', {
    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
  });
  if (res.ok) kpis.value = await res.json();
};

const cargarProspectos = async () => {
  const token = localStorage.getItem('token');
  const res = await fetch('http://127.0.0.1:8001/api/prospectos', {
    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
  });
  if (res.ok) prospectos.value = await res.json();
};

const cargarVehiculos = async () => {
  const token = localStorage.getItem('token');
  const res = await fetch('http://127.0.0.1:8001/api/vehiculos', {
    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
  });
  if (res.ok) vehiculos.value = await res.json();
};

const abrirModalNuevo = () => {
  modoEdicion.value = false;
  formulario.value = { id: null, nombre: '', apellido: '', email: '', vehiculo_interes: '', estado: 'nuevo' };
  mostrarModal.value = true;
};

const abrirModalEditar = (prospecto) => {
  modoEdicion.value = true;
  formulario.value = { ...prospecto };
  mostrarModal.value = true;
};

const guardarProspecto = async () => {
  cargando.value = true;
  const token = localStorage.getItem('token');
  const url = modoEdicion.value ? `http://127.0.0.1:8001/api/prospectos/${formulario.value.id}` : 'http://127.0.0.1:8001/api/prospectos';
  const metodo = modoEdicion.value ? 'PUT' : 'POST';

  try {
    const res = await fetch(url, {
      method: metodo,
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify(formulario.value)
    });

    if (res.ok) {
      mostrarModal.value = false;
      cargarDashboard(); // Recargamos todo para actualizar KPIs
    } else {
      alert("Hubo un error al guardar.");
    }
  } catch (error) {
    console.error("Error:", error);
  } finally {
    cargando.value = false;
  }
};

const eliminarProspecto = async (id) => {
  if (!confirm('¿Estás seguro de que deseas eliminar este prospecto?')) return;
  const token = localStorage.getItem('token');
  try {
    const res = await fetch(`http://127.0.0.1:8001/api/prospectos/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    if (res.ok) cargarDashboard();
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
/* Estilos Base del Layout */
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

/* Grid de Tarjetas KPI */
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
.kpi-card { background: white; padding: 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border-left: 5px solid #ccc; }
.kpi-card.azul { border-left-color: #3498db; }
.kpi-card.verde { border-left-color: #2ecc71; }
.kpi-card.amarillo { border-left-color: #f1c40f; }
.kpi-card.morado { border-left-color: #9b59b6; }
.kpi-info h3 { font-size: 24px; margin: 0 0 5px 0; color: #2c3e50; }
.kpi-info p { margin: 0; color: #7f8c8d; font-size: 14px; font-weight: bold; }
.kpi-icon { font-size: 32px; opacity: 0.8; }

/* Tabla y Secciones */
.recent-prospects { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); }
.section-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
.btn-primary { background-color: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 15px; text-align: left; border-bottom: 1px solid #ecf0f1; }
.text-center { text-align: center; color: #7f8c8d; }
.badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
.badge.nuevo { background-color: #d1f2eb; color: #1abc9c; }
.badge.en_contacto { background-color: #fcf3cf; color: #f1c40f; }
.badge.cerrado { background-color: #fadbd8; color: #e74c3c; }

/* Botones de acción en la tabla */
.btn-icon { background: none; border: none; font-size: 18px; cursor: pointer; margin-right: 10px; opacity: 0.7; transition: opacity 0.3s; }
.btn-icon:hover { opacity: 1; }

/* Modal */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: white; padding: 30px; border-radius: 10px; width: 400px; display: flex; flex-direction: column; gap: 12px; }
.modal input, .modal select { padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 100%; box-sizing: border-box; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; }
.btn-cancel { background: white; border: 1px solid #ccc; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
</style>