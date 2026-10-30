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

    section Etapa 3: Desarrollo
    Backend PHP (controladores)     :2026-10-16, 20d
    Frontend HTML/JS vanilla        :2026-11-05, 18d
    Autenticación y middlewares     :2026-10-16, 12d
    Integración API-Frontend        :2026-11-23, 10d

    section Etapa 4: Base de Datos
    Migraciones 001-003 (esquema)   :2026-10-16, 8d
    Migraciones 004-006 (stock, normalización) :2026-11-01, 12d
    Datos de prueba (migración 007) :2026-11-13, 4d

    section Etapa 5: Pruebas y Documentación
    Pruebas funcionales y de errores:2026-12-01, 15d
    Correcciones                    :2026-12-16, 8d
    Documentación integrador        :2026-10-01, 30d
    Entregables (diagramas, APA7)   :2026-12-24, 7d
```

## Hitos del cronograma

| Fecha estimada | Hito                                                 | Entregable asociado                 |
| -------------- | ---------------------------------------------------- | ----------------------------------- |
| 2026-08-15     | Análisis de problemática concluido                   | 01_analisis_problematica.md         |
| 2026-09-02     | Requerimientos y FODA                                | 02_etapa1_requerimientos.md         |
| 2026-10-10     | Diseño completo (modelo, casos de uso, arquitectura) | 03_diseno_sistema.md                |
| 2026-11-13     | Base de datos operativa con datos de prueba          | migraciones 001→007                 |
| 2026-12-16     | Sistema funcional y probado                          | 05_sistema_funcional_pruebas.md     |
| 2026-12-31     | Entrega final del proyecto integrador                | Documento APA7 + diagramas + código |
