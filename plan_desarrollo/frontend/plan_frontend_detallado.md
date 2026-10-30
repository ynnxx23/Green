# Plan de desarrollo detallado — Frontend

Sistema de gestión de un restaurante — "Green en la Casa Blanca"

---

## 1. Identidad visual y concepto de diseño

### Concepto general
Restaurante elegante, moderno con estilo neoclásico. Primera presencia web. Diseño refinado, suave y sofisticado.

### Paleta de colores de marca

| Rol | Color | Uso |
|-----|-------|-----|
| Primario | `#1B4332` Verde bosque | Encabezados, fondos oscuros, botones principales |
| Secundario | `#2D6A4F` Verde salvia | Elementos secundarios, bordes sutiles |
| Acento neoclásico | `#B8860B` Dorado apagado | Líneas divisorias, divisiones doradas, detalles |
| Fondo suave | `#F5F5DC` Crema cálido | Fondo principal, secciones claras |
| Superficie | `#FFFFFF` Blanco | Tarjetas, secciones internas |
| Texto principal | `#2C2C2C` Carbón | Cuerpo de texto |
| Texto secundario | `#5A6B5A` Verde grisáceo | Texto de apoyo, descripciones |

### Tipografía

| Uso | Fuente | Estilo | Característica |
|-----|--------|--------|----------------|
| Encabezados (h1-h3, logo) | **Cinzel** (serif) | 600, espaciado de letras amplio | Neoclásico, refinado, elegante |
| Cuerpo de texto | **Lato** (sans-serif) | 400-500 | Legible, moderno, suave |

### Requisitos de diseño (combinación Opción 2 + Opción 3)

El usuario especificó los siguientes requisitos que deben reflejarse en todo el frontend:

| Elemento | De la Opción 2 | De la Opción 3 | Decisión final |
|----------|---------------|----------------|----------------|
| **Fondo del inicio (Hero)** | Imagen de comida de fondo, suave | Color plano | **Imagen de comida de fondo** con opacidad baja, aspecto suave |
| **Bordes y esquinas** | Redondeados, suaves | Rectos, definidos | **Redondeados suaves** (border-radius 6-12px) |
| **Logo** | Sin círculo, se ve entero | En círculo | **Sin círculo**, imagen PNG completa |
| **Tipografía** | Lato + Cinzel | Cinzel con espaciado amplio | **Cinzel** para encabezados con `letter-spacing` amplio |
| **Divisiones doradas** | No | Sí, división dorada debajo de títulos | **División dorada** (`#B8860B`) bajo cada encabezado de sección |
| **Delimitación de carta** | Tarjetas blancas simples | Líneas doradas claritas delimitando secciones | **Líneas doradas claritas** delimitando la sección de carta |
| **Sección de reservas** | Fondo blanco, formulario simple | Fondo verde bosque, formulario centrado | **Fondo verde bosque (`#1B4332`)**, formulario centrado, refinado |
| **Aspecto general** | Suave, refinado | Muy definido y recto | **Refinado, suave, con detalles neoclásicos** |

### Elementos visuales recurrentes

- **División dorada**: Línea horizontal de 2-3px en `#B8860B` debajo de cada `h2` de sección.
- **Marco ornamental opcional**: Esquinas doradas en el contenedor principal (opcional, versión desktop).
- **Imágenes de comida**: Fondo del hero con fotografía de alta calidad de platos (opacidad 10-15%).
- **Tarjetas**: Fondo blanco, borde sutil, sombra suave, bordes redondeados.
- **Botones**: Verde bosque para primario, transparente con borde verde para secundario.

---

## 2. Stack tecnológico (frontend)

| Componente | Tecnología | Versión | Observación |
|------------|-----------|---------|-------------|
| Lenguaje de marcado | HTML5 | — | Semántico, accesible |
| Estilos | CSS3 | — | Variables CSS, sin preprocesadores (para simplicidad) |
| Lógica del cliente | JavaScript vanilla | ES6+ | Sin frameworks (React, Vue, Angular) — requerido por instrucción |
| Peticiones al backend | `fetch()` API | — | Consume endpoints JSON de `public/index.php` |
| Iconos | SVG sprite + PNG directo | — | `sprite.svg` para iconos lineales; `logo.png` y `plato.png` como imagen |
| Fuentes | Google Fonts | Cinzel, Lato | Carga vía `<link>` o `@import` |
| Diseño | Responsive | Mobile-first | Móvil, tablet, escritorio |

---

## 3. Estructura de carpetas (frontend)

```
public/
├── index.html                  # Página principal (inicio)
├── nosotros.html               # Nosotros / historia
├── carta.html                  # Carta / menú completo
├── reservas.html               # Página de reservas
├── contacto.html               # Contacto
├── login.html                  # Login (si se requiere auth visual)
├── registro.html               # Registro de usuario
├── mis-reservas.html           # Historial de reservas propias
├── assets/
│   ├── css/
│   │   ├── main.css            # Estilos principales (variables, base, componentes)
│   │   ├── hero.css            # Estilos específicos del hero
│   │   ├── menu.css            # Estilos de la carta
│   │   ├── reservas.css        # Estilos de formulario de reservas
│   │   └── responsive.css      # Media queries
│   ├── js/
│   │   ├── main.js             # Lógica general (nav, menú móvil)
│   │   ├── api.js              # Cliente fetch centralizado
│   │   ├── reservas.js         # Lógica de formulario de reservas
│   │   └── auth.js             # Login, registro, sesión
│   ├── icons/
│   │   ├── logo.png            # Logo del restaurante (PNG directo)
│   │   ├── plato.png           # Imagen de plato (PNG directo)
│   │   ├── sprite.svg          # Iconos lineales (teléfono, whatsapp, etc.)
│   │   └── icons.css           # Estilos de iconos
│   ├── img/                    # Imágenes de comida, interior, galería
│   └── fonts/                  # Fuentes si se alojan localmente (opcional)
```

---

## 4. Páginas y componentes

### 4.1 Inicio (`index.html`)
**Hero section:**
- Fondo con imagen de comida de alta calidad (opacidad 10-15% sobre fondo crema)
- Título "Green en la Casa Blanca" con tipografía Cinzel, letra espaciada
- Subtítulo en Lato, estilo itálico, color verde grisáceo
- Dos botones: "Reservar mesa" (primario, verde bosque) y "Ver carta" (secundario, borde verde)
- Logo sin círculo, imagen completa en esquina superior izquierda

**Secciones de inicio:**
- Nosotros (resumen breve)
- Carta destacada (3-4 platos estrella con imagen y precio)
- Reservas rápidas (formulario mínimo)

### 4.2 Nosotros (`nosotros.html`)
- Historia del restaurante
- Filosofía (producto local, green, neoclásico)
- Equipo (chef, sala)
- Espacios (comedor, barra, salón privado)
- Imágenes del interior

### 4.3 Carta / Menú (`carta.html`)
**Diseño (requisito del usuario):**
- Título "NUESTRA CARTA" con tipografía Cinzel, mayúsculas, espaciado amplio
- **División dorada** (`#B8860B`, 2-3px) debajo del título
- Sección delimitada por **líneas doradas claritas** que enmarcan el contenido
- Filtros por categoría (píldoras: Entradas, Principales, Postres, Bebidas)
- Vista en tarjetas (desktop: 3 columnas; móvil: 1 columna)
- Cada plato: foto, nombre, descripción, precio, badges dietéticos
- Diseño aireado, espacio entre tarjetas, bordes redondeados suaves

### 4.4 Reservas (`reservas.html`)
**Diseño (requisito del usuario):**
- Fondo verde bosque (`#1B4332`)
- Formulario centrado, tarjeta blanca sobre fondo verde
- Campos: nombre, email, teléfono, fecha, hora, comensales, ocasión, comentarios
- Botón "Enviar reserva" (verde bosque o dorado)
- Teléfono y WhatsApp visibles como alternativa directa
- Mensaje de éxito: "Reserva enviada. Te confirmaremos por WhatsApp."

### 4.5 Contacto (`contacto.html`)
- Dirección con mapa (placeholder o iframe)
- Horarios (cocina y atención)
- Teléfono, WhatsApp, email
- Formulario de contacto

### 4.6 Login / Registro (opcional, fase posterior)
- Páginas de autenticación visual
- Conexión a `POST /api/auth/login` y `POST /api/auth/registro`

---

## 5. Requisitos funcionales (frontend)

| ID | Nombre | Descripción |
|----|--------|-------------|
| FF-01 | Navegación principal | Menú fijo con logo y enlaces a Nosotros, Carta, Reservas, Contacto |
| FF-02 | Hero con imagen de fondo | Imagen de comida en el inicio con texto superpuesto |
| FF-03 | Carta con filtros | Filtrar platos por categoría sin recargar página |
| FF-04 | Formulario de reservas | Enviar datos de reserva al backend (`POST /api/reservas`) |
| FF-05 | Diseño responsive | Funcionar en móvil, tablet y escritorio |
| FF-06 | Accesibilidad | Teclado, foco visible, etiquetas, contraste WCAG 2.2 AA |
| FF-07 | Logo sin círculo | Imagen PNG completa, sin recorte circular |
| FF-08 | Divisiones doradas | Línea `#B8860B` bajo cada encabezado de sección |
| FF-09 | Carta delimitada | Líneas doradas claritas enmarcando la sección de carta |

---

## 6. Requisitos no funcionales (frontend)

| ID | Nombre | Descripción |
|----|--------|-------------|
| FNF-01 | Estilo visual | Elegante, moderno, neoclásico, refinado, suave |
| FNF-02 | Colores de marca | Usar exclusivamente la paleta definida (verde bosque, salvia, dorado, crema) |
| FNF-03 | Tipografía | Cinzel para encabezados, Lato para cuerpo |
| FNF-04 | Responsive | Mobile-first, breakpoint en 768px |
| FNF-05 | Accesibilidad | WCAG 2.2 nivel AA (contraste, teclado, foco, etiquetas, ARIA) |
| FNF-06 | Rendimiento | Carga rápida, imágenes optimizadas, CSS y JS minificados en producción |
| FNF-07 | Sin frameworks | JavaScript vanilla, CSS puro |
| FNF-08 | Integración backend | Consumir API REST en `http://localhost/Yensel/Morday/public/` |

---

## 7. Integración con backend

El frontend consume la API REST ya desarrollada en `public/index.php`:

| Frontend necesita | Endpoint backend | Estado | Notas |
|-------------------|------------------|--------|-------|
| Ver disponibilidad de mesas | `GET /api/mesas` | ✅ Listo | Para formulario de reservas |
| Crear reserva | `POST /api/reservas` | ✅ Listo | Requiere auth de cliente |
| Ver carta de productos | `GET /api/productos` | ✅ Listo | Para llenar la carta dinámicamente |
| Login de usuario | `POST /api/auth/login` | ✅ Listo | Para área de cliente |
| Ver reservas propias | `GET /api/reservas` | ✅ Listo | Requiere auth |
| Registro de usuario | `POST /api/auth/registro` | ✅ Listo | Para nuevo cliente |

**Flujo de reservas:**
1. Usuario abre `reservas.html`
2. Llena formulario (fecha, hora, comensales)
3. `fetch()` envía `POST` a `/api/reservas`
4. Backend valida disponibilidad y crea reserva
5. Frontend muestra mensaje de éxito o error

---

## 8. Plan de fases (frontend)

### Fase F8a — Estructura base y estilos globales
- Crear carpeta `public/assets/css/` y `public/assets/js/`
- Crear `main.css` con variables de marca, reset, tipografía, utilidades
- Crear `api.js` con cliente fetch centralizado
- Crear `main.js` con lógica de navegación y menú móvil
- Configurar Google Fonts (Cinzel, Lato)

**Criterio de completado:** `index.html` carga sin errores, variables CSS funcionan, menú móvil se abre/cierra.

### Fase F8b — Página de inicio (Hero + secciones)
- Crear `index.html` con hero (imagen de fondo de comida)
- Implementar división dorada bajo títulos
- Implementar logo sin círculo
- Sección Nosotros resumida y Carta destacada

**Criterio de completado:** Hero se ve refinado, imagen de fondo se ve suave, botones funcionan, responsive en móvil.

### Fase F8c — Página de Carta / Menú
- Crear `carta.html`
- Implementar título "NUESTRA CARTA" con división dorada
- Delimitar sección con líneas doradas claritas
- Implementar filtros por categoría
- Conectar a `GET /api/productos` para llenar dinámicamente

**Criterio de completado:** Carta se ve delimitada por líneas doradas, platos se cargan desde la API, filtros funcionan.

### Fase F8d — Página de Reservas
- Crear `reservas.html`
- Fondo verde bosque, formulario centrado en tarjeta blanca
- Conectar a `POST /api/reservas`
- Validación en cliente + definitiva en servidor

**Criterio de completado:** Reserva se envía exitosamente, mensaje de confirmación se muestra, errores se manejan.

### Fase F8e — Páginas adicionales (Nosotros, Contacto)
- Crear `nosotros.html`, `contacto.html`
- Imágenes del interior, mapa, horarios
- Formulario de contacto

**Criterio de completado:** Todas las páginas navegan correctamente entre sí.

### Fase F8f — Pruebas, ajuste fino y entrega
- Pruebas en móvil, tablet y escritorio
- Verificar accesibilidad (teclado, foco, contraste)
- Verificar que no haya secretos hardcodeados
- Ajustes finales de diseño

**Criterio de completado:** Todas las páginas pasan pruebas críticas, frontend listo para producción.

---

## 9. Archivos de referencia

- `documentacion_desarrollo/04_diseno_y_accesibilidad.md` — Design tokens y accesibilidad
- `documentacion_desarrollo/02_stack_y_arquitectura.md` — Contratos frontend ↔ backend
- `documentacion_desarrollo/08_hoja_de_ruta.md` — Fase F8 y criterios de completado
- `documentacion/variables_configuracion.md` — Variables de entorno
- `plan_desarrollo/backend/plan_backend.md` — Plan del backend (ya implementado)

---

## 10. Próximo paso inmediato

El backend está 100% operativo en `http://localhost/Yensel/Morday/public/`. El frontend queda por implementar siguiendo este plan. La primera tarea es crear `public/assets/css/main.css` con las variables de marca y la estructura base de `index.html`.

---

*Plan generado el 26/10/2026. Frontend pendiente de implementación. Diseño: combinación de Opción 2 (suave, imagen de fondo, logo sin círculo) y Opción 3 (tipografía Cinzel, división dorada, sección de reservas en verde bosque).*
