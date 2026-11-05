# Plan de desarrollo — Frontend

Sistema de gestión de un restaurante (frontend dinámico)

---

## 1. Estado actual

El frontend **está implementado** en `public/` con HTML5, CSS3 y JavaScript vanilla.

Páginas públicas: `index.html`, `carta.html`, `reservas.html`, `nosotros.html`, `contacto.html`, `login.html`, `registro.html`.

Paneles autenticados: `panel.html`, `panel-cliente.html`, `panel-encargado.html`, `panel-admin.html`, `panel-reportes.html`, `gestion-productos.html`, `subir_imagenes.html`.

Assets: `assets/css/` (design-tokens, componentes, main), `assets/js/` (api, auth, reservas, ui, app, vistas), `assets/icons/` (logo, plato, sprite.svg), `assets/img/platos/`.

Diseño: neoclásico, colores verde bosque/salvia/dorado, tipografía Cinzel + Lato, responsive, accesible.

---

## 2. Stack tecnológico (definido en `02_stack_y_arquitectura.md`)

| Componente | Tecnología | Decisión |
|------------|-----------|----------|
| Lógica del cliente | JavaScript vanilla | Sin frameworks (React, Vue, Angular) |
| Estilos | CSS | Design tokens, componentes, accesibilidad WCAG 2.2 AA |
| Presentación | HTML semántico | Vistas HTML, responsive (móvil, tablet, escritorio) |
| Peticiones al backend | `fetch()` | Consume endpoints JSON de `public/index.php` |

---

## 3. Estructura actual (implementada)

```
public/
├── index.html               # Inicio público
├── nosotros.html            # Nosotros
├── carta.html               # Carta completa
├── reservas.html            # Reservas públicas
├── contacto.html            # Contacto
├── login.html               # Login
├── registro.html            # Registro
├── panel.html               # Despachador por rol
├── panel-cliente.html       # Panel cliente
├── panel-encargado.html     # Panel encargado
├── panel-admin.html         # Panel administrador
├── panel-reportes.html      # Reportes admin
├── gestion-productos.html   # Gestión productos
├── subir_imagenes.html      # Subir imágenes
├── assets/
│   ├── css/
│   │   ├── design-tokens.css
│   │   ├── componentes.css
│   │   ├── components.css
│   │   └── main.css
│   ├── js/
│   │   ├── api.js
│   │   ├── auth.js
│   │   ├── reservas.js
│   │   ├── ui.js
│   │   ├── app.js
│   │   └── vistas.js
│   ├── icons/
│   │   ├── logo.png
│   │   ├── plato.png
│   │   ├── sprite.svg
│   │   └── icons.css
│   └── img/platos/          # imágenes de platos
└── .htaccess                # mod_rewrite para front controller
```

---

## 4. Pantallas planificadas (Fase F8)

| Pantalla | Descripción | Actor |
|----------|-------------|-------|
| Login / Registro | Autenticación de usuarios | Público / Usuario |
| Consulta de áreas y mesas | Listar áreas, ver mesas y disponibilidad | Cualquier usuario |
| Crear reserva | Seleccionar fecha, horario, mesa | Usuario autenticado |
| Mis reservas | Historial y cancelación propias | Usuario autenticado |
| Registrar pedido | Asociar mesa, mesero, productos | Mesero |
| Ver pedidos | Listar pedidos (propios o todos según rol) | Mesero / Cocinero / Admin |
| Panel admin | Gestión de usuarios, áreas, mesas, productos | Administrador |

---

## 5. Requisitos no funcionales del frontend

- Accesibilidad WCAG 2.2 nivel AA (teclado, foco visible, etiquetas, contraste, semántica, ARIA).
- Diseño adaptable/responsive (móvil, tablet, escritorio).
- Validación inmediata en cliente + definitiva en servidor.
- Conservación de datos ante error recuperable.
- Prevención de envíos duplicados.
- Identificadores y mensajes en español.

---

## 6. Dependencias

- **Backend:** Debe estar operativo (fases F1-F7 ya completadas).
- **Endpoints:** Los endpoints de la API deben estar probados antes de conectar el frontend.
- **Autenticación:** El frontend usa sesión de cookie PHP nativa (no JWT).

---

## 7. Archivos de referencia

- `documentacion/desarrollo/04_diseno_y_accesibilidad.md` — Design tokens y accesibilidad.
- `documentacion/desarrollo/08_hoja_de_ruta.md` — Fase F8 y criterios de completado.
- `documentacion/desarrollo/02_stack_y_arquitectura.md` — Contratos frontend ↔ backend.

---

*Plan generado el 26/10/2026. Frontend implementado.*
