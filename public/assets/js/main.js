/*
  main.js — Lógica general del frontend
  Navegación, menú móvil, inicialización común
*/

import { api } from './api.js';

document.addEventListener('DOMContentLoaded', () => {
  initNav();
  initSesion();
});

function initNav() {
  const btnNav = document.querySelector('.btn-nav');
  const navLinks = document.querySelector('.nav-links');

  // Menú móvil (toggle si existe botón hamburguesa)
  const btnMenu = document.querySelector('[data-menu-toggle]');
  if (btnMenu && navLinks) {
    btnMenu.addEventListener('click', () => {
      const abierto = navLinks.classList.toggle('abierto');
      btnMenu.setAttribute('aria-expanded', abierto);
    });
  }

  // Cerrar sesión si existe el botón
  const btnLogout = document.getElementById('btn-logout');
  if (btnLogout) {
    btnLogout.addEventListener('click', async (e) => {
      e.preventDefault();
      try {
        await api.logout();
        window.location.href = 'index.html';
      } catch (err) {
        console.error('Error cerrando sesión:', err);
        window.location.href = 'index.html';
      }
    });
  }
}

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
      // Usuario autenticado
      const rol = data.datos.id_rol;
      const esAdmin = rol === 4;
      const esEncargado = rol === 2 || rol === 3 || rol === 4;
      const nombre = data.datos.nombre;

      const chip = `
        <div class="usuario-chip">${nombre} <span style="opacity:0.8;">(${esAdmin ? 'admin' : esEncargado ? 'encargado' : 'cliente'})</span></div>
        <button id="btn-logout-nav" class="btn-sesion ghost">Cerrar sesión</button>
        <a href="panel.html" class="btn-sesion">Mi panel</a>
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
    } else {
      // No autenticado
      if (navSesion) {
        navSesion.innerHTML = `
          <a href="login.html" class="btn-sesion">Iniciar sesión</a>
        `;
      }
      if (btnLogin) btnLogin.style.display = 'inline-block';
      if (btnLogout) btnLogout.style.display = 'none';
      if (btnAdmin) btnAdmin.style.display = 'none';
    }
  } catch (err) {
    console.warn('Sesión no disponible:', err.message);
    const navSesionF = document.getElementById('nav-sesion');
    if (navSesionF) {
      navSesionF.innerHTML = `
        <a href="login.html" class="btn-sesion">Iniciar sesión</a>
      `;
    }
    if (btnLogin) btnLogin.style.display = 'inline-block';
    if (btnLogout) btnLogout.style.display = 'none';
    if (btnAdmin) btnAdmin.style.display = 'none';
  }

  const btnLogoutNav = document.getElementById('btn-logout-nav');
  if (btnLogoutNav) {
    btnLogoutNav.addEventListener('click', async (e) => {
      e.preventDefault();
      try {
        await api.logout();
      } catch (err2) { console.error('Error cerrando sesión:', err2); }
      window.location.href = 'index.html';
    });
  }
}

// Utilidad: formatear moneda
export function formatearMoneda(valor) {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(valor);
}

// Utilidad: mostrar toast/notificación temporal
export function mostrarToast(mensaje, tipo = 'exito') {
  const toast = document.createElement('div');
  toast.className = `toast toast-${tipo}`;
  toast.textContent = mensaje;
  toast.style.cssText = `
    position: fixed;
    top: 1rem;
    right: 1rem;
    padding: 1rem 1.5rem;
    border-radius: 6px;
    color: #fff;
    font-family: ${getComputedStyle(document.documentElement).fontFamily};
    z-index: 9999;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: toastSlide 0.3s ease forwards;
  `;
  if (tipo === 'exito') {
    toast.style.background = '#2D6A4F';
  } else {
    toast.style.background = '#A83636';
  }
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'toastSlideOut 0.3s ease forwards';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

// Inyectar keyframes de toast si no existen
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
