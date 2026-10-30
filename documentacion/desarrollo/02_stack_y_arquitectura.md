# 02. Stack y arquitectura — Sistema de gestion de un restaurante

> **Estado:** borrador de planificacion (etapa 1 - sin programar).  
> **Idioma:** espanol.  
> **Decisiones firmes vs pendientes:** se marca cada una.

---

## 1. Tecnologías seleccionadas y justificacion

| Tecnologia | Funcion | Motivo de eleccion | Integracion con las demas | Alternativas evaluadas | Decision |
|------------|---------|--------------------|---------------------------|------------------------|----------|
| **PHP** | Backend, API, logica de negocio, acceso a datos | Requerido por la instruccion. Amplio soporte en hosting compartido, XAMPP y Docker. Maduro para aplicaciones web servidor-renderizadas. | Se conecta a MariaDB mediante PDO. Expone endpoints consumidos por JS. Sirve las vistas HTML. | Node.js, Python, Java, Ruby | **Firme:** PHP (requerido). |
| **JavaScript (vanilla)** | Logica del cliente, interactividad, peticiones AJAX/fetch al backend | Requerido por la instruccion. Navegador nativo, sin dependencias de build ni frameworks. | Consume endpoints PHP (JSON). Manipula DOM y validaciones inmediatas. | React, Vue, Angular, jQuery | **Firme:** JS vanilla (sin framework). |
| **MySQL / MariaDB** | Sistema de gestion de bases de datos | Requerido por la instruccion. MariaDB es compatible con MySQL y disponible en XAMPP, Docker y la mayoria de hostings. | PHP se conecta via PDO. Migraciones versionadas. | PostgreSQL, SQLite | **Firme:** MariaDB/MySQL (versión de referencia MariaDB 10.11 / MySQL 8.0). |
| **XAMPP** | Entorno local de desarrollo (Apache + PHP + MariaDB) | Permite desarrollar sin Docker. Requerido como una de las tres modalidades. | Apache ejecuta PHP; PHP usa MariaDB local. | WAMP, LAMP, PHP built-in server | **Firme:** XAMPP como opcion local (no obligatoria). |
| **Docker + Docker Compose** | Definicion y reproduccion de entornos de ejecucion | Requerido por la instruccion. Garantiza igualdad entre desarrollo, pruebas y producción. | Contenedor PHP+Apache, contenedor MariaDB, red interna, volumenes persistentes. | Maquinas virtuales, instalacion nativa | **Firme:** Docker Compose como opcion (no obligatoria). |
| **POO (PHP)** | Organizar logica en clases, entidades, servicios, controladores | Requerido por la instruccion. Mantenibilidad, separation de responsabilidades. | Aplicado en capas: Modelo, Servicio, Controlador, Acceso a datos. | Programacion estructurada pura | **Firme:** POO. |
| **bcrypt (password_hash/password_verify)** | Almacenamiento seguro de contrasenas | Requerido por la instruccion. Estandar para hash de contrasenas. | Usado en servicio de autenticacion al registrar y al iniciar sesion. | Argon2, scrypt, SHA-256 | **Firme:** bcrypt. |
| **SHA-256** | Solo si hay una necesidad tecnica concreta y justificada | Requerido por la instrucion. NO para contrasenas, NO como firma de JWT. | No usado por defecto. | — | **Firme:** no se usa SHA-256 por defecto. |
| **Git y GitHub** | Control de versiones, ramas, integracion continua | Requerido por la instruccion. | Ramas main/develop, PRs, GitHub Actions. | GitLab, Bitbucket, SVN | **Firme:** GitHub. |
| **JWT** | Autenticacion token-based (solo si realmente se requiere) | Evaluar. No se agrega automaticamente con sesion de cookie. | Si se usa: almacenamiento seguro, renovacion, revocacion. | Sesion de cookie nativa de PHP | **Pendiente (A-04):** decision temporal = sesion de cookie nativa, sin JWT inicialmente. |
| **Access / Refresh / ID tokens** | Mecanismos de autenticacion | Evaluar cada uno. No implementar los tres automaticamente. | — | — | **Pendiente:** no se implementan por defecto (sesion de cookie). ID token solo con proveedor de identidad. |

### Decision de autenticacion (temporal, sujeta a confirmacion A-04)
Se usa **sesion nativa de PHP con cookie de sesion** (`HttpOnly`, `Secure` en HTTPS, `SameSite=Lax`). **No se usa JWT** en la version base porque:
- Es una aplicacion web servidor-renderizada (PHP) con navegador; la sesion de cookie es suficiente y mas simple.
- Agregar JWT sin una necesidad real (API stateless para clientes externos) añade complejidad, almacenamiento de tokens y superficie de ataque sin beneficio.

Si mas adelante se requiere una API stateless, se evaluara JWT como mejora aprobada (ver 01, mejora 2).

---

## 2. Arquitectura general

**Tipo:** Arquitectura en capas, monolito modular proporcionado al alcance (escuela, 1-2 integrantes). No microservicios, no capas excesivas.

```
Capa de presentacion (HTML/CSS/JS)  <-- peticiones fetch/AJAX -->  Capa de rutas/endpoints (PHP)
                                                                       |
                                                                       v
                                                               Capa de controladores
                                                                       |
                                                                       v
                                                           Capa de servicios (logica de negocio)
                                                                       |
                                                                       v
                                                           Capa de validaciones
                                                                       |
                                                                       v
                                                           Capa de acceso a datos (DAO/PDO)
                                                                       |
                                                                       v
                                                           Capa de modelos/entidades
                                                                       |
                                                                       v
                                                       Base de datos MariaDB/MySQL
```

**Transversal (cortan todas las capas):**
- Configuración (variables de entorno).
- Middleware de autenticacion, autorizacion y rate limiting.
- Manejo centralizado de errores.
- Logger (sin secretos).

### Responsabilidades por capa

| Capa | Responsabilidades | Prohibiciones |
|------|-------------------|---------------|
| Presentacion | HTML semántico, CSS con design tokens, JS vanilla para interactividad y peticiones fetch. | No contiene consultas SQL, ni reglas de negocio, ni acceso directo a BD. |
| Rutas/endpoints | Mapeo de URL a controladores. Parsing de cuerpo JSON / form. Respuesta JSON estandar. | No contiene logica de negocio. |
| Controladores | Reciben peticion, llaman al servicio, devuelven respuesta. No contienen reglas de negocio complejas. | No acceden directamente a BD (usan DAO/servicio). |
| Servicios | Logica de negocio: crear reserva, registrar pedido, cambiar estado, autenticar. Orquestan DAOs. | No conocen HTTP ni detalles de presentacion. |
| Validaciones | Reglas de entrada (tipos, rangos, formatos) y reglas de negocio (RB). | No mezcladas con presentacion. |
| Acceso a datos (DAO) | Consultas preparadas con PDO. Mapeo filas -> entidades. | No contiene logica de negocio ni HTML. |
| Modelos/entidades | Clases que representan tablas (Usuario, Reserva, Pedido, Mesa, Area, Producto, Rol). Datos puro + validacion basica. | No contienen consultas SQL. |
| Middleware (auth/rate limit) | Verificar sesion, rol, permisos, limitar peticiones (Token Bucket). | No contienen logica de negocio de dominio. |
| Configuracion | Variables de entorno, credenciales de BD, claves, rutas de archivos. Centralizada. | No contiene valores secretos hardcodeados. |
| Manejo centralizado de errores | Excepciones, respuestas HTTP (400, 401, 403, 404, 429, 500), mensajes seguros sin filtracion interna. | No revela consultas SQL, rutas ni stack traces al cliente. |

---

## 3. Estructura de carpetas

```
SportSlot/  (nombre de carpeta del proyecto, se puede renombrar a restaurante/)
├── public/                     # Punto de entrada unico y assets publicos
│   ├── index.php               # Front controller (enrutador simple)
│   ├── assets/
│   │   ├── css/
│   │   │   ├── design-tokens.css
│   │   │   ├── base.css
│   │   │   ├── components.css
│   │   │   └── layouts.css
│   │   ├── js/
│   │   │   ├── app.js
│   │   │   ├── api.js          # cliente fetch centralizado
│   │   │   ├── auth.js
│   │   │   ├── reservas.js
│   │   │   ├── pedidos.js
│   │   │   └── ui.js
│   │   └── img/
│   └── uploads/                # archivos subidos (si se aprueba mejora) - gitignore
├── src/
│   ├── Config.php              # carga de variables de entorno
│   ├── Database.php            # conexion PDO singleton
│   ├── Middleware/
│   │   ├── Auth.php            # autenticacion y autorizacion
│   │   └── RateLimiter.php     # Token Bucket
│   ├── Controller/
│   │   ├── BaseController.php
│   │   ├── AuthController.php
│   │   ├── UsuarioController.php
│   │   ├── AreaController.php
│   │   ├── MesaController.php
│   │   ├── ReservaController.php
│   │   ├── PedidoController.php
│   │   └── ProductoController.php
│   ├── Service/
│   │   ├── ServicioDeAutenticacion.php
│   │   ├── ServicioDeReservas.php
│   │   ├── ServicioDeMesas.php
│   │   ├── ServicioDePedidos.php
│   │   └── ServicioDeProductos.php
│   ├── Validator/
│   │   ├── ValidadorDeReserva.php
│   │   ├── ValidadorDePedido.php
│   │   └── ValidadorDeUsuario.php
│   ├── Dao/
│   │   ├── BaseDao.php
│   │   ├── UsuarioDao.php
│   │   ├── MesaDao.php
│   │   ├── ReservaDao.php
│   │   └── PedidoDao.php
│   ├── Model/
│   │   ├── Usuario.php
│   │   ├── Rol.php
│   │   ├── Area.php
│   │   ├── Mesa.php
│   │   ├── Reserva.php
│   │   ├── Pedido.php
│   │   └── Producto.php
│   └── Exception/
│       ├── AppException.php
│       └── ValidacionException.php
├── views/                      # vistas PHP (HTML parciales) - opcional si servidor-renderizado
├── migrations/                 # migraciones SQL versionadas (no ejecutadas en esta etapa)
│   ├── 001_create_schema.sql
│   └── ...
├── documentacion_desarrollo/   # DOCUMENTACION INTERNA - GITIGNORED
│   ├── 01_analisis_requisitos.md
│   ├── 02_stack_y_arquitectura.md
│   ├── 03_base_de_datos.md
│   ├── 04_diseno_y_accesibilidad.md
│   ├── 05_seguridad_y_privacidad.md
│   ├── 06_pruebas_y_calidad.md
│   ├── 07_git_ci_cd_y_docker.md
│   └── 08_hoja_de_ruta.md
├── documentacion/              # DOCUMENTACION VERSIONADA (guias de instalacion, etc.)
│   ├── guia_instalacion_xampp.md
│   ├── guia_instalacion_docker.md
│   ├── guia_despliegue_hosting.md
│   ├── tabla_comparativa_entornos.md
│   ├── variables_configuracion.md
│   ├── estrategia_migraciones.md
│   └── pruebas_portabilidad.md
├── docker-compose.yml
├── Dockerfile
├── .env.example                # ejemplo sin secretos reales
├── .env                        # local, gitignore (valores reales)
├── .gitignore
└── README.md
```

**Nota:** `composer.json` es opcional y solo para autoload PSR-4 de clases; no es un framework ni dependencia pesada. Si no se usa, se cargan clases con `spl_autoload_register` nativo.

---

## 4. Convenciones de nomenclatura (identificadores en espanol)

| Elemento | Convencion | Ejemplo |
|----------|------------|---------|
| Clases | PascalCase | `ServicioDeReservas`, `MesaDao` |
| Variables | camelCase | `fechaSeleccionada`, `idMesa` |
| Funciones / metodos | camelCase | `crearReserva`, `verificarContrasena` |
| Constantes | UPPER_SNAKE_CASE | `DURACION_MAXIMA_RESERVA`, `ESTADO_CONFIRMADA` |
| Tablas BD | snake_case | `usuarios`, `mesas`, `reservas`, `pedidos` |
| Columnas BD | snake_case | `id_usuario`, `fecha_reserva`, `id_mesa` |
| Claves foraneas | snake_case con sufijo _id | `id_mesa`, `id_usuario`, `id_area` |
| Archivos PHP | PascalCase para clases, descriptivo en espanol | `ServicioDeReservas.php`, `ValidadorDeReserva.php` |
| Archivos JS/CSS | kebab-case o camelCase descriptivo | `api.js`, `design-tokens.css` |
| Rutas/endpoints | kebab-case, en espanol | `/api/reservas`, `/api/mesas/disponibilidad`, `/api/pedidos` |
| Mensajes de interfaz | Espanol claro, natural, consistente | "La mesa ya esta reservada en ese horario." |

**No se traducen:** nombres de funciones nativas de PHP/JS, metodos de PDO, directivas de protocolo, estandares externos (JWT, bcrypt, HTTP, SQL).

---

## 5. Estrategia de configuracion por entorno

- Configuracion separada del codigo fuente.
- Valores por variable de entorno. Archivo `.env` local (gitignored) con valores reales. Archivo `.env.example` versionado sin secretos.
- No se usa `localhost` hardcodeado para BD en todos los entornos:
  - XAMPP: `DB_HOST=127.0.0.1` (o localhost segun XAMPP).
  - Docker: `DB_HOST=mariadb` (nombre del servicio).
  - Hosting: direccion configurada por el proveedor.

Variables necesarias (documentadas en `documentacion/variables_configuracion.md`):
- `APP_ENV` (development, testing, production)
- `APP_URL` (direccion publica de la aplicacion)
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
- `SESSION_COOKIE_SECURE` (0/1), `SESSION_COOKIE_SAMESITE`
- `RATE_LIMIT_CAPACITY`, `RATE_LIMIT_REFILL_RATE`
- Claves de firma solo si se usa JWT (pendiente A-04).

---

## 6. Contratos frontend <-> backend y respuestas API

- Endpoints devuelven JSON con formato estandar:
  ```json
  {
    "exito": true,
    "datos": { ... },
    "mensaje": "Operacion exitosa",
    "errores": null
  }
  ```
  En caso de error: `"exito": false`, `"codigo"` (HTTP), `"mensaje"` legible para el usuario, `"detalles"` opcional (sin secretos ni SQL).
- Códigos HTTP: 200 OK, 201 Creado, 204 Sin contenido, 400 Solicitud incorrecta, 401 No autorizado, 403 Prohibido, 404 No encontrado, 409 Conflicto (reserva superpuesta), 429 Demasiadas solicitudes, 500 Error interno.
- Content-Type: `application/json` para API. `text/html` para vistas.
- Autenticacion: cookie de sesion PHP enviada automaticamente por el navegador.

---

## 7. Estrategia para agregar funcionalidades sin romper las existentes

- Nuevos endpoints bajo rutas nuevas (no modificar endpoints existentes de forma incompatible).
- Nuevas tablas via migraciones versionadas (no alterar tablas existentes de forma destructiva).
- Validaciones nuevas en capas separadas; los servicios existentes no se modifican, se extienden.
- Versionado implícito por ruta: `/api/v1/...` si en el futuro se requiere romper compatibilidad.

---

## 8. Decisiones pendientes / requieren confirmacion

| # | Decision | Opciones | Recomendacion actual |
|---|----------|----------|----------------------|
| A-04 | JWT vs sesion de cookie | Sesion cookie nativa / JWT | Sesion cookie nativa (sin JWT por ahora) |
| A-06 | Politica de vencimiento de reserva | Tiempo fijo / sin vencimiento automatico | Por definir con docente |
| A-07 | Tiempo maximo de anticipacion para reservar | 7 dias / 30 dias / ilimitado | Por definir |

---

## 9. Incompatibilidades detectadas y resolucion

| Incompatibilidad | Resolucion |
|------------------|------------|
| SHA-256 para contrasenas (prohibido) vs seguridad | Usar bcrypt exclusivamente para contrasenas. SHA-256 solo si hay necesidad justificada y documentada. |
| JWT automatico con sesion de cookie (desaconsejado) | No se usa JWT por defecto. Solo sesion de cookie. |
| Dos entornos redundantes (XAMPP + Docker simultaneos) | Se documentan como alternativas. El codigo es independiente del entorno (config por variables de entorno). |
| `localhost` para BD en Docker | En Docker se usa nombre del servicio `mariadb`; en XAMPP/hosting la direccion configurada. |
