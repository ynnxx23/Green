# 04. Diseno y accesibilidad — Sistema de gestion de un restaurante

> **Estado:** borrador de planificacion (etapa 1 - sin programar, sin archivos HTML/CSS ejecutables).  
> **Idioma:** espanol.  
> **Contenido permitido:** especificacion de diseno, paleta, componentes, pautas, diagrama de estados (no interfaces finales implementadas).

---

## 1. Identidad visual y principios HCI

El sistema de gestion de un restaurante debe transmitir orden, rapidez y claridad. Un mesero debe ver de inmediato qué mesas están libres, ocupadas o reservadas, y cuál es el estado de cada pedido (pendiente, en preparacion, listo, entregado).

**Principios HCI aplicados:**
- **Ley de Hick:** reducir las opciones simultáneas innecesarias. El proceso de reserva se divide en pasos claros (1. Area/mesa → 2. Fecha → 3. Horario disponible → 4. Confirmar). El registro de pedido también se divide (seleccionar mesa → elegir productos → confirmar).
- **Consistencia:** los mismos componentes (botones, inputs, tarjetas de mesa, estados de pedido) se reutilizan en todas las pantallas.
- **Prevención de errores:** no se permite seleccionar una mesa ya ocupada; el botón "Reservar" se deshabilita si no hay selección válida; las acciones destructivas (cancelar reserva, cancelar pedido) piden confirmación.
- **Retroalimentación:** mensajes de error concretos junto al campo, indicadores de carga, estados de éxito y vacío.
- **Jerarquía visual:** lo más importante (disponibilidad de mesas, estado de pedidos, botón de acción principal) tiene mayor tamaño y contraste.

---

## 2. Paleta de colores y design tokens

**Valores concretos (hexadecimal).** Se definen como variables CSS reutilizables.

| Token | Valor | Uso |
|-------|-------|-----|
| `--color-primario` | `#1D4ED8` (azul-700) | Acciones principales, botón principal, enlaces importantes |
| `--color-primario-oscuro` | `#1E40AF` | Estado hover/activo del botón principal |
| `--color-secundario` | `#64748B` (slate-500) | Acciones secundarias, texto de apoyo |
| `--color-exito` | `#15803D` (green-700) | Estados de éxito, reservas confirmadas, pedidos entregados, mesa libre |
| `--color-advertencia` | `#B45309` (amber-700) | Estados de advertencia, reservas pendientes, pedidos en preparacion |
| `--color-error` | `#DC2626` (red-600) | Errores, reservas canceladas, pedidos cancelados, acciones destructivas |
| `--color-mesa-libre` | `#15803D` | Mesa disponible |
| `--color-mesa-ocupada` | `#DC2626` | Mesa ocupada / no disponible |
| `--color-mesa-reservada` | `#B45309` | Mesa reservada |
| `--color-pedido-pendiente` | `#64748B` | Pedido pendiente |
| `--color-pedido-preparacion` | `#2563EB` | Pedido en preparacion |
| `--color-pedido-listo` | `#15803D` | Pedido listo para entregar |
| `--color-fondo` | `#F8FAFC` (slate-50) | Fondo general de páginas |
| `--color-superficie` | `#FFFFFF` | Tarjetas, modales, formularios |
| `--color-texto-principal` | `#0F172A` (slate-900) | Texto principal |
| `--color-texto-secundario` | `#475569` (slate-600) | Texto secundario, etiquetas |
| `--color-texto-suave` | `#94A3B8` (slate-400) | Placeholder, texto deshabilitado |
| `--color-borde` | `#E2E8F0` (slate-200) | Bordes de inputs y tarjetas |
| `--color-borde-enfoque` | `#1D4ED8` | Borde de campo con foco |

**Uso del color para información:** nunca se comunica un estado solo mediante color. Cada estado (libre/ocupado/reservado/error/exito) incluye además texto, icono o patrón. Ejemplo: una mesa ocupada muestra el chip "Ocupada" con texto, no solo un círculo rojo.

---

## 3. Tipografía y escala tipográfica

- **Familia:** sistema sans-serif (`-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif`). Fuente del sistema para rendimiento y legibilidad; sin fuentes externas.
- **Tamaños base (rem):**
  - `--tamano-xs`: `0.75rem` (12px) — etiquetas pequeñas, notas
  - `--tamano-sm`: `0.875rem` (14px) — texto secundario, captions
  - `--tamano-base`: `1rem` (16px) — texto corporal, inputs
  - `--tamano-lg`: `1.125rem` (18px) — subtítulos de sección
  - `--tamano-xl`: `1.25rem` (20px) — títulos de página
  - `--tamano-2xl`: `1.5rem` (24px) — títulos principales
- **Pesos:** 400 (normal), 500 (mediano), 600 (seminegrita), 700 (negrita).
- **Altura de línea:** 1.5 para texto corporal; 1.25 para títulos.

---

## 4. Espaciado y márgenes

Escala base de 4 px (0.25 rem):

| Token | Valor | Uso |
|-------|-------|-----|
| `--espacio-1` | `0.25rem` (4px) | Espaciado interno muy ajustado |
| `--espacio-2` | `0.5rem` (8px) | Separación entre elementos relacionados |
| `--espacio-3` | `0.75rem` (12px) | Padding de inputs pequeños |
| `--espacio-4` | `1rem` (16px) | Padding estándar de tarjetas y formularios |
| `--espacio-6` | `1.5rem` (24px) | Separación entre secciones |
| `--espacio-8` | `2rem` (32px) | Márgenes de sección grande |
| `--espacio-12` | `3rem` (48px) | Separación entre bloques principales |

---

## 5. Botones

Variantes reutilizables, con estados definido (normal, hover, activo, deshabilitado, carga).

| Variante | Fondo | Texto | Borde | Uso |
|----------|-------|-------|-------|-----|
| Primaria | `--color-primario` | blanco | none | Acciones principales (Reservar, Guardar, Confirmar, Tomar pedido) |
| Secundaria | `--color-superficie` | `--color-texto-principal` | `--color-borde` | Acciones secundarias (Cancelar, Volver, Limpiar) |
| Éxito | `--color-exito` | blanco | none | Confirmar reserva, marcar pedido listo |
| Peligro | `--color-error` | blanco | none | Eliminar, cancelar reserva/pedido (acción destructiva) |
| Fantasma | transparente | `--color-secundario` | none | Enlaces con aspecto de botón |

**Tamaños:** `pequeno` (padding 0.5rem 1rem, texto sm), `mediano` (padding 0.625rem 1.25rem, texto base), `grande` (padding 0.75rem 1.5rem, texto base, ancho completo en móvil).

**Radio:** `0.375rem` (6px) en todos los botones.

**Estado de carga:** al hacer clic en "Reservar" o "Tomar pedido", el botón muestra indicador de carga (spinner) y se deshabilita para prevenir envíos duplicados.

---

## 6. Bordes, radios y sombras

| Token | Valor | Uso |
|-------|-------|-----|
| `--radio-sm` | `0.25rem` (4px) | Inputs pequeños, etiquetas |
| `--radio-base` | `0.375rem` (6px) | Inputs, tarjetas, botones, modales |
| `--radio-lg` | `0.5rem` (8px) | Tarjetas principales, contenedores grandes |
| `--sombra-sm` | `0 1px 2px 0 rgb(0 0 0 / 0.05)` | Inputs con foco, elementos sutiles |
| `--sombra-base` | `0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)` | Tarjetas, modales |
| `--sombra-lg` | `0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)` | Modales, menús desplegables |

---

## 7. Estados de componentes

Cada componente interactivo define estos estados visualmente:
- **Normal / reposo**
- **Hover** (solo dispositivos con puntero; no se basa en hover para funcionalidad crítica en táctil)
- **Foco** (anillo visible `--color-borde-enfoque` + outline, obligatorio para accesibilidad)
- **Activo / presionado**
- **Deshabilitado** (opacidad reducida, cursor no permitido, no ejecutable)
- **Carga** (spinner + deshabilitado)
- **Error** (borde rojo, mensaje de error asociado, icono)
- **Éxito** (borde verde, mensaje breve, icono)

---

## 8. Sistema de iconografía

- Iconos simples y semánticos (mesa libre, ocupada, reservada, usuario, calendario, reloj, pedido, cocina, entregar, cerrar, búsqueda, alerta, éxito).
- Se evalúa usar SVG embebidos en línea (sin librerías externas de iconos, sin dependencias de red). Cada icono informativo tiene su alternativa de texto o `aria-label`.
- No se usan fuentes de iconos externas (evitar dependencia y rastreo de terceros).

---

## 9. Jerarquía visual y composición

- **Títulos de página:** `--tamano-2xl`, peso 700, color `--color-texto-principal`.
- **Subtítulos de sección:** `--tamano-lg`, peso 600.
- **Etiquetas de campo:** `--tamano-base`, peso 500, color `--color-texto-principal`, asociadas a sus inputs mediante `<label for="...">`.
- **Texto de ayuda / nota:** `--tamano-sm`, color `--color-texto-secundario`.
- **Campos obligatorios:** identificados con texto "(obligatorio)" o asterisco con leyenda al inicio del formulario; no solo con color.
- **Distribución:** contenedor central con ancho máximo (max-width) en escritorio; ancho completo con padding en móvil.

---

## 10. Componentes y patrones compartidos

Se definen una vez y se reutilizan:
- **Btn** (botón, variantes arriba).
- **Input** (texto, email, contraseña, número, fecha, hora, select) con label, mensaje de error y estado.
- **TarjetaMesa** (numero, capacidad, estado libre/ocupada/reservada, botón de acción).
- **SelectorDeFecha** (input tipo date con validación).
- **GrillaDeHorarios** (bloques de hora disponibles/ocupados).
- **TarjetaPedido** (numero de pedido, mesa, mesero, estado, productos, botón de cambio de estado).
- **SelectorDeProducto** (lista de productos del menu con cantidad).
- **Alerta** (error, éxito, advertencia, información) con rol `alert` o `status`.
- **Modal** (fondo semitransparente, contenedor, cierre con ESC y clic fuera, foco atrapado).
- **Spinner** (indicador de carga).
- **TablaDeReservas** (lista de reservas con estado, acciones, fecha).
- **TablaDePedidos** (lista de pedidos con estado, mesero, mesa, acciones de cocina/mesero).
- **Navbar** (nombre del app, usuario, salir, administrador/mesero/cocinero si corresponde).
- **PieDePagina** (enlaces legales, versión).

Todos los componentes respetan los design tokens (colores, espacios, radios, sombras) definidos en esta sección.

---

## 11. Diseño responsive

**Puntos de quiebre (breakpoints):**
- `--bp-movil`: hasta `639px` (celulares).
- `--bp-tablet`: `640px` a `1023px` (tablets en horizontal y algunos portátiles pequeños).
- `--bp-escritorio`: `1024px` en adelante.

**Estrategia:** mobile-first. Los estilos base aplican a móvil y se añaden reglas con `@media (min-width: 640px)` y `@media (min-width: 1024px)`.

| Comportamiento | Móvil | Tablet / Escritorio |
|----------------|-------|---------------------|
| Navegación | Menú hamburguesa o barra inferior | Barra horizontal superior |
| Formulario de reserva | Una columna, paso a paso | Dos columnas (mesa + calendario) cuando quepa |
| Tabla de pedidos | Desplazable horizontal o tarjetas apiladas | Tabla completa |
| Grilla de horarios | Filas apiladas | Grilla horizontal de horas |
| Ancho de contenido | 100% con padding 1rem | Contenedor centrado max-width 80rem |

**Controles táctil:** áreas de interacción mínimas de `44x44 px` (recomendación accesibilidad). Evitar desbordamientos y contenido ilegible en pantallas pequeñas.

---

## 12. Accesibilidad (WCAG 2.2 nivel AA, objetivo de referencia)

La accesibilidad forma parte de los criterios de aceptación y pruebas (no es opcional).

| Requisito | Cómo se implementa |
|-----------|-------------------|
| Navegación completa por teclado | Todos los controles alcanzables con Tab; orden de foco lógico; no hay trampas de foco. |
| Orden de foco lógico y visible | El foco tiene un anillo visible (`--color-borde-enfoque` + outline) en todos los elementos interactivos. |
| Etiquetas asociadas a campos | Cada `<input>`/`<select>`/`<textarea>` tiene su `<label for="id">` o `aria-label`. |
| Texto alternativo para imágenes informativas | `alt` descriptivo en imágenes; imágenes decorativas con `alt=""`. |
| Contraste suficiente | Texto normal mínimo 4.5:1; texto grande 3:1; elementos gráficos y bordes de componentes 3:1, usando los tokens definidos. |
| Encabezados y estructura semántica | `<h1>` único por página, jerarquía `<h2>`...`<h6>`, `<main>`, `<nav>`, `<section>`, `<form>`, `<button>`, `<a>`. |
| Identificación accesible de errores | Mensajes de error asociados al campo mediante `aria-describedby` y `aria-invalid="true"`; anuncio para lectores de pantalla. |
| Mensajes de estado para tecnologías de asistencia | Alertas dinámicas con `role="status"` o `role="alert"` según urgencia. |
| No depender exclusivamente del color | Cada estado incluye texto, icono o borde distintivo además del color. |
| Preferencia de movimiento reducido | Respetar `prefers-reduced-motion: reduce` (desactivar animaciones/transiciones). |
| Zoom hasta 200% | La interfaz sigue usable sin pérdida de contenido ni funcionalidad. |

---

## 13. Formularios dinámicos y estados de interfaz

### Formularios dinámicos
- Los campos se muestran u ocultan según el contexto sin recargar la página (ej. al seleccionar una mesa, se muestran sus horarios disponibles; al elegir productos en un pedido, se agrega una línea de detalle).
- El estado de visibilidad se gestiona con JavaScript vanilla y `aria-expanded` / `aria-hidden` para accesibilidad.
- Validación inmediata (on blur / on input) cuando aporte valor (ej. email con formato inválido, cantidad de producto menor a 1).
- Validación definitiva siempre en el servidor (PHP).

### Estados de interfaz definidos
Cada pantalla o bloque puede estar en uno de estos estados:
1. **Carga:** spinner + mensaje "Cargando..." (usado cuando una indicación simple es suficiente; no usar skeleton si un indicador de carga es más apropiado).
2. **Vacío:** mensaje "No hay reservas" o "No hay mesas disponibles" con acción sugerida.
3. **Error:** mensaje concreto y orientado a resolver el problema (ej. "La mesa ya está reservada en ese horario. Elija otro horario.").
4. **Éxito:** mensaje breve (ej. "Reserva creada con éxito.") con opción de ir al historial.
5. **Falta de conexión:** aviso claro cuando no se puede contactar al servidor.

### Conservación de datos y prevención de envíos duplicados
- Al producirse un error recuperable (validación de servidor), se conservan los datos ingresados en el formulario (no se borra lo escrito).
- Se previenen envíos duplicados deshabilitando el botón durante el envío y usando un token de solicitud (CSRF) cuando corresponda.
- Validación de reglas de negocio en servidor independientemente de atributos HTML ni JavaScript del cliente.
