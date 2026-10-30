/*
  auth.js — Lógica de autenticación (login / registro)
  Conecta con POST /api/auth/login y POST /api/auth/registro
*/

import { api } from './api.js';
import { mostrarToast } from './main.js';

document.addEventListener('DOMContentLoaded', () => {
  const formLogin = document.getElementById('form-login');
  const formRegistro = document.getElementById('form-registro');

  if (formLogin) initLogin(formLogin);
  if (formRegistro) initRegistro(formRegistro);
});

function initLogin(form) {
  const btn = form.querySelector('button[type="submit"]');
  const mensaje = document.getElementById('mensaje-estado');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const datos = {
      email: form.elements['email'].value.trim(),
      contrasena: form.elements['contrasena'].value,
    };

    if (!datos.email || !datos.contrasena) {
      mostrarToast('Correo y contraseña son obligatorios.', 'error');
      return;
    }

    const textoOriginal = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Iniciando...';
    if (mensaje) { mensaje.style.display = 'none'; mensaje.textContent = ''; }

    try {
      const respuesta = await api.login(datos);
      if (respuesta.exito) {
        mostrarToast('Inicio de sesión exitoso.', 'exito');
        setTimeout(() => { window.location.href = 'panel.html'; }, 800);
      } else {
        throw new Error(respuesta.mensaje || 'Credenciales inválidas.');
      }
    } catch (err) {
      console.error('Error login:', err);
      if (mensaje) {
        mensaje.className = 'mensaje-estado error';
        mensaje.textContent = err.message || 'Error al iniciar sesión.';
        mensaje.style.display = 'block';
      }
      mostrarToast(err.message || 'Error al iniciar sesión.', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = textoOriginal;
    }
  });
}

function initRegistro(form) {
  const btn = form.querySelector('button[type="submit"]');
  const mensaje = document.getElementById('mensaje-estado');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const datos = {
      nombre: form.elements['nombre'].value.trim(),
      email: form.elements['email'].value.trim(),
      contrasena: form.elements['contrasena'].value,
      id_rol: parseInt(form.elements['id_rol'].value, 10) || 1,
    };

    if (!datos.nombre || !datos.email || !datos.contrasena) {
      mostrarToast('Todos los campos son obligatorios.', 'error');
      return;
    }

    if (datos.contrasena.length < 8) {
      mostrarToast('La contraseña debe tener al menos 8 caracteres.', 'error');
      return;
    }

    const textoOriginal = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Registrando...';
    if (mensaje) { mensaje.style.display = 'none'; mensaje.textContent = ''; }

    try {
      const respuesta = await api.registro(datos);
      if (respuesta.exito) {
        mostrarToast('Usuario registrado correctamente. Inicie sesión.', 'exito');
        setTimeout(() => { window.location.href = 'login.html'; }, 1000);
      } else {
        throw new Error(respuesta.mensaje || 'No se pudo registrar.');
      }
    } catch (err) {
      console.error('Error registro:', err);
      if (mensaje) {
        mensaje.className = 'mensaje-estado error';
        mensaje.textContent = err.message || 'Error al registrar usuario.';
        mensaje.style.display = 'block';
      }
      mostrarToast(err.message || 'Error al registrar usuario.', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = textoOriginal;
    }
  });
}
