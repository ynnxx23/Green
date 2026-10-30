# Plan de desarrollo — Frontend

Sistema de gestión de un restaurante (frontend dinámico)

---

## 1. Estado actual

El frontend **no está implementado**. El proyecto actualmente solo expone una API REST en JSON desde `public/index.php`. No hay pantallas visuales ni assets (CSS/JS) para usar el sistema desde el navegador.

Según la hoja de ruta (`documentacion_desarrollo/08_hoja_de_ruta.md`), esto corresponde a la **Fase F8 — Interfaz y frontend dinámico**, que depende de F3, F5, F6 y F7 (ya completadas en el backend).

---

## 2. Stack tecnológico (definido en `02_stack_y_arquitectura.md`)

| Componente | Tecnología | Decisión |
|------------|-----------|----------|
| Lógica del cliente | JavaScript vanilla | Sin frameworks (React, Vue, Angular) |
| Estilos | CSS | Design tokens, componentes, accesibilidad WCAG 2.2 AA |
| Presentación | HTML semántico | Vistas HTML, responsive (móvil, tablet, escritorio) |
| Peticiones al backend | `fetch()` | Consume endpoints JSON de `public/index.php` |

---

## 3. Estructura propuesta (pendiente de crear)

```
public/
├── index.html               # Página de entrada (login)
├── assets/
│   ├── css/
│   │   ├── design-tokens.css    # paleta, tipografía, espaciado
│   │   ├── components.css       # botones, inputs, alertas, tarjetas
│   │   └── layouts.css          # layouts responsive
│   ├── js/
│   │   ├── api.js           # cliente fetch centralizado
│   │   ├── auth.js          # login, registro, sesión
│   │   ├── mesas.js         # consulta de áreas/mesas/disponibilidad
│   │   ├── reservas.js      # crear, historial, cancelar reservas
│   │   ├── pedidos.js       # registro y gestión de pedidos
│   │   └── admin.js         # panel de administración
│   └── img/
└── views/                   # vistas HTML parciales (opcional)
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

- `documentacion_desarrollo/04_diseno_y_accesibilidad.md` — Design tokens y accesibilidad.
- `documentacion_desarrollo/08_hoja_de_ruta.md` — Fase F8 y criterios de completado.
- `documentacion_desarrollo/02_stack_y_arquitectura.md` — Contratos frontend ↔ backend.

---

*Plan generado el 26/10/2026. Frontend pendiente de implementación.*
