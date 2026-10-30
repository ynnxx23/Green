/*
  reservas.js — Lógica del formulario de reservas
  Conecta con POST /api/reservas
*/

import { api } from './api.js';
import { mostrarToast } from './main.js';

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-reserva');
  if (!form) return;

  const btnSubmit = form.querySelector('button[type="submit"]');
  const mensaje = document.getElementById('mensaje-estado');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!btnSubmit) return;

    const datos = {
      nombre: form.elements['nombre'].value.trim(),
      email: form.elements['email'].value.trim(),
      telefono: form.elements['telefono'].value.trim(),
      fecha: form.elements['fecha'].value,
      hora: form.elements['hora'].value,
      comensales: parseInt(form.elements['comensales'].value, 10) || 1,
      ocasion: form.elements['ocasion'].value.trim() || '',
      comentarios: form.elements['comentarios'].value.trim() || '',
    };

    // Validación básica en cliente
    if (!datos.nombre || !datos.email || !datos.fecha || !datos.hora) {
      mostrarToast('Por favor complete los campos obligatorios.', 'error');
      return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(datos.email)) {
      mostrarToast('El correo electrónico no es válido.', 'error');
      return;
    }

    // Estado de carga
    const textoOriginal = btnSubmit.textContent;
    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Enviando...';
    if (mensaje) {
      mensaje.style.display = 'none';
      mensaje.className = 'mensaje-estado';
      mensaje.textContent = '';
    }

    try {
      const respuesta = await api.crearReserva(datos);

      if (respuesta.exito) {
        if (mensaje) {
          mensaje.className = 'mensaje-estado exito';
          mensaje.textContent = 'Reserva enviada con éxito. Te confirmaremos por WhatsApp.';
          mensaje.style.display = 'block';
        }
        mostrarToast('Reserva enviada correctamente.', 'exito');
        form.reset();
      } else {
        throw new Error(respuesta.mensaje || 'No se pudo crear la reserva.');
      }
    } catch (err) {
      console.error('Error reserva:', err);
      if (mensaje) {
        mensaje.className = 'mensaje-estado error';
        mensaje.textContent = err.message || 'Error al enviar la reserva. Intente nuevamente.';
        mensaje.style.display = 'block';
      }
      mostrarToast(err.message || 'Error al enviar la reserva.', 'error');
    } finally {
      btnSubmit.disabled = false;
      btnSubmit.textContent = textoOriginal;
    }
  });
});
