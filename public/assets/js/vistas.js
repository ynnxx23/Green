/* Lógica de vistas — Sistema de gestión de un restaurante */

const Vistas = {
  async initMesas() {
    const contenedor = document.getElementById('areas-listado');
    const alerta = document.getElementById('mesas-alerta');
    if (!contenedor) return;

    try {
      const datos = await api.get('/api/areas');
      if (!datos.exito) throw new Error(datos.mensaje);

      contenedor.innerHTML = '';
      for (const area of datos.datos) {
        const areaEl = document.createElement('div');
        areaEl.className = 'tarjeta';
        areaEl.style.marginBottom = 'var(--espacio-4)';
        areaEl.innerHTML = `
          <h2 style="font-size: var(--tamano-lg); margin-top: 0;">${escapeHtml(area.nombre)}</h2>
          <div id="mesas-area-${area.id_area}" class="fila" style="flex-wrap: wrap;"></div>
        `;
        contenedor.appendChild(areaEl);

        // Cargar mesas del área
        const datosMesas = await api.get(`/api/mesas?id_area=${area.id_area}`);
        const mesasContenedor = document.getElementById(`mesas-area-${area.id_area}`);
        if (datosMesas.exito) {
          for (const mesa of datosMesas.datos) {
            mesasContenedor.appendChild(crearTarjetaMesa(mesa));
          }
        }
      }
    } catch (error) {
      mostrarAlerta(error.message || 'Error al cargar áreas y mesas.', 'error', 'mesas-alerta');
    }
  },

  async initReservas() {
    const contenedor = document.getElementById('reservas-listado');
    const alerta = document.getElementById('reservas-alerta');
    if (!contenedor) return;

    limpiarAlerta('reservas-alerta');

    try {
      const datos = await api.get('/api/reservas/mis-reservas');
      if (!datos.exito) throw new Error(datos.mensaje);

      contenedor.innerHTML = '';
      if (datos.datos.length === 0) {
        contenedor.innerHTML = '<p style="color: var(--color-texto-secundario);">No tiene reservas.</p>';
        return;
      }

      const tabla = document.createElement('table');
      tabla.className = 'tabla';
      tabla.innerHTML = `
        <thead>
          <tr>
            <th>Mesa</th>
            <th>Fecha</th>
            <th>Hora inicio</th>
            <th>Hora fin</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      `;
      const tbody = tabla.querySelector('tbody');

      for (const reserva of datos.datos) {
        const fila = document.createElement('tr');
        fila.innerHTML = `
          <td>${escapeHtml(reserva.numero_mesa || 'N/A')}</td>
          <td>${formatearFecha(reserva.fecha_reserva)}</td>
          <td>${formatearHora(reserva.hora_inicio)}</td>
          <td>${formatearHora(reserva.hora_fin)}</td>
          <td><span class="estado-chip estado-chip--${reserva.estado === 'confirmada' ? 'libre' : reserva.estado}">${escapeHtml(reserva.estado)}</span></td>
          <td>
            ${reserva.estado === 'pendiente' || reserva.estado === 'confirmada'
              ? `<button class="btn btn--peligro btn--secundaria" style="padding: 0.375rem 0.75rem; font-size: var(--tamano-sm);" onclick="Vistas.cancelarReserva(${reserva.id_reserva})">Cancelar</button>`
              : '-'}
          </td>
        `;
        tbody.appendChild(fila);
      }

      contenedor.appendChild(tabla);
    } catch (error) {
      mostrarAlerta(error.message || 'Error al cargar reservas.', 'error', 'reservas-alerta');
    }
  },

  async cancelarReserva(idReserva) {
    if (!confirm('¿Está seguro de cancelar esta reserva?')) return;

    try {
      const datos = await api.post(`/api/reservas/${idReserva}/cancelar`);
      if (datos.exito) {
        mostrarAlerta('Reserva cancelada correctamente.', 'exito', 'reservas-alerta');
        this.initReservas();
      } else {
        mostrarAlerta(datos.mensaje || 'Error al cancelar.', 'error', 'reservas-alerta');
      }
    } catch (error) {
      mostrarAlerta(error.message || 'Error al cancelar la reserva.', 'error', 'reservas-alerta');
    }
  },

  async initPedidos() {
    const contenedor = document.getElementById('pedidos-listado');
    const alerta = document.getElementById('pedidos-alerta');
    if (!contenedor) return;

    limpiarAlerta('pedidos-alerta');

    try {
      const datos = await api.get('/api/pedidos/mis-pedidos');
      if (!datos.exito) throw new Error(datos.mensaje);

      contenedor.innerHTML = '';
      if (datos.datos.length === 0) {
        contenedor.innerHTML = '<p style="color: var(--color-texto-secundario);">No tiene pedidos.</p>';
        return;
      }

      const tabla = document.createElement('table');
      tabla.className = 'tabla';
      tabla.innerHTML = `
        <thead>
          <tr>
            <th>Mesa</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody></tbody>
      `;
      const tbody = tabla.querySelector('tbody');

      for (const pedido of datos.datos) {
        const fila = document.createElement('tr');
        fila.innerHTML = `
          <td>${escapeHtml(pedido.numero_mesa || 'N/A')}</td>
          <td>${formatearFecha(pedido.fecha_pedido)}</td>
          <td><span class="estado-chip estado-chip--${pedido.estado === 'listo' ? 'libre' : pedido.estado}">${escapeHtml(pedido.estado)}</span></td>
          <td>${formatearMoneda(pedido.total)}</td>
        `;
        tbody.appendChild(fila);
      }

      contenedor.appendChild(tabla);
    } catch (error) {
      mostrarAlerta(error.message || 'Error al cargar pedidos.', 'error', 'pedidos-alerta');
    }
  },

  async initAdmin() {
    const esAdmin = document.body.dataset.esAdmin === 'true' || await this.verificarAdmin();
    if (!esAdmin) {
      document.getElementById('vista-admin').innerHTML = '<p style="color: var(--color-error);">No tiene permisos de administrador.</p>';
      return;
    }

    await this.cargarAdminUsuarios();
    await this.cargarAdminAreas();
    await this.cargarAdminMesas();
    await this.cargarAdminProductos();
  },

  async verificarAdmin() {
    try {
      const datos = await api.get('/api/auth/sesion');
      return datos.exito && datos.datos.es_administrador;
    } catch (e) {
      return false;
    }
  },

  async cargarAdminUsuarios() {
    const contenedor = document.getElementById('admin-usuarios-listado');
    const alerta = document.getElementById('admin-usuarios-alerta');
    if (!contenedor) return;

    limpiarAlerta('admin-usuarios-alerta');

    try {
      const datos = await api.get('/api/admin/usuarios');
      if (!datos.exito) throw new Error(datos.mensaje);

      contenedor.innerHTML = '';
      const tabla = document.createElement('table');
      tabla.className = 'tabla';
      tabla.innerHTML = `
        <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th>Acciones</th></tr></thead>
        <tbody></tbody>
      `;
      const tbody = tabla.querySelector('tbody');

      for (const u of datos.datos) {
        const fila = document.createElement('tr');
        fila.innerHTML = `
          <td>${escapeHtml(u.nombre)}</td>
          <td>${escapeHtml(u.email)}</td>
          <td>${u.id_rol}</td>
          <td>${u.activo ? 'Sí' : 'No'}</td>
          <td>
            ${u.activo
              ? `<button class="btn btn--peligro btn--secundaria" style="padding: 0.25rem 0.5rem; font-size: var(--tamano-xs);" onclick="Vistas.desactivarAdminUsuario(${u.id_usuario})">Desactivar</button>`
              : '<span style="font-size: var(--tamano-xs); color: var(--color-texto-suave);">-</span>'}
          </td>
        `;
        tbody.appendChild(fila);
      }

      contenedor.appendChild(tabla);
    } catch (error) {
      mostrarAlerta(error.message || 'Error al cargar usuarios.', 'error', 'admin-usuarios-alerta');
    }
  },

  async desactivarAdminUsuario(idUsuario) {
    if (!confirm('¿Está seguro de desactivar este usuario?')) return;

    try {
      const datos = await api.delete(`/api/admin/usuarios/${idUsuario}`);
      if (datos.exito) {
        mostrarAlerta('Usuario desactivado.', 'exito', 'admin-usuarios-alerta');
        this.cargarAdminUsuarios();
      } else {
        mostrarAlerta(datos.mensaje || 'Error.', 'error', 'admin-usuarios-alerta');
      }
    } catch (error) {
      mostrarAlerta(error.message || 'Error al desactivar usuario.', 'error', 'admin-usuarios-alerta');
    }
  },

  async cargarAdminAreas() {
    const contenedor = document.getElementById('admin-areas-listado');
    const alerta = document.getElementById('admin-areas-alerta');
    if (!contenedor) return;

    limpiarAlerta('admin-areas-alerta');

    try {
      const datos = await api.get('/api/admin/areas');
      if (!datos.exito) throw new Error(datos.mensaje);

      contenedor.innerHTML = '';
      const tabla = document.createElement('table');
      tabla.className = 'tabla';
      tabla.innerHTML = `
        <thead><tr><th>Nombre</th><th>Descripción</th><th>Activo</th></tr></thead>
        <tbody></tbody>
      `;
      const tbody = tabla.querySelector('tbody');

      for (const a of datos.datos) {
        const fila = document.createElement('tr');
        fila.innerHTML = `
          <td>${escapeHtml(a.nombre)}</td>
          <td>${escapeHtml(a.descripcion || '-')}</td>
          <td>${a.activo ? 'Sí' : 'No'}</td>
        `;
        tbody.appendChild(fila);
      }

      contenedor.appendChild(tabla);
    } catch (error) {
      mostrarAlerta(error.message || 'Error al cargar áreas.', 'error', 'admin-areas-alerta');
    }
  },

  async cargarAdminMesas() {
    const contenedor = document.getElementById('admin-mesas-listado');
    const alerta = document.getElementById('admin-mesas-alerta');
    if (!contenedor) return;

    limpiarAlerta('admin-mesas-alerta');

    try {
      const datos = await api.get('/api/admin/mesas?incluir_inactivas=1');
      if (!datos.exito) throw new Error(datos.mensaje);

      contenedor.innerHTML = '';
      const tabla = document.createElement('table');
      tabla.className = 'tabla';
      tabla.innerHTML = `
        <thead><tr><th>Número</th><th>Capacidad</th><th>Activo</th></tr></thead>
        <tbody></tbody>
      `;
      const tbody = tabla.querySelector('tbody');

      for (const m of datos.datos) {
        const fila = document.createElement('tr');
        fila.innerHTML = `
          <td>${escapeHtml(m.numero_mesa)}</td>
          <td>${m.capacidad}</td>
          <td>${m.activo ? 'Sí' : 'No'}</td>
        `;
        tbody.appendChild(fila);
      }

      contenedor.appendChild(tabla);
    } catch (error) {
      mostrarAlerta(error.message || 'Error al cargar mesas.', 'error', 'admin-mesas-alerta');
    }
  },

  async cargarAdminProductos() {
    const contenedor = document.getElementById('admin-productos-listado');
    const alerta = document.getElementById('admin-productos-alerta');
    if (!contenedor) return;

    limpiarAlerta('admin-productos-alerta');

    try {
      const datos = await api.get('/api/productos');
      if (!datos.exito) throw new Error(datos.mensaje);

      contenedor.innerHTML = '';
      const tabla = document.createElement('table');
      tabla.className = 'tabla';
      tabla.innerHTML = `
        <thead><tr><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Activo</th></tr></thead>
        <tbody></tbody>
      `;
      const tbody = tabla.querySelector('tbody');

      for (const p of datos.datos) {
        const fila = document.createElement('tr');
        fila.innerHTML = `
          <td>${escapeHtml(p.nombre)}</td>
          <td>${escapeHtml(p.categoria || '-')}</td>
          <td>${formatearMoneda(p.precio)}</td>
          <td>${p.activo ? 'Sí' : 'No'}</td>
        `;
        tbody.appendChild(fila);
      }

      contenedor.appendChild(tabla);
    } catch (error) {
      mostrarAlerta(error.message || 'Error al cargar productos.', 'error', 'admin-productos-alerta');
    }
  },

  crearTarjetaMesa(mesa) {
    const estado = mesa.activo ? 'libre' : 'ocupada';
    const tarjeta = document.createElement('div');
    tarjeta.className = 'tarjeta-mesa';
    tarjeta.innerHTML = `
      <div style="font-weight: 600;">${escapeHtml(mesa.numero_mesa)}</div>
      <div style="font-size: var(--tamano-sm); color: var(--color-texto-secundario);">Capacidad: ${mesa.capacidad}</div>
      <span class="estado-chip estado-chip--${estado}">
        <span class="punto-estado punto-estado--${estado}"></span>
        ${mesa.activo ? 'Libre' : 'No disponible'}
      </span>
      ${mesa.activo
        ? `<button class="btn btn--primaria" style="width: 100%; margin-top: var(--espacio-2); font-size: var(--tamano-sm);" onclick="Vistas.reservarMesa(${mesa.id_mesa})">Reservar</button>`
        : `<button class="btn btn--secundaria" style="width: 100%; margin-top: var(--espacio-2); font-size: var(--tamano-sm);" disabled>No disponible</button>`}
    `;
    return tarjeta;
  },

  async reservarMesa(idMesa) {
    const fecha = prompt('Ingrese la fecha (YYYY-MM-DD):');
    if (!fecha) return;
    if (!/^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
      mostrarAlerta('Fecha inválida. Use YYYY-MM-DD.', 'error', 'mesas-alerta');
      return;
    }

    try {
      const datos = await api.get(`/api/mesas/disponibilidad?id_mesa=${idMesa}&fecha=${fecha}`);
      if (!datos.exito) throw new Error(datos.mensaje);

      const horarios = datos.datos.horarios_disponibles;
      if (horarios.length === 0) {
        mostrarAlerta('No hay horarios disponibles para esa fecha.', 'advertencia', 'mesas-alerta');
        return;
    }

      const opciones = horarios.map((h, i) => `${i + 1}. ${h.hora_inicio.slice(0,5)} - ${h.hora_fin.slice(0,5)}`).join('\n');
      const seleccion = prompt(`Horarios disponibles para el ${fecha}:\n${opciones}\n\nIngrese el número del horario:`);
      const idx = parseInt(seleccion, 10) - 1;

      if (isNaN(idx) || idx < 0 || idx >= horarios.length) {
        mostrarAlerta('Selección inválida.', 'error', 'mesas-alerta');
        return;
      }

      const horario = horarios[idx];

      const resultado = await api.post('/api/reservas', {
        id_mesa: idMesa,
        fecha_reserva: fecha,
        hora_inicio: horario.hora_inicio,
        hora_fin: horario.hora_fin,
      });

      if (resultado.exito) {
        mostrarAlerta('Reserva creada correctamente.', 'exito', 'mesas-alerta');
        this.initMesas();
      } else {
        mostrarAlerta(resultado.mensaje || 'Error al crear reserva.', 'error', 'mesas-alerta');
      }
    } catch (error) {
      mostrarAlerta(error.message || 'Error al reservar.', 'error', 'mesas-alerta');
    }
  },
};

window.Vistas = Vistas;
