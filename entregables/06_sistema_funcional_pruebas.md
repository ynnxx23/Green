# 06 - Sistema funcional y pruebas

Documentación de las pruebas realizadas, errores corregidos, ejecución del sistema y conclusión del proyecto "Green en la Casa Blanca".

## 1. Pruebas realizadas

Se verificó el correcto funcionamiento de las siguientes funcionalidades:

### Autenticación

- Login y registro como cliente, encargado y administrador.
- Sesión persistida con cookie `HttpOnly` y redirección al panel según el rol.
- Bloqueo temporal por intentos de ingreso fallidos.
- Recuperación de contraseña con token de un solo uso.

### Carta y e-commerce

- Catálogo con 30 platos activos, precio, imagen y **stock visible** en cada tarjeta.
- Filtro por categoría (entradas, principales, postres, bebidas).
- **Modal de cantidad** al pulsar «Pedir», con tope por stock disponible.
- **Carrito persistente** entre páginas, con contador en el navbar.
- **Carrito en dos columnas:** pedido a la izquierda, lista de platos a la derecha con scroll propio y filtro por categoría.
- Tarjetas de **tamaño uniforme** (394 px) con el botón «Pedir» en la misma posición en todas.
- Carta destacada del inicio con las cuatro categorías, incluida la coctelería.

### Reservas

- Creación de reserva en una fecha con cupo abierto.
- **Selector de fecha restringido:** solo se habilitan los días con disponibilidad.
- **Selector de hora filtrado:** solo ofrece las franjas de esa fecha.
- **Asignación automática de mesa** por capacidad y sin solapamiento.
- Reserva **sin sesión**: el sistema crea o reutiliza el usuario por correo.
- Reserva **confirmada directamente** (no queda pendiente de aprobación).
- Cancelación de reserva propia.

### Pedidos

- Creación de pedido para llevar y a domicilio respetando stock y límite de venta.
- Seña del 50 % automática y cálculo del saldo pendiente.
- Descuento real del stock al confirmar el pedido.

### Gestión (encargado y administrador)

- **Generación masiva de cupos**: rango de fechas + días de la semana + franja horaria + mesas por cupo.
- **Eliminación de cupos por rango** de fechas.
- Alta y edición de platos de la carta, y activación/desactivación.
- Edición de stock y límite de venta por producto.
- Cambio de estado de pedido, de seña y de reserva.
- Creación de usuario y reportes administrativos.

## 2. Corrección de errores

Durante las pruebas se detectaron y corrigieron los siguientes errores:

### Errores de la primera etapa

| Error detectado | Corrección aplicada |
| --- | --- |
| Navbar roto: links apilados verticalmente | Navbar con flexbox: logo a la izquierda, enlaces centrados, carrito y sesión a la derecha |
| Fondo hero roto: imagen `hero-comida` inexistente | Fondo cambiado a `plato.png` con opacidad suave |
| Verde de marca muy oscuro | Verde aclarado: primario `#2E5F4E`, secundario `#4A8C6F` |
| Roles reducidos/inconsistentes | Estandarizados a 3 roles: cliente, encargado, administrador |
| No había botón de registro en el navbar | Registro movido dentro del flujo de login |
| `id_mesa` era NOT NULL en pedidos | `id_mesa` ahora es nullable para permitir pedidos sin mesa |
| No existía historial de movimientos | Se agregó registro de historial en cada acción relevante |
| No existían reportes administrativos | Se implementaron reportes de ventas, productos, reservas y días |

### Errores detectados en la etapa de estabilización

| Error detectado | Causa raíz | Corrección aplicada |
| --- | --- | --- |
| 404 de `api.js` en `http://…/assets/js/assets/js/api.js` | `main.js` importaba `./assets/js/api.js` estando ya en la carpeta `assets/js/` | Import corregido a `./api.js` |
| Conexión MySQL con host y usuario vacíos | `Config::obtener()` usaba `??` con `getenv()`, que devuelve `false` (no `null`) y por tanto no se saltaba en la cadena | Lectura explícita de `$_ENV` → `$_SERVER` → `getenv()` → valor por defecto |
| Variables del `.env` intermitentes entre peticiones | `putenv()` persiste en el worker de Apache, pero `$_ENV` se reinicia vacío (`variables_order="GPCS"` no incluye `E`) | El cargador del `.env` consulta `$_SERVER` en lugar de `getenv()` para decidir si la variable viene del contenedor |
| Clave `APP_ENV` corrompida por BOM UTF-8 | El `.env` empezaba con BOM, que quedaba pegado al nombre de la primera clave | BOM eliminado del `.env` y el cargador ahora lo ignora si aparece |
| Acentos corruptos en la base de datos (`Caf?? expreso`) | El cliente `mysql.exe` de XAMPP usaba el codepage de la consola en lugar de UTF-8 | Importaciones con `--default-character-set=utf8mb4` y datos reparados |
| `SyntaxError: Unexpected token ')'` en `index.html` | El bucle `for` cerraba con `});` en vez de `}` | Cierre corregido; el módulo entero_FAILED_TO_PARSE y no se pintaba la carta destacada |
| Botón «Pedir» invisible en la carta | El atributo `onclick` no cerraba la comilla double, y el parser HTML descartaba el `<button>` completo | Botón reescrito con atributos `data-*` y listener delegado |
| `mostrar is not defined` en los paneles | Las funciones de los `<script type="module">` no quedan en `window` y los `onclick` inline no las ven | Funciones expuestas explícitamente en `window` |
| `api.js` con dos versiones distintas en memoria | El `<script src="…api.js?v=2">` y el `import` inline usaban especificadores distintos, así que eran dos módulos | Unificado a `?v=3` en `<script>` e `import`, con cache-busting |
| Reservas que no se guardaban | El `if ($ruta === '' || $ruta === 'listar')` con respuesta 405 se ejecutaba antes del bloque de `POST` | Reordenados los bloques de enrutado en `ReservaController`, `manejarStock` y `manejarDisponibilidadAdmin` |
| «There is already an active transaction» al reservar | `ReservaDao::crearConBloqueo` abría una transacción dentro de la del controlador | El DAO reutiliza la transacción externa si ya hay una activa |
| `Unknown database 'restaurante'` | `Config::obtener()` caía al valor por defecto cuando la variable no estaba disponible | Corregido junto con el fallo de `getenv()` descrito arriba |
| Pedidos sin dirección de entrega | `pedidos.observaciones` no existía en la base de datos | Columna agregada (migración 024) y `direccion_domicilio` expuesta en las respuestas |
| «Method Not Allowed» al guardar stock o disponibilidad | Mismo problema de orden de bloques que en las reservas | Reordenados los bloques de enrutado |
| Historial sin registro de pedidos a domicilio | El `id_registro` del historial no se guardaba para los pedidos | Registro de `CREAR_PEDIDO` y `CREAR_RESERVA` verificado |

### Errores detectados en la etapa de e-commerce

| Error detectado | Causa raíz | Corrección aplicada |
| --- | --- | --- |
| El plato agregado no aparecía en ningún carrito | El carrito se guardaba en `pedido_carrito` y lo consumía `pedidos.html`, que además lo borraba | Nuevo módulo `carrito.js` con clave `pedido_carrito_v2`, migración del carrito antiguo y persistencia entre páginas |
| El stock no se mostraba en las tarjetas | El producto no incluye `stock_actual` en la plantilla de la carta | Indicador de stock con tres niveles: disponible, bajo y agotado |
| Tarjetas de tamaño dispares y botón en distinto sitio | El botón se construía dentro del HTML con `onclick` y dependía del largo del nombre | Tarjetas con `flex` de altura fija, imagen y contenido de altura mínima, y botón en un pie siempre alineado |
| La lista de platos desplazaba la página completa | El carrito era una sola columna vertical | Layout de dos columnas con columna derecha `sticky` y lista con `overflow-y: auto` |
| Sin filtro en la lista de platos | No existía selector de categoría en la página de pedido | Filtro por categoría generado dinámicamente desde los productos activos |

## 3. Pruebas de historial

Se verificó que se registren movimientos de historial en las siguientes acciones:

| Acción | Movimiento esperado |
| --- | --- |
| Crear reserva | Registro de nueva reserva con fecha, mesa y cliente |
| Cancelar reserva | Registro de cancelación con motivo y usuario |
| Cambiar estado de reserva | Registro de transición de estado (ej. pendiente → confirmada) |
| Crear pedido | Registro de nuevo pedido con platos, mesa y total |
| Cambiar estado de pedido | Registro de transición de estado (ej. pendiente → en_preparación) |

Cada movimiento queda asociado al usuario que lo ejecutó y a la fecha/hora del suceso.

## 4. Pruebas de reportes

Se verificó que el administrador pueda consultar los siguientes reportes desde el panel:

- Ventas por periodo: filtro por rango de fechas con totales de venta.
- Productos más vendidos: ranking de platos por cantidad vendida.
- Días con más reservas: conteo de reservas agrupado por fecha.
- Días con más ventas: conteo y monto de ventas agrupado por fecha.
- Historial filtrado: búsqueda de movimientos por usuario, acción o rango de fechas.

## 5. Pruebas de normalización

Se verificó la creación de pedidos sin depender de una mesa física:

- Pedido para llevar: se crea un pedido sin asignar `id_mesa` (mesa nula).
- Pedido a domicilio: se crea un pedido sin mesa y con dirección de entrega registrada.

## 6. Pruebas de estabilidad

- **50 peticiones consecutivas** contra cinco endpoints públicos: 0 errores 5xx.
- **14 páginas públicas y de panel** cargadas sin errores de consola ni recursos fallidos.
- Carga y borrado de la base de datos de prueba con las 24 migraciones aplicadas.

## 7. Cómo ejecutar el sistema

### Requisitos

- XAMPP (Apache y MySQL/MariaDB) o Docker.
- PHP 8.2 o superior.
- MariaDB / MySQL.

### Pasos en XAMPP (entorno local)

1. Copiar el proyecto a `C:\xampp\htdocs\Yensel\Morday` con `public/` como raíz pública.
2. Crear el archivo `.env` con las credenciales de conexión:

   ```env
   APP_ENV=development
   APP_URL=http://localhost/Yensel/Morday/public
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=restaurante_dev
   DB_USER=root
   DB_PASSWORD=
   SESSION_COOKIE_SECURE=0
   ```

3. Crear la base de datos y aplicar las migraciones en orden:

   ```powershell
   powershell -ExecutionPolicy Bypass -File aplicar_todas_migraciones.ps1
   ```

   El script aplica las migraciones `001` a `024` con `--default-character-set=utf8mb4` para
   preservar los acentos.

4. Iniciar Apache y MySQL desde el panel de XAMPP y acceder a
   `http://localhost/Yensel/Morday/public/`.

> **Nota:** el `.env` no lleva BOM (Byte Order Mark). Si lo edita un editor de Windows,
> guárdelo en UTF-8 **sin BOM**, o la primera variable quedará corrompida.

### Pasos en Docker

```bash
docker compose up -d
```

### Cuentas de prueba

| Rol | Correo | Contraseña |
| --- | --- | --- |
| Administrador | `admin@restaurante.local` | `admin123` |
| Administrador | `laura.admin@test.local` | `admin123` |
| Encargado | `carlos.encargado@test.local` | `admin123` |
| Cliente | `cliente@demo.com` | `cliente123` |
| Cliente | `maria.cliente@test.local` | `admin123` |

## 8. Conclusión

El sistema se encuentra completamente funcional con los 3 roles definidos (cliente, encargado, administrador). Todas las funcionalidades del problema han sido resueltas: autenticación, carta con stock visible, **reservas confirmadas directamente con días y horas restringidos a la disponibilidad real**, **e-commerce con carrito persistente, modal de cantidad y pedidos a domicilio**, seña y saldo, cambio de estados, **cupos de reserva generados en lote desde ambos paneles de gestión**, **alta y edición de platos y stock**, gestión de usuarios, bloqueo de mesas y reportes. El panel despachador redirige correctamente según el rol de cada usuario.

---

**Archivo creado:** `entregables/06_sistema_funcional_pruebas.md`
