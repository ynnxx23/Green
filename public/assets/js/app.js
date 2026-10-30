/* Enrutador de vistas — Sistema de gestión de un restaurante */

const Rutas = {
  '/': 'vista-inicio',
  '/login': 'vista-login',
  '/registro': 'vista-registro',
  '/mesas': 'vista-mesas',
  '/reservas': 'vista-reservas',
  '/pedidos': 'vista-pedidos',
  '/admin': 'vista-admin',
};

function navegar(ruta) {
  history.pushState(null, '', ruta);
  renderizarVista(ruta);
}

function renderizarVista(ruta) {
  const idVista = Rutas[ruta] || 'vista-inicio';

  document.querySelectorAll('[data-vista]').forEach((el) => {
    el.classList.add('oculto');
  });

  const vista = document.getElementById(idVista);
  if (vista) {
    vista.classList.remove('oculto');
  }

  document.title = 'Restaurante - ' + (idVista.replace('vista-', '').charAt(0).toUpperCase() + idVista.replace('vista-', '').slice(1));
}

window.addEventListener('popstate', () => {
  renderizarVista(window.location.pathname);
});

window.navegar = navegar;
window.renderizarVista = renderizarVista;
