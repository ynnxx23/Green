/*
  main.js — Lógica general del frontend
  Navegación, menú móvil, inicialización común, utilidades HCI
*/

import { api } from './api.js';

document.addEventListener('DOMContentLoaded', () => {
  initNav();
  initSesion();
});

/* ══════════════════════════════════════════════════════════════════════
   NAVEGACIÓN
   ═════════════════════════════════════════════════════════════════════ */
function initNav() {
  const btnMenu = document.querySelector('[data-menu-toggle]');
  const navLinks = document.querySelector('.nav-links');

  if (btnMenu && navLinks) {
    btnMenu.addEventListener('click', () => {
      const abierto = navLinks.classList.toggle('abierto');
      btnMenu.setAttribute('aria-expanded', abierto);
    });
  }

  const btnLogout = document.getElementById('btn-logout');
  if (btnLogout) {
    btnLogout.addEventListener('click', async (e) => {
      e.preventDefault();
      try {
        await api.logout();
        mostrarToast('Sesión cerrada.', 'exito');
        setTimeout(() => { window.location.href = 'index.html'; }, 600);
      } catch (err) {
        console.error('Error cerrando sesión:', err);
        window.location.href = 'index.html';
      }
    });
  }
}

/* ══════════════════════════════════════════════════════════════════════
   SESIÓN
   ═════════════════════════════════════════════════════════════════════ */
async function initSesion() {
  const sesionContainer = document.getElementById('sesion-info');
  const btnLogin = document.getElementById('btn-login');
  const btnLogout = document.getElementById('btn-logout');
  const btnAdmin = document.getElementById('btn-admin');
  const navSesion = document.getElementById('nav-sesion');

  if (!sesionContainer && !btnLogin && !btnLogout && !btnAdmin && !navSesion) return;

  try {
    const data = await api.sesion();
    if (data.exito) {
      const rol = data.datos.id_rol;
      const esAdmin = rol === 4;
      const esEncargado = rol === 2 || rol === 4;
      const nombre = data.datos.nombre;

      const chip = `
        <div class="usuario-chip">${nombre} <span style="opacity:0.8;">(${esAdmin ? 'admin' : esEncargado ? 'encargado' : 'cliente'})</span></div>
        <button id="btn-logout-nav" class="btn-sesion ghost" aria-label="Cerrar sesión">Cerrar sesión</button>
        ${esAdmin ? `<a href="panel-admin.html" class="btn-sesion">Panel Admin</a>` : `<a href="${esEncargado ? 'panel-encargado.html' : 'panel-cliente.html'}" class="btn-sesion">Mi panel</a>`}
      `;

      if (sesionContainer) {
        sesionContainer.innerHTML = `
          <span>Hola, <strong>${nombre}</strong></span>
          <span>(${data.datos.email})</span>
        `;
      }
      if (btnLogin) btnLogin.style.display = 'none';
      if (btnLogout) btnLogout.style.display = 'inline-block';
      if (btnAdmin && esAdmin) btnAdmin.style.display = 'inline-block';
      if (navSesion) navSesion.innerHTML = chip;

      const btnLogoutNav = document.getElementById('btn-logout-nav');
      if (btnLogoutNav) {
        btnLogoutNav.addEventListener('click', async (e) => {
          e.preventDefault();
          try {
            await api.logout();
            mostrarToast('Sesión cerrada.', 'exito');
            setTimeout(() => { window.location.href = 'index.html'; }, 600);
          } catch (err2) { console.error('Error cerrando sesión:', err2); }
        });
      }
    } else {
      if (navSesion) {
        navSesion.innerHTML = `<a href="login.html" class="btn-sesion">Iniciar sesión</a>`;
      }
      if (btnLogin) btnLogin.style.display = 'inline-block';
      if (btnLogout) btnLogout.style.display = 'none';
      if (btnAdmin) btnAdmin.style.display = 'none';
    }
  } catch (err) {
    console.warn('Sesión no disponible:', err.message);
    const navSesionF = document.getElementById('nav-sesion');
    if (navSesionF) {
      navSesionF.innerHTML = `<a href="login.html" class="btn-sesion">Iniciar sesión</a>`;
    }
    if (btnLogin) btnLogin.style.display = 'inline-block';
    if (btnLogout) btnLogout.style.display = 'none';
    if (btnAdmin) btnAdmin.style.display = 'none';
  }
}

/* ══════════════════════════════════════════════════════════════════════
   UTILIDADES HCI
   ═════════════════════════════════════════════════════════════════════ */

export function formatearMoneda(valor) {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(valor || 0);
}

export function mostrarToast(mensaje, tipo = 'exito') {
  const toast = document.createElement('div');
  toast.className = `toast toast-${tipo}`;
  toast.textContent = mensaje;
  toast.style.cssText = `
    position: fixed;
    top: 1rem;
    right: 1rem;
    padding: 1rem 1.5rem;
    border-radius: var(--radius-md);
    color: #fff;
    font-family: var(--font-body);
    font-size: 0.9rem;
    font-weight: 600;
    z-index: 9999;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: toastSlide 0.3s ease forwards;
    max-width: 300px;
  `;
  toast.style.background = tipo === 'exito' ? '#2D6A4F' : '#A83636';
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'toastSlideOut 0.3s ease forwards';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

if (!document.getElementById('toast-keyframes')) {
  const style = document.createElement('style');
  style.id = 'toast-keyframes';
  style.textContent = `
    @keyframes toastSlide {
      from { transform: translateX(120%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes toastSlideOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(120%); opacity: 0; }
    }
  `;
  document.head.appendChild(style);
}

/* Mostrar alerta dentro de un contenedor específico */
export function mostrarAlerta(id, texto, tipo = 'error') {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = texto;
  el.className = `alerta alerta--${tipo}`;
  el.style.display = 'block';
  setTimeout(() => { el.style.display = 'none'; }, 5000);
}

/* Botón con estado de carga */
export function setBtnLoading(btn, cargando) {
  if (!btn) return;
  if (cargando) {
    btn.disabled = true;
    btn.dataset.textoOriginal = btn.textContent;
    btn.textContent = 'Procesando...';
    btn.style.opacity = '0.7';
  } else {
    btn.disabled = false;
    btn.textContent = btn.dataset.textoOriginal || btn.textContent;
    btn.style.opacity = '1';
  }
}

/* Confirmación antes de acción destructiva */
export function confirmar(mensaje = '¿Está seguro de realizar esta acción?') {
  return confirm(mensaje);
}

/* Formatear fecha legible */
export function formatearFecha(fecha) {
  if (!fecha) return '-';
  return new Date(fecha).toLocaleDateString('es-CO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
}

/* Formatear hora */
export function formatearHora(hora) {
  if (!hora) return '-';
  return String(hora).substring(0, 5);
}
