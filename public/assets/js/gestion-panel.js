/*
  gestion-panel.js — Módulo compartido de gestión para paneles

  Contiene la lógica reutilizable por el panel de encargado y el de admin:
  - Gestión de stock y límites de venta por plato
  - Alta / edición / activación de platos
  - Creación masiva de cupos de disponibilidad (reservas)
  - Calendario de disponibilidad

  Se exporta como window.GestionPanel para poder usarse desde los scripts
  inline de cada página.
*/

(function () {
  'use strict';

  const DIAS_SEMANA = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
  const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
    'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

  const estado = {
    productos: [],
    stock: [],
    disponibilidad: [],
    mesSeleccionado: new Date(),
    fechasSeleccionadas: new Set()
  };

  // -----------------------------------------------------------------------
  // Utilidades
  // -----------------------------------------------------------------------
  function escapeHtml(texto) {
    const d = document.createElement('div');
    d.textContent = texto === null || texto === undefined ? '' : String(texto);
    return d.innerHTML;
  }

  function iso(d) {
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  function $(id) {
    return document.getElementById(id);
  }

  /**
   * Muestra una alerta en un contenedor.
   */
  function alerta(id, texto, tipo = 'error') {
    const el = $(id);
    if (!el) return;
    el.textContent = texto;
    el.className = 'alerta alerta--' + tipo;
    el.style.display = 'block';
  }

  function limpiarAlerta(id) {
    const el = $(id);
    if (!el) return;
    el.textContent = '';
    el.style.display = 'none';
  }

  // -----------------------------------------------------------------------
  // Productos y stock
  // -----------------------------------------------------------------------
  async function cargarProductos(api) {
    try {
      const r = await api.productos();
      estado.productos = r.datos || [];
    } catch (e) {
      estado.productos = [];
      console.error('productos', e);
    }
    return estado.productos;
  }

  async function cargarStock(api) {
    try {
      const r = await api.stock();
      estado.stock = r.datos || [];
    } catch (e) {
      estado.stock = [];
      console.error('stock', e);
    }
    renderStock();
    return estado.stock;
  }

  function renderStock() {
    const grid = $('grid-stock');
    if (!grid) return;
    grid.innerHTML = '';
    if (!estado.productos.length) {
      grid.innerHTML = '<p class="vacio">No hay productos.</p>';
      return;
    }

    estado.productos.forEach(function (p) {
      const s = estado.stock.find(function (x) { return x.id_producto === p.id_producto; }) || { stock_actual: 0, limite_venta: 0 };
      const card = document.createElement('div');
      card.className = 'stock-card';
      card.innerHTML =
        '<h4>' + escapeHtml(p.nombre) + '</h4>' +
        '<p style="margin:0; font-size:0.82rem; color:var(--color-text-muted);">' + escapeHtml(p.categoria || '') + ' — $' + Number(p.precio || 0).toFixed(2) + '</p>' +
        '<div class="row">' +
          '<span class="stock" style="font-size:0.8rem; font-weight:600;">Stock actual:</span>' +
          '<input type="number" min="0" step="1" data-id="' + p.id_producto + '" data-producto="' + p.id_producto + '" data-campo="stock_actual" value="' + (Number(s.stock_actual) || 0) + '">' +
        '</div>' +
        '<div class="row">' +
          '<span class="limite" style="font-size:0.8rem; font-weight:600;">Límite venta:</span>' +
          '<input type="number" min="0" step="1" data-id="' + p.id_producto + '" data-producto="' + p.id_producto + '" data-campo="limite_venta" title="0 = sin límite" value="' + (Number(s.limite_venta) || 0) + '">' +
        '</div>';
      grid.appendChild(card);
    });
  }

  async function guardarStock(api, mostrarToast) {
    const inputs = document.querySelectorAll('#grid-stock input[data-campo]');
    if (!inputs.length) {
      alerta('alerta-stock', 'No hay cambios que guardar.', 'warning');
      return false;
    }

    // Agrupar por producto (un input por campo)
    const cambios = {};
    inputs.forEach(function (inp) {
      const id = inp.dataset.producto || inp.dataset.id;
      if (!cambios[id]) cambios[id] = {};
      cambios[id][inp.dataset.campo] = parseInt(inp.value, 10) || 0;
    });

    try {
      for (const [id, datos] of Object.entries(cambios)) {
        await api.guardarStock({ id_producto: parseInt(id, 10), ...datos });
      }
      alerta('alerta-stock', 'Stock y límites actualizados correctamente.', 'exito');
      if (mostrarToast) mostrarToast('Stock y límites guardados.', 'exito');
      await cargarStock(api);
      return true;
    } catch (e) {
      alerta('alerta-stock', e.message || 'Error al guardar el stock.', 'error');
      return false;
    }
  }

  // -----------------------------------------------------------------------
  // Alta / edicion de productos
  // -----------------------------------------------------------------------
  function renderProductosAdmin() {
    const wrap = $('tabla-productos-admin');
    if (!wrap) return;
    wrap.innerHTML = '';

    if (!estado.productos.length) {
      wrap.innerHTML = '<p class="vacio">No hay productos registrados.</p>';
      return;
    }

    let html = '<table><thead><tr><th>Imagen</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acción</th></tr></thead><tbody>';
    estado.productos.forEach(function (p) {
      const s = estado.stock.find(function (x) { return x.id_producto === p.id_producto; });
      const stock = s ? Number(s.stock_actual) : null;
      const img = p.imagen_url
        ? '<img src="' + escapeHtml(p.imagen_url) + '" class="imagen-preview" alt="' + escapeHtml(p.nombre) + '">'
        : '<span class="sin-imagen">Sin imagen</span>';
      html += '<tr>' +
        '<td>' + img + '</td>' +
        '<td>' + escapeHtml(p.nombre) + '</td>' +
        '<td>' + escapeHtml(p.categoria || '') + '</td>' +
        '<td>$' + Number(p.precio || 0).toFixed(2) + '</td>' +
        '<td>' + (stock === null ? '-' : stock) + '</td>' +
        '<td><span class="estado estado--' + (p.activo ? 'confirmada' : 'cancelada') + '">' + (p.activo ? 'Activo' : 'Inactivo') + '</span></td>' +
        '<td>' +
          '<button class="btn-sm btn-accion" data-editar-producto="' + p.id_producto + '">Editar</button>' +
          '<button class="btn-sm btn-accion" style="margin-left:0.4rem;" data-activar-producto="' + p.id_producto + '" data-valor="' + (!p.activo) + '">' +
            (p.activo ? 'Desactivar' : 'Activar') +
          '</button>' +
        '</td>' +
      '</tr>';
    });
    html += '</tbody></table>';
    wrap.innerHTML = html;
  }

  function preencherFormularioProducto(producto) {
    const campoNombre = $('inp-producto-nombre-display');
    if (campoNombre) campoNombre.value = producto ? producto.nombre : '';

    const campoCategoria = $('inp-producto-categoria');
    if (campoCategoria) {
      if (producto) campoCategoria.value = producto.categoria || 'Entradas';
      else if (campoCategoria.tagName === 'SELECT') campoCategoria.value = 'Entradas';
    }

    const campoPrecio = $('inp-producto-precio');
    if (campoPrecio) campoPrecio.value = producto ? Number(producto.precio || 0) : '';

    const campoImagen = $('inp-producto-imagen-url');
    if (campoImagen) campoImagen.value = producto ? (producto.imagen_url || '') : '';

    // Id del producto a editar (0 = nuevo). Puede estar en un hidden o en un select.
    const oculto = $('inp-producto-id');
    if (oculto) oculto.value = producto ? producto.id_producto : '';
    const select = $('inp-producto-nombre');
    if (select && select.tagName === 'SELECT') select.value = producto ? producto.id_producto : '';

    const titulo = $('titulo-form-producto');
    if (titulo) {
      titulo.textContent = producto
        ? 'Editar plato #' + producto.id_producto
        : 'Agregar plato nuevo';
    }
  }

  /**
   * Id del producto seleccionado en el formulario (0 = nuevo).
   */
  function idProductoFormulario() {
    const oculto = $('inp-producto-id');
    if (oculto && oculto.value !== '') return parseInt(oculto.value, 10) || 0;
    const select = $('inp-producto-nombre');
    if (select && select.tagName === 'SELECT') return parseInt(select.value, 10) || 0;
    return 0;
  }

  async function guardarProducto(api, mostrarToast) {
    const id = idProductoFormulario();
    const campoNombre = $('inp-producto-nombre-display');
    const nombre = campoNombre ? campoNombre.value.trim() : '';
    const categoria = $('inp-producto-categoria') ? $('inp-producto-categoria').value.trim() : '';
    const precio = parseFloat($('inp-producto-precio') ? $('inp-producto-precio').value : 0) || 0;
    const imagen_url = $('inp-producto-imagen-url')
      ? ($('inp-producto-imagen-url').value.trim() || null)
      : null;

    if (!nombre) {
      alerta('alerta-productos', 'El nombre es obligatorio.', 'error');
      return false;
    }
    if (precio <= 0) {
      alerta('alerta-productos', 'El precio debe ser mayor a cero.', 'error');
      return false;
    }

    try {
      if (id > 0) {
        await api._request('/productos/' + id, {
          method: 'PUT',
          body: JSON.stringify({ nombre, precio, categoria, imagen_url })
        });
        alerta('alerta-productos', 'Plato actualizado correctamente.', 'exito');
      } else {
        await api._request('/productos', {
          method: 'POST',
          body: JSON.stringify({ nombre, precio, categoria, imagen_url })
        });
        alerta('alerta-productos', 'Plato agregado correctamente.', 'exito');
      }
      if (mostrarToast) mostrarToast(id > 0 ? 'Plato actualizado.' : 'Plato agregado.', 'exito');
      preencherFormularioProducto(null);
      await cargarProductos(api);
      await cargarStock(api);
      renderProductosAdmin();
      return true;
    } catch (e) {
      alerta('alerta-productos', e.message || 'Error al guardar el plato.', 'error');
      return false;
    }
  }

  async function activarProducto(api, id, activar, mostrarToast) {
    try {
      await api._request('/productos/' + id + '/activar', { method: 'PUT' });
      alerta('alerta-productos', activar ? 'Plato activado.' : 'Plato desactivado.', 'exito');
      if (mostrarToast) mostrarToast(activar ? 'Plato activado.' : 'Plato desactivado.', 'exito');
      await cargarProductos(api);
      await cargarStock(api);
      renderProductosAdmin();
    } catch (e) {
      alerta('alerta-productos', e.message || 'Error al cambiar el estado.', 'error');
    }
  }

  // -----------------------------------------------------------------------
  // Disponibilidad / cupos de reservas
  // -----------------------------------------------------------------------
  async function cargarDisponibilidad(api, fecha) {
    try {
      const r = await api.adminDisponibilidad(fecha ? { fecha } : {});
      estado.disponibilidad = r.datos || [];
    } catch (e) {
      estado.disponibilidad = [];
      console.error('disponibilidad', e);
    }
    return estado.disponibilidad;
  }

  function renderCalendarioDisp() {
    const grid = $('grid-calendario-disp');
    if (!grid) return;

    const anio = estado.mesSeleccionado.getFullYear();
    const mes = estado.mesSeleccionado.getMonth();
    const elMes = $('cal-mes-disp');
    if (elMes) elMes.textContent = MESES[mes] + ' ' + anio;

    const primero = new Date(anio, mes, 1);
    const offset = primero.getDay();
    const diasEnMes = new Date(anio, mes + 1, 0).getDate();
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    const conDisponibilidad = new Set(estado.disponibilidad.map(function (d) { return d.fecha; }));

    let html = '';
    DIAS_SEMANA.forEach(function (d) { html += '<div class="cal-dia-nombre">' + d + '</div>'; });

    for (let i = 0; i < offset; i++) {
      html += '<div class="cal-dia" style="visibility:hidden;"></div>';
    }

    for (let d = 1; d <= diasEnMes; d++) {
      const fecha = new Date(anio, mes, d);
      const fechaStr = iso(fecha);
      const disponible = conDisponibilidad.has(fechaStr);
      const esHoy = fecha.getTime() === hoy.getTime();
      const seleccionado = estado.fechasSeleccionadas.has(fechaStr);

      let clases = 'cal-dia ';
      if (seleccionado) clases += 'seleccionado';
      else if (disponible) clases += 'tiene-reserva';
      else clases += 'disponible';

      html += '<div class="' + clases + (esHoy ? ' hoy' : '') + '" data-fecha="' + fechaStr + '" title="' + fechaStr + '">' + d + '</div>';
    }

    grid.innerHTML = html;
    actualizarContadorFechas();
  }

  function actualizarContadorFechas() {
    const el = $('fecha-contador');
    if (el) el.textContent = estado.fechasSeleccionadas.size + ' fechas seleccionadas';
  }

  function mesAnteriorDisp() {
    estado.mesSeleccionado = new Date(estado.mesSeleccionado.getFullYear(), estado.mesSeleccionado.getMonth() - 1, 1);
    renderCalendarioDisp();
  }

  function mesSiguienteDisp() {
    estado.mesSeleccionado = new Date(estado.mesSeleccionado.getFullYear(), estado.mesSeleccionado.getMonth() + 1, 1);
    renderCalendarioDisp();
  }

  /**
   * Crea cupos en lote: rango de fechas + dias de la semana + franja horaria.
   */
  async function generarCupos(api, mostrarToast) {
    const fecha_inicio = $('cupo-fecha-inicio').value;
    const fecha_fin = $('cupo-fecha-fin').value;
    const hora_inicio = $('cupo-hora-inicio').value;
    const hora_fin = $('cupo-hora-fin').value;
    const mesas = parseInt($('cupo-mesas').value, 10) || 1;
    const dias = Array.from(document.querySelectorAll('#cupo-dias input[type="checkbox"]:checked'))
      .map(function (el) { return parseInt(el.value, 10); });

    if (!fecha_inicio || !fecha_fin) { alerta('alerta-cupos', 'Indique el rango de fechas.'); return false; }
    if (!hora_inicio || !hora_fin) { alerta('alerta-cupos', 'Indique el horario del restaurante.'); return false; }
    if (hora_fin <= hora_inicio) { alerta('alerta-cupos', 'La hora de cierre debe ser posterior a la de apertura.'); return false; }
    if (dias.length === 0) { alerta('alerta-cupos', 'Seleccione al menos un día de la semana.'); return false; }

    const btn = $('btn-generar-cupos');
    if (btn) { btn.disabled = true; btn.textContent = 'Generando...'; }

    try {
      const r = await api.generarDisponibilidad({
        fecha_inicio: fecha_inicio,
        fecha_fin: fecha_fin,
        hora_inicio: hora_inicio,
        hora_fin: hora_fin,
        dias: dias,
        mesas_disponibles: mesas
      });

      if (r.exito) {
        const n = (r.datos && r.datos.cupos_generados) || 0;
        alerta('alerta-cupos', r.mensaje || (n + ' cupos generados.'), 'exito');
        const res = $('cupos-resultado');
        if (res) res.textContent = n + ' cupos creados entre ' + fecha_inicio + ' y ' + fecha_fin + '.';
        if (mostrarToast) mostrarToast(r.mensaje || 'Cupos generados.', 'exito');
        await cargarDisponibilidad(api);
        renderCalendarioDisp();
        return true;
      }
      alerta('alerta-cupos', r.mensaje || 'No se pudieron generar los cupos.');
      return false;
    } catch (e) {
      alerta('alerta-cupos', e.message || 'Error al generar los cupos.');
      return false;
    } finally {
      if (btn) { btn.disabled = false; btn.textContent = 'Generar cupos'; }
    }
  }

  async function eliminarCuposRango(api, mostrarToast) {
    const fecha_inicio = $('cupo-fecha-inicio').value;
    const fecha_fin = $('cupo-fecha-fin').value;
    if (!fecha_inicio || !fecha_fin) { alerta('alerta-cupos', 'Indique el rango de fechas.'); return false; }
    if (!confirm('Se eliminaran todos los cupos del rango ' + fecha_inicio + ' a ' + fecha_fin + '. Continuar?')) return false;

    try {
      const r = await api.eliminarDisponibilidadRango(fecha_inicio, fecha_fin);
      if (r.exito) {
        const n = (r.datos && r.datos.eliminados) || 0;
        alerta('alerta-cupos', r.mensaje || (n + ' cupos eliminados.'), 'exito');
        const res = $('cupos-resultado');
        if (res) res.textContent = n + ' cupos eliminados del rango.';
        if (mostrarToast) mostrarToast(r.mensaje || 'Cupos eliminados.', 'exito');
        await cargarDisponibilidad(api);
        renderCalendarioDisp();
        return true;
      }
      alerta('alerta-cupos', r.mensaje || 'No se pudieron eliminar los cupos.');
      return false;
    } catch (e) {
      alerta('alerta-cupos', e.message || 'Error al eliminar los cupos.');
      return false;
    }
  }

  /**
   * Guarda la disponibilidad de las fechas seleccionadas una por una.
   */
  async function guardarDisponibilidadSeleccionada(api, mostrarToast) {
    if (estado.fechasSeleccionadas.size === 0) {
      alerta('alerta-disp', 'Seleccione al menos una fecha.', 'error');
      return false;
    }
    const hora_inicio = $('disp-hora-inicio').value;
    const hora_fin = $('disp-hora-fin').value;
    const mesas = parseInt($('disp-mesas').value, 10) || 1;
    if (!hora_inicio || !hora_fin) { alerta('alerta-disp', 'Ingrese hora inicio y fin.', 'error'); return false; }

    try {
      for (const fecha of estado.fechasSeleccionadas) {
        await api.guardarDisponibilidad({ fecha, hora_inicio, hora_fin, mesas_disponibles: mesas });
      }
      if (mostrarToast) mostrarToast('Disponibilidad guardada para ' + estado.fechasSeleccionadas.size + ' fecha(s).', 'exito');
      estado.fechasSeleccionadas.clear();
      await cargarDisponibilidad(api);
      renderCalendarioDisp();
      return true;
    } catch (e) {
      alerta('alerta-disp', e.message || 'Error al guardar disponibilidad.', 'error');
      return false;
    }
  }

  /**
   * Inicializa las fechas por defecto del generador de cupos.
   */
  function initFechasCupos() {
    const hoy = new Date();
    const fin = new Date();
    fin.setDate(fin.getDate() + 28);
    const inpIni = $('cupo-fecha-inicio');
    const inpFin = $('cupo-fecha-fin');
    if (inpIni && !inpIni.value) inpIni.value = iso(hoy);
    if (inpFin && !inpFin.value) inpFin.value = iso(fin);
  }

  // -----------------------------------------------------------------------
  // API pública
  // -----------------------------------------------------------------------
  window.GestionPanel = {
    estado: estado,
    DIAS_SEMANA: DIAS_SEMANA,
    escapeHtml: escapeHtml,
    iso: iso,
    alerta: alerta,
    limpiarAlerta: limpiarAlerta,
    cargarProductos: cargarProductos,
    cargarStock: cargarStock,
    renderStock: renderStock,
    guardarStock: guardarStock,
    renderProductosAdmin: renderProductosAdmin,
    preencherFormularioProduto: preencherFormularioProducto,
    preencherFormularioProducto: preencherFormularioProducto,
    idProductoFormulario: idProductoFormulario,
    guardarProducto: guardarProducto,
    activarProducto: activarProducto,
    cargarDisponibilidad: cargarDisponibilidad,
    renderCalendarioDisp: renderCalendarioDisp,
    actualizarContadorFechas: actualizarContadorFechas,
    mesAnteriorDisp: mesAnteriorDisp,
    mesSiguienteDisp: mesSiguienteDisp,
    generarCupos: generarCupos,
    eliminarCuposRango: eliminarCuposRango,
    guardarDisponibilidadSeleccionada: guardarDisponibilidadSeleccionada,
    initFechasCupos: initFechasCupos
  };
})();
