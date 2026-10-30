# Diagrama de Gantt — Cronograma Proyecto Integrador Cuatrimestral 2026

*Generado con Mermaid (sintaxis .md). Cronograma estimado del desarrollo del Sistema de Gestión de Restaurante "Green en la Casa Blanca".*

```mermaid
gantt
    title Proyecto Integrador Cuatrimestral 2026 — Sistema de Gestión de Restaurante
    dateFormat  YYYY-MM-DD
    axisFormat %d/%m
    todayMarker off

    section Etapa 1: Análisis y Requerimientos
    Análisis de problemática        :2026-08-01, 15d
    Requerimientos funcionales      :2026-08-16, 10d
    Requerimientos no funcionales   :2026-08-26, 7d
    FODA y APA 7                    :2026-09-02, 8d

    section Etapa 2: Diseño del Sistema
    Modelo de datos y DER           :2026-09-10, 12d
    Casos de uso                    :2026-09-22, 8d
    Arquitectura y API REST         :2026-09-30, 10d
    Reglas de negocio               :2026-10-10, 6d

    section Etapa 3: Desarrollo (Core)
    Backend PHP (controladores)     :2026-10-16, 20d
    Frontend HTML/JS vanilla        :2026-11-05, 18d
    Autenticación y middlewares     :2026-10-16, 12d
    Integración API-Frontend        :2026-11-23, 10d

    section Etapa 4: Base de Datos (Core)
    Migraciones 001-003 (esquema)   :2026-10-16, 8d
    Migraciones 004-006 (stock, normalización) :2026-11-01, 12d
    Datos de prueba (migración 007) :2026-11-13, 4d

    section Etapa 5: Pruebas y Documentación (Core)
    Pruebas funcionales y de errores:2026-12-01, 15d
    Correcciones                    :2026-12-16, 8d
    Documentación integrador        :2026-10-01, 30d
    Entregables (diagramas, APA7)   :2026-12-24, 7d

    section Etapa 6: Funcionalidades Extendidas (RF-68 a RF-105)
    Migraciones 008-009 (config, límites, recovery) :2027-01-02, 10d
    Migraciones 010-011 (cupones, calificaciones, fidelidad) :2027-01-12, 12d
    Migración 012 (delivery, repartidores) :2027-01-24, 8d
    Migración 013 (RF pendientes: caja, notifs, sucursales) :2027-02-01, 8d
    Frontend: paneles extendidos (encargado + cliente) :2027-01-02, 20d
    Integración y pruebas extendidas   :2027-02-09, 10d
```

## Hitos del cronograma (plan original)

| Fecha estimada | Hito                                                 | Entregable asociado                 |
| -------------- | ---------------------------------------------------- | ----------------------------------- |
| 2026-08-15     | Análisis de problemática concluido                   | 01_analisis_problematica.md         |
| 2026-09-02     | Requerimientos y FODA                                | 02_etapa1_requerimientos.md         |
| 2026-10-10     | Diseño completo (modelo, casos de uso, arquitectura) | 03_diseno_sistema.md                |
| 2026-11-13     | Base de datos operativa con datos de prueba          | migraciones 001→007                 |
| 2026-12-16     | Sistema funcional y probado (core)                   | 05_sistema_funcional_pruebas.md     |
| 2026-12-31     | Entrega final del proyecto integrador                | Documento APA7 + diagramas + código |

## Hitos adicionales (implementación extendida RF-68 a RF-105)

| Fecha estimada | Hito                                                 | Entregable / Migraciones            |
| -------------- | ---------------------------------------------------- | ----------------------------------- |
| 2027-01-12     | Configuración, límites, recuperación contraseña      | Migraciones 008, 009                |
| 2027-01-24     | Cupones, calificaciones, preferencias, fidelidad     | Migraciones 010, 011                |
| 2027-02-01     | Delivery, zonas, repartidores                        | Migración 012                       |
| 2027-02-09     | RF pendientes: cierre caja, notificaciones, sucursales, permisos | Migración 013              |
| 2027-02-19     | Paneles extendidos + integración completa            | panel-encargado.html, panel-cliente.html |
| 2027-02-28     | Documentación actualizada y entrega final extendida  | README.md, documentacion/, entregables/ |
