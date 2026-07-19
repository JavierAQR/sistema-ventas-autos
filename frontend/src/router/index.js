import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import Vehiculos from '../views/Vehiculos.vue'
import Cotizaciones from '../views/Cotizaciones.vue'
import Seguros from '../views/Seguros.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    {
      path: '/dashboard', // ¡Esta línea faltaba!
      name: 'dashboard',
      component: Dashboard
    },
    { 
      path: '/vehiculos', 
      name: 'vehiculos', 
      component: Vehiculos 
    },
    { 
      path: '/cotizaciones', 
      name: 'cotizaciones', 
      component: Cotizaciones 
    },
    { 
      path: '/seguros', 
      name: 'seguros', 
      component: Seguros 
    },
    {
      path: '/', // Opcional: redirigir a dashboard si entran a la raíz
      redirect: '/dashboard'
    }
  ]
})

// El guardián debe estar configurado ANTES de exportar
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  // Si intenta ir a cualquier lugar que no sea login y no tiene token, lo echamos al login
  if (to.name !== 'login' && !token) {
    next({ name: 'login' });
  } else {
    next();
  }
});

export default router