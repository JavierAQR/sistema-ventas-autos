# Manual de Usuario — Sistema AutoVentas

## 1. Introducción

AutoVentas es un sistema web para la gestión integral de ventas de una concesionaria de vehículos. Permite administrar el ciclo completo de una venta: desde el registro de un cliente potencial (prospecto), pasando por la cotización de un vehículo, hasta el cierre de la venta y la oferta de un seguro asociado.

El sistema está organizado en 5 módulos principales, accesibles desde el menú lateral:

- 🧑‍💼 **Prospectos**
- 🚙 **Mis Vehículos**
- 📋 **Cotizaciones**
- 💰 **Ventas**
- 🛡️ **Seguros**

---

## 2. Acceso al sistema

### Cómo ingresar

1. Abre el sistema en el navegador (URL del frontend, ej: `http://localhost:5173`).
2. Ingresa tu **correo** y **contraseña** en el formulario de Login.
3. Presiona **Entrar**.

Si las credenciales son correctas, el sistema guarda tu sesión y te redirige automáticamente al **Panel Principal**. Si son incorrectas, se muestra un mensaje de error debajo del formulario (por ejemplo: _"Credenciales inválidas"_).

### Sesión activa

El sistema valida en cada pantalla que exista una sesión activa. Si no hay sesión (por ejemplo, si nunca iniciaste sesión o esta expiró), el sistema te redirige automáticamente a la pantalla de Login.

Para cerrar sesión manualmente, usa el botón **🚪 Cerrar Sesión** ubicado en la parte inferior del menú lateral, disponible en todas las pantallas.

> ⚠️ **Nota:** el sistema actualmente no distingue roles (todos los usuarios que inician sesión son "vendedores" y tienen acceso a los mismos módulos y funciones). Si en el futuro se agregan roles diferenciados (ej: administrador vs. vendedor), esta sección debe actualizarse.

---

## 3. Panel Principal (Dashboard)

Es la primera pantalla que se ve al iniciar sesión. Muestra un resumen general de la gestión comercial, con 3 secciones:

### 3.1 Indicadores (KPIs)

En la parte superior se muestran 5 tarjetas con métricas clave:

| Indicador             | Descripción                                                                       |
| --------------------- | --------------------------------------------------------------------------------- |
| **Prospectos**        | Cantidad total de prospectos registrados en el sistema                            |
| **Ventas realizadas** | Cantidad de ventas con estado "Realizada"                                         |
| **Ventas fallidas**   | Cantidad de ventas con estado "Fallida"                                           |
| **Conversión**        | Porcentaje de ventas realizadas respecto al total gestionado (tasa de conversión) |
| **Seguros**           | Cantidad de seguros vinculados a ventas                                           |

### 3.2 Embudo de ventas

Un gráfico de barras horizontales que muestra cuántos prospectos hay actualmente en cada etapa del embudo (**Prospección → Calificación → Negociación → Cierre**). Es una forma rápida de visualizar en qué etapa se concentra la mayoría de los clientes potenciales, y sirve como referencia visual del mismo embudo descrito en la sección 4 de este manual.

### 3.3 Prospectos recientes

Una tabla con el listado de prospectos (nombre, vehículo de interés, estado y email). Incluye un botón **+ Nuevo**, que lleva directo al módulo de Prospectos para registrar uno nuevo.

> ⚠️ Si la sesión expiró o las credenciales dejaron de ser válidas al intentar cargar esta información, el sistema cierra la sesión automáticamente y redirige al Login.

---

## 4. Módulo de Prospectos 🧑‍💼

### ¿Qué es un prospecto?

Es un cliente potencial interesado en algún vehículo del catálogo. Es el primer registro que se crea en el sistema al iniciar una relación comercial con un cliente.

### Campos del prospecto

| Campo               | Obligatorio | Descripción                                                                 |
| ------------------- | ----------- | --------------------------------------------------------------------------- |
| Nombre              | Sí          | Nombre del cliente                                                          |
| Apellido            | Sí          | Apellido del cliente                                                        |
| Email               | Sí          | Correo de contacto                                                          |
| Teléfono            | No          | Teléfono de contacto                                                        |
| Vehículo de interés | Sí          | Texto libre describiendo el vehículo que le interesa (ej: "Toyota Corolla") |
| Estado              | —           | Ver sección de estados más abajo                                            |

### Acciones disponibles

- **+ Nuevo Prospecto**: abre un formulario para registrar un cliente nuevo. Al crearse, el estado inicial siempre es **Prospección**.
- **✏️ Editar**: permite modificar los datos del prospecto, incluyendo su estado.
- **🗑️ Eliminar**: borra el prospecto del sistema (pide confirmación).

### Estados del prospecto y cómo cambian

El prospecto avanza por un embudo de 4 etapas:

1. **Prospección** — estado inicial, recién registrado.
2. **Calificación** — el vendedor confirmó que el cliente tiene interés real y capacidad de compra.
3. **Negociación** — ya se le generó al menos una cotización.
4. **Cierre** — la venta se concretó.

**Importante:** algunos cambios de estado son **manuales** (los hace el vendedor a mano desde el botón Editar) y otros son **automáticos** (el sistema los cambia solo, según las acciones que se hagan en otros módulos):

| Transición                             | Tipo       | Cuándo ocurre                                                                               |
| -------------------------------------- | ---------- | ------------------------------------------------------------------------------------------- |
| → Prospección                          | Automático | Al crear el prospecto                                                                       |
| Prospección → Calificación             | **Manual** | El vendedor lo edita a mano cuando confirma el interés del cliente                          |
| Prospección/Calificación → Negociación | Automático | Se le crea la primera cotización                                                            |
| Negociación → Cierre                   | Automático | Se aprueba una cotización (genera la venta), o se registra/edita una venta como "realizada" |
| Negociación → Calificación             | Automático | Se rechaza su única cotización activa (retrocede si no tiene otra pendiente o aprobada)     |
| Cierre → Negociación                   | Automático | Su única venta realizada pasa a "fallida" (retrocede si no tiene otra venta realizada)      |

**En la práctica:** el vendedor solo necesita cambiar manualmente el estado a **Calificación**. Los demás cambios (Negociación, Cierre, y sus retrocesos) los hace el sistema solo, como consecuencia de trabajar en Cotizaciones y Ventas — no hace falta ir a editar el prospecto para actualizarlos.

---

## 5. Módulo de Vehículos 🚙

### ¿Qué es?

El catálogo de vehículos disponibles para la venta.

### Campos del vehículo

| Campo  | Obligatorio | Descripción                            |
| ------ | ----------- | -------------------------------------- |
| Marca  | Sí          | Ej: Toyota                             |
| Modelo | Sí          | Ej: Corolla                            |
| Año    | Sí          | Año del vehículo                       |
| Precio | Sí          | Precio de lista                        |
| Stock  | Sí          | Cantidad total de unidades registradas |

### Stock disponible vs. Stock total

La tabla muestra **"X / Y disponibles"**, donde:

- **Y** = stock total cargado en el sistema.
- **X** = stock disponible en este momento (se calcula automáticamente restando las ventas ya realizadas).

**No es necesario descontar el stock manualmente al hacer una venta** — el sistema lo calcula solo en tiempo real cada vez que consulta el catálogo.

### Restricciones importantes

- **No se puede editar el stock por debajo de la cantidad ya vendida.** Por ejemplo, si ya se vendieron 5 unidades de un modelo, no se puede bajar el stock total a menos de 5 — el sistema mostrará un mensaje de error indicando el mínimo permitido.
- **No se puede cotizar ni vender un vehículo sin stock disponible.** Si el stock disponible llega a 0, el sistema bloquea la creación de nuevas cotizaciones y ventas para ese vehículo hasta que se aumente el stock o se libere alguna unidad (por ejemplo, si una venta se marca como fallida).

---

## 6. Módulo de Cotizaciones 📋

### ¿Qué es?

Una propuesta formal de precio para un prospecto sobre un vehículo específico. Es el paso previo a concretar una venta.

### Cómo crear una cotización

1. Selecciona el **prospecto**.
2. El sistema busca automáticamente si el prospecto tiene un vehículo de interés registrado que coincida con el catálogo, y autocompleta el vehículo y el precio si encuentra coincidencia.
3. Ajusta el **precio final** si es necesario.
4. Define el **estado**: Pendiente, Aprobada o Rechazada.

### Estados de la cotización

- **Pendiente**: recién creada, en negociación.
- **Aprobada**: el cliente aceptó — **esto genera automáticamente una Venta** con estado "realizada", y mueve al prospecto a estado "Cierre".
- **Rechazada**: el cliente no aceptó. Si era la única cotización activa del prospecto, este retrocede automáticamente a "Calificación".

### Restricciones importantes

- No se puede crear ni aprobar una cotización si el vehículo no tiene stock disponible.

---

## 7. Módulo de Ventas 💰

### ¿Qué es?

La transacción final. Puede generarse automáticamente al aprobar una cotización, o registrarse manualmente desde este módulo.

### Cómo crear una venta manualmente

1. Selecciona una **cotización aprobada** de la lista (solo aparecen las que ya están en estado "Aprobada").
2. El monto de venta se autocompleta con el precio final de la cotización.
3. Define el **estado**: Realizada o Fallida.

### Sobre el motivo de pérdida

El campo **"Motivo de pérdida"** solo aplica y es obligatorio cuando el estado de la venta es **Fallida**. Sirve para registrar por qué no se concretó la venta (ej: "el cliente no calificó para el crédito", "compró en la competencia"). Si el estado es "Realizada", este campo no se muestra ni se solicita.

### Edición de una venta

- El cliente y vehículo asociados **no se pueden modificar** una vez creada la venta (se muestran solo como referencia informativa).
- Sí se puede editar el monto y el estado.
- Si se cambia el estado de "Realizada" a "Fallida", el prospecto asociado retrocede automáticamente a "Negociación" (salvo que tenga otra venta realizada que respalde su estado de "Cierre").

### Restricciones importantes

- No se puede registrar o pasar una venta a estado "Realizada" si el vehículo no tiene stock disponible en ese momento.

---

## 8. Módulo de Seguros 🛡️

### ¿Qué es?

Un producto adicional que se ofrece **después** de concretar una venta. Cada venta puede tener como máximo un seguro asociado.

### Requisito previo

Solo se puede registrar un seguro sobre una **venta ya realizada**. El selector de "Venta realizada" en el formulario solo muestra ventas con ese estado. Si una venta ya tiene un seguro asociado, no se puede crear otro para la misma venta — hay que editarlo en vez de duplicarlo.

### Campos del seguro

| Campo           | Obligatorio              | Descripción                                    |
| --------------- | ------------------------ | ---------------------------------------------- |
| Venta realizada | Sí                       | La venta a la que se asocia el seguro          |
| Aseguradora     | Sí                       | Ej: Rimac                                      |
| Tipo de seguro  | Sí                       | Todo Riesgo / Daños a terceros / Pérdida Total |
| Prima esperada  | Sí                       | Monto estimado de la prima                     |
| Prima real      | Solo si estado = Vendido | Monto final acordado                           |
| Estado          | Sí                       | Prospectado / Vendido                          |
| Observaciones   | No                       | Notas adicionales                              |

### Estados del seguro

- **Prospectado**: se le ofreció el seguro al cliente, aún no confirma.
- **Vendido**: el cliente aceptó comprar el seguro.

**Este cambio de estado es siempre manual** — a diferencia de los prospectos, el sistema no puede saber por sí solo si el cliente aceptó el seguro; el vendedor debe actualizarlo a mano cuando cierre esa negociación con el cliente.

---
