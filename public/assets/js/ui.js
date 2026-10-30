/* Utilidades de interfaz — Sistema de gestión de un restaurante */

function mostrarAlerta(mensaje, tipo = 'error', contenedorId = 'alerta-global') {
  const contenedor = document.getElementById(contenedorId);
  if (!contenedor) return;

  contenedor.innerHTML = `
    <div class="alerta alerta--${tipo}" role="alert">
      ${escapeHtml(mensaje)}
    </div>
  `;

  setTimeout(() => {
    contenedor.innerHTML = '';
  }, 5000);
}

function limpiarAlerta(contenedorId = 'alerta-global') {
  const contenedor = document.getElementById(contenedorId);
  if (contenedor) contenedor.innerHTML = '';
}

function escapeHtml(texto) {
  const div = document.createElement('div');
  div.textContent = texto;
  return div.innerHTML;
}

function formatearFecha(fecha) {
  return new Date(fecha).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
}

function formatearHora(hora) {
  return hora;
}

function formatearMoneda(valor) {
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'ARS',
  }).format(valor);
}

window.mostrarAlerta = mostrarAlerta;
window.limpiarAlerta = limpiarAlerta;
window.escapeHtml = escapeHtml;
window.formatearFecha = formatearFecha;
window.formatearHora = formatearHora;
window.formatearMoneda = formatearMoneda;
