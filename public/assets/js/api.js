/*
  api.js — Cliente fetch centralizado
  Consume la API REST del backend en public/index.php
*/

const API_BASE = 'http://localhost/Yensel/Morday/public/api';

class ApiClient {
  constructor(baseUrl = API_BASE) {
    this.baseUrl = baseUrl;
  }

  async _request(endpoint, options = {}) {
    const url = `${this.baseUrl}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      ...options.headers,
    };

    const config = {
      ...options,
      headers,
      credentials: 'include', // enviar cookies de sesión
    };

    try {
      const response = await fetch(url, config);
      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        throw new Error(data.mensaje || `Error ${response.status}`);
      }

      return data;
    } catch (error) {
      console.error('[ApiClient]', error);
      throw error;
    }
  }

  // GET
  get(endpoint) {
    return this._request(endpoint, { method: 'GET' });
  }

  // POST
  post(endpoint, body) {
    return this._request(endpoint, {
      method: 'POST',
      body: JSON.stringify(body),
    });
  }

  // PUT
  put(endpoint, body) {
    return this._request(endpoint, {
      method: 'PUT',
      body: JSON.stringify(body),
    });
  }

  // DELETE
  delete(endpoint) {
    return this._request(endpoint, { method: 'DELETE' });
  }

  // ── Endpoints del restaurante ──────────────────────────────
  areas() { return this.get('/areas'); }
  mesas(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/mesas${qs ? '?' + qs : ''}`);
  }
  productos() { return this.get('/productos'); }
  reservas(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/reservas${qs ? '?' + qs : ''}`);
  }
  crearReserva(datos) { return this.post('/reservas', datos); }
  cancelarReserva(id) { return this.delete(`/reservas/${id}`); }
  pedidos(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/pedidos${qs ? '?' + qs : ''}`);
  }
  crearPedido(datos) { return this.post('/pedidos', datos); }
  cambiarEstadoPedido(id, estado) {
    return this.put(`/pedidos/${id}/estado`, { estado });
  }
  stock() { return this.get('/admin/stock'); }
  guardarStock(datos) { return this.post('/admin/stock', datos); }
  disponibilidad(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/disponibilidad${qs ? '?' + qs : ''}`);
  }
  adminDisponibilidad(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/admin/disponibilidad${qs ? '?' + qs : ''}`);
  }
  guardarDisponibilidad(datos) { return this.post('/admin/disponibilidad', datos); }
  eliminarDisponibilidad(id) { return this.delete(`/admin/disponibilidad/${id}`); }

  // ── Menú semanal ─────────────────────────────────────────────
  menuSemanal(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/menu-semanal${qs ? '?' + qs : ''}`);
  }
  guardarMenuSemanal(datos) { return this.post('/menu-semanal', datos); }
  eliminarMenuSemanal(id) { return this.delete(`/menu-semanal/${id}`); }

  // ── Seña de pedidos ──────────────────────────────────────────
  actualizarSeñaPedido(id, estado) {
    return this.post(`/pedidos/${id}/seña`, { estado });
  }

  // ── Autenticación ──────────────────────────────────────────
  login(datos) { return this.post('/auth/login', datos); }
  registro(datos) { return this.post('/auth/registro', datos); }
  recuperar(datos) { return this.post('/auth/recuperar', datos); }
  restablecer(datos) { return this.post('/auth/restablecer', datos); }
  logout() { return this.post('/auth/logout', {}); }
  sesion() { return this.get('/auth/sesion'); }

  // ── Perfil de usuario (RF20) ───────────────────────────────
  perfil() { return this.get('/usuario/perfil'); }
  actualizarPerfil(datos) { return this.put('/usuario/perfil', datos); }

  // ── Detalle de pedido y reserva (RF21, RF22) ───────────────
  detallePedido(id) { return this.get(`/pedidos/${id}/detalle`); }
  detalleReserva(id) { return this.get(`/reservas/${id}/detalle`); }

  // ── Asignar mesero a pedido (RF26) ─────────────────────────
  asignarMesero(id, datos) { return this.put(`/pedidos/${id}/mesero`, datos); }

  // ── Stock bajo (RF25) ──────────────────────────────────────
  stockBajo(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/admin/stock/bajo${qs ? '?' + qs : ''}`);
  }

  // ── Productos: activar (RF24) ──────────────────────────────
  activarProducto(id) { return this.put(`/productos/${id}/activar`, {}); }

  // ── Reportes: exportar CSV (RF28) ──────────────────────────
  exportarReporteVentas(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/admin/reportes/exportar${qs ? '?' + qs : ''}`);
  }

  // ── Reportes ───────────────────────────────────────────────
  reportesVentas(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/admin/reportes/ventas${qs ? '?' + qs : ''}`);
  }
  reportesProductosMasVendidos(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/admin/reportes/productos-mas-vendidos${qs ? '?' + qs : ''}`);
  }
  reportesReservasPorDia(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/admin/reportes/reservas-por-dia${qs ? '?' + qs : ''}`);
  }
  reportesVentasPorDia(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/admin/reportes/ventas-por-dia${qs ? '?' + qs : ''}`);
  }
  historialMovimientos(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return this.get(`/admin/reportes/historial${qs ? '?' + qs : ''}`);
  }
  insertarHistorial(datos) { return this.post('/admin/historial', datos); }
}

export const api = new ApiClient();
