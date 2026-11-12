# Índice de Entregables — Sistema de Gestión de Restaurante "Green en la Casa Blanca"

Este directorio contiene todos los entregables del Proyecto Integrador Cuatrimestral 2026, ordenados lógicamente por fases del proyecto.

| N.º | Archivo | Contenido |
| --- | --- | --- |
| 01 | `01_documento_apa7.md` | Documento final del proyecto en formato APA 7.ª edición (estructura igual a la referencia: portada, resumen, contenido, situación problemática, diagrama de Gantt, entrevistas abiertas/cerradas por rol —administrador, encargado, cliente—, cuadro del mundo del problema, matriz FODA, objetivos generales y específicos, requerimientos funcionales con una tabla cada uno, requerimientos no funcionales en lista, alcance, despliegue, limitaciones, especificaciones y entregables). 100 % adaptado al proyecto actual del restaurante "Green en la Casa Blanca". |
| 02 | `02_analisis_problematica.md` | Análisis de la problemática: descripción de la situación, identificación del problema, usuarios involucrados, objetivo general y específicos, alcance y limitaciones. |
| 03 | `03_etapa1_requerimientos.md` | Primera etapa del proyecto: entrevista, mundo del problema, matriz FODA, objetivos, especificación de requerimientos (funcionales y no funcionales), alcance, limitaciones, entregables y formato APA 7. |
| 04 | `04_diseno_sistema.md` | Análisis y diseño del sistema: actores y roles, casos de uso, modelo de datos, reglas de negocio, arquitectura técnica y endpoints API. |
| 05 | `05_desarrollo.md` | Desarrollo: diseño de interfaz, base de datos (migraciones 001–024), programación (controladores, servicios, middleware, módulos JS) e integración app ↔ base de datos. |
| 06 | `06_sistema_funcional_pruebas.md` | Sistema funcional: pruebas realizadas, errores corregidos, historial de movimientos, reportes y cómo ejecutar el sistema. |
| 07 | `07_plan_mantenimiento.md` | Plan de mantenimiento: tipos de mantenimiento (correctivo, adaptativo, preventivo, evolutivo), tareas periódicas, roles responsables y procedimiento de incidencias. |
| 08 | `08_plan_capacitacion.md` | Plan de capacitación: público objetivo, contenidos por rol, modalidad, duración, materiales, cronograma y evaluación. |
| 09 | `09_diagrama_caso_uso.md` | **Diagrama de casos de uso.** 27 casos de uso (CU1–CU30) con el actor Cliente, Encargado y Administrador, relaciones `<<include>>` / `<<extend>>` y reglas de negocio. Todos los casos que implican operación **exigen iniciar sesión** como requisito previo. Incluye el archivo editable `Diagrama_Caso_Uso_Green.drawio`. |
| 10 | `10_diagrama_der.md` | **Diagrama Entidad-Relación (DER).** 23 entidades con atributos, claves PK/FK/UK y relaciones en notación Barker. Incluye el archivo editable `Diagrama_DER_Green.drawio`. |
| 11 | `11_diagrama_gantt.md` | Diagrama de Gantt (Mermaid): cronograma estimado del proyecto por etapas. |
| 12 | `12_diagrama_clases.md` | **Diagrama de clases.** 23 clases de entidad y 8 servicios (31 clases en total) con atributos, métodos y relaciones UML. Incluye el archivo editable `Diagrama_Clases_Green.drawio`. |
| 13 | `13_diccionario_datos.md` | **Diccionario de datos.** Descripción campo por campo de las 23 tablas del modelo físico: nombre, tamaño, tipo de dato, clave, nulidad, valor por defecto y descripción funcional. |

## Archivos de diagrama editables (draw.io)

| Archivo | Contenido | Formato |
|---|---|---|
| `Diagrama_Caso_Uso_Green.drawio` | Diagrama de casos de uso UML | draw.io / diagrams.net |
| `Diagrama_DER_Green.drawio` | Diagrama entidad-relación (notación Barker) | draw.io / diagrams.net |
| `Diagrama_Clases_Green.drawio` | Diagrama de clases UML | draw.io / diagrams.net |

> Para editar: abrir [app.diagrams.net](https://app.diagrams.net) → *Abrir desde dispositivo* → seleccionar el `.drawio`.

Los tres `.drawio` replican **exactamente** la estructura, el estilo y los elementos de los
archivos de referencia de `entregables/referencia/` (`CASOSDEUSOPAÑOLL.drawio`,
`DER_Panol_Barker.drawio` y `Diagrama_Clases_Panol.drawio`), con el contenido del proyecto
"Green en la Casa Blanca" y sin colores ni elementos ajenos a la referencia.

## Formato

Los entregables 09, 10, 11 y 12 se entregan en dos formatos equivalentes:

1. **`.md` con sintaxis Mermaid** — texto legible y versionable, con las tablas de atributos y relaciones en Markdown.
2. **`.drawio`** — archivo editable en draw.io / diagrams.net con los símbolos UML aplicados.

El entregable 13 (diccionario de datos) se entrega en `.md`, con la misma estructura de tablas que el `.docx` de referencia.

**Trazabilidad:** Los requerimientos funcionales (RF01–RF105) del documento APA7 están trazados a los diagramas de casos de uso y al modelo de datos del DER. Las 23 entidades del DER corresponden una a una con las tablas documentadas en el diccionario de datos.

**Simplificación del modelo (migración 025):** la base de datos pasó de 36 a **23 tablas**. Se eliminaron las funcionalidades sin interfaz ni uso real (`recuperar_contrasena`, `cierre_caja`, `respaldos_bd`, `notificaciones`, `calificaciones_platos`, `preferencias_cliente`, `platos_favoritos`, `puntos_fidelidad`, `reservas_recurrentes`, `sucursales`, `permisos`/`roles_permisos`, `cupones_descuento`/`uso_cupon_cliente`), se normalizó `productos.categoria` en la nueva tabla `categorias` y se eliminaron las columnas de sucursal y las columnas muertas. El detalle está en `migrations/025_simplificacion_base_datos.sql`.
