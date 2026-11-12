/*
  carrito.js — Carrito de compras compartido (e-commerce)

  Gestiona el carrito en localStorage y expone helpers para:
  - agregar / quitar / modificar cantidades
  - pintar el badge con el total de articulos en el nav
  - abrir la modal de cantidad antes de agregar un plato

  Se incluye como <script type="module"> y expone window.Carrito para poder
  usarse desde los scripts inline de cada pagina.
*/

const CLAVE_STORAGE = 'pedido_carrito_v2';

function leerCarrito() {
  try {
    const raw = localStorage.getItem(CLAVE_STORAGE);
    if (!raw) return {};
    const datos = JSON.parse(raw);
    return (datos && typeof datos === 'object') ? datos : {};
  } catch (e) {
    return {};
  }
}

function guardarCarrito(carrito) {
  try {
    localStorage.setItem(CLAVE_STORAGE, JSON.stringify(carrito));
  } catch (e) {
    console.warn('No se pudo guardar el carrito', e);
  }
  actualizarBadge();
  window.dispatchEvent(new CustomEvent('carrito:cambio', { detail: carrito }));
}

// Estructura de un item: { id, nombre, precio, imagen, cantidad, stock }
function obtenerItems() {
  return Object.values(leerCarrito());
}

function totalArticulos() {
  return obtenerItems().reduce((suma, item) => suma + (Number(item.cantidad) || 0), 0);
}

function totalPrecio() {
  return obtenerItems().reduce((suma, item) => suma + (Number(item.precio) || 0) * (Number(item.cantidad) || 0), 0);
}

function agregarItem(producto, cantidad) {
  const carrito = leerCarrito();
  const id = String(producto.id_producto);
  const cant = Math.max(1, parseInt(cantidad, 10) || 1);
  const stock = producto.stock_actual !== undefined && producto.stock_actual !== null
    ? Number(producto.stock_actual)
    : null;

  const existente = carrito[id];
  const nuevaCantidad = (existente ? Number(existente.cantidad) : 0) + cant;

  if (stock !== null && nuevaCantidad > stock) {
    throw new Error('Solo quedan ' + stock + ' unidades de ' + producto.nombre + '.');
  }

  carrito[id] = {
    id: producto.id_producto,
    nombre: producto.nombre,
    precio: Number(producto.precio) || 0,
    imagen: producto.imagen_url || '',
    cantidad: nuevaCantidad,
    stock: stock
  };

  guardarCarrito(carrito);
  return carrito[id];
}

function quitarItem(idProducto) {
  const carrito = leerCarrito();
  delete carrito[String(idProducto)];
  guardarCarrito(carrito);
}

function vaciarCarrito() {
  guardarCarrito({});
}

function actualizarBadge() {
  const total = totalArticulos();
  document.querySelectorAll('[data-carrito-badge]').forEach(el => {
    el.textContent = total > 0 ? String(total) : '';
    el.setAttribute('data-vacio', total > 0 ? '0' : '1');
  });
  document.querySelectorAll('[data-carrito-total]').forEach(el => {
    el.textContent = total > 0 ? String(total) : '';
  });
}

// ---------------------------------------------------------------------------
// Modal de cantidad
// ---------------------------------------------------------------------------
let modalActual = null;

function cerrarModal() {
  if (!modalActual) return;
  modalActual.classList.remove('abierta');
  const el = modalActual;
  modalActual = null;
  setTimeout(() => { if (el.parentNode) el.parentNode.removeChild(el); }, 220);
}

function crearModal() {
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.setAttribute('role', 'dialog');
  overlay.setAttribute('aria-modal', 'true');
  overlay.setAttribute('aria-label', 'Cantidad');
  overlay.innerHTML = `
    <div class="modal">
      <div class="modal-header">
        <h3 id="modal-titulo">Agregar al pedido</h3>
        <button type="button" class="modal-cerrar" aria-label="Cerrar">&times;</button>
      </div>
      <div class="modal-body">
        <div class="modal-producto">
          <img id="modal-img" src="" alt="">
          <div class="modal-producto-info">
            <h4 id="modal-nombre"></h4>
            <p id="modal-categoria"></p>
            <p class="precio" id="modal-precio"></p>
          </div>
        </div>
        <div class="modal-cantidad">
          <label for="modal-input-cantidad">Cantidad</label>
          <div class="cantidad-control">
            <button type="button" id="modal-menos" aria-label="Restar">&minus;</button>
            <span id="modal-cantidad">1</span>
            <button type="button" id="modal-mas" aria-label="Sumar">+</button>
          </div>
        </div>
        <div class="modal-total">
          <span>Total</span>
          <span class="valor" id="modal-total">$ 0</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" id="modal-cancelar">Cancelar</button>
        <button type="button" class="btn-modal-ok" id="modal-agregar">Agregar al pedido</button>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);
  return overlay;
}

function formatearMoneda(valor) {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(valor || 0);
}

function abrirModalCantidad(producto, alConfirmar) {
  cerrarModal();

  const overlay = crearModal();
  modalActual = overlay;

  const stock = producto.stock_actual !== undefined && producto.stock_actual !== null
    ? Number(producto.stock_actual)
    : null;
  let cantidad = 1;

  const elImg = overlay.querySelector('#modal-img');
  const elNombre = overlay.querySelector('#modal-nombre');
  const elCategoria = overlay.querySelector('#modal-categoria');
  const elPrecio = overlay.querySelector('#modal-precio');
  const elCantidad = overlay.querySelector('#modal-cantidad');
  const elTotal = overlay.querySelector('#modal-total');
  const btnMenos = overlay.querySelector('#modal-menos');
  const btnMas = overlay.querySelector('#modal-mas');
  const btnAgregar = overlay.querySelector('#modal-agregar');

  elImg.src = producto.imagen_url || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=300&fit=crop';
  elImg.alt = producto.nombre || '';
  elNombre.textContent = producto.nombre || '';
  elCategoria.textContent = producto.categoria || '';
  elPrecio.textContent = formatearMoneda(producto.precio);

  function refrescar() {
    elCantidad.textContent = String(cantidad);
    elTotal.textContent = formatearMoneda((Number(producto.precio) || 0) * cantidad);
    btnMenos.disabled = cantidad <= 1;
    btnMas.disabled = stock !== null && cantidad >= stock;
    btnAgregar.disabled = stock !== null && stock <= 0;
  }

  btnMenos.addEventListener('click', () => {
    if (cantidad > 1) { cantidad--; refrescar(); }
  });
  btnMas.addEventListener('click', () => {
    if (stock === null || cantidad < stock) { cantidad++; refrescar(); }
  });
  overlay.querySelector('.modal-cerrar').addEventListener('click', cerrarModal);
  overlay.querySelector('#modal-cancelar').addEventListener('click', cerrarModal);
  overlay.addEventListener('click', (e) => { if (e.target === overlay) cerrarModal(); });
  document.addEventListener('keydown', function onEsc(e) {
    if (e.key === 'Escape') { cerrarModal(); document.removeEventListener('keydown', onEsc); }
  });

  btnAgregar.addEventListener('click', () => {
    cerrarModal();
    if (typeof alConfirmar === 'function') alConfirmar(cantidad);
  });

  refrescar();
  overlay.classList.add('abierta');
}

// ---------------------------------------------------------------------------
// API pública
// ---------------------------------------------------------------------------
window.Carrito = {
  CLAVE_STORAGE: CLAVE_STORAGE,
  leer: leerCarrito,
  items: obtenerItems,
  totalArticulos: totalArticulos,
  totalPrecio: totalPrecio,
  agregar: agregarItem,
  quitar: quitarItem,
  vaciar: vaciarCarrito,
  actualizarBadge: actualizarBadge,
  abrirModalCantidad: abrirModalCantidad,
  formatearMoneda: formatearMoneda,
  cerrarModal: cerrarModal
};

// Si habia un carrito en la clave vieja (pedido_carrito), migrarlo
(function migrarCarritoViejo() {
  try {
    const viejo = localStorage.getItem('pedido_carrito');
    if (!viejo) return;
    const datos = JSON.parse(viejo);
    if (datos && typeof datos === 'object' && Object.keys(datos).length) {
      // El carrito viejo solo guardaba cantidades; se migra al formato nuevo
      // y luego se borra para no duplicar.
      const nuevo = leerCarrito();
      Object.entries(datos).forEach(([id, cant]) => {
        const clave = String(id);
        if (!nuevo[clave]) {
          nuevo[clave] = { id: parseInt(id, 10), nombre: 'Producto #' + id, precio: 0, imagen: '', cantidad: Number(cant) || 1, stock: null };
        }
      });
      guardarCarrito(nuevo);
    }
    localStorage.removeItem('pedido_carrito');
  } catch (e) {
    localStorage.removeItem('pedido_carrito');
  }
})();

actualizarBadge();
