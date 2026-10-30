# Diseño conceptual — Green en la Casa Blanca

Restaurante elegante, moderno con estilo neoclásico. Primera presencia web.

---

## 1. Investigación: ¿cómo están diseñadas las páginas de restaurantes conocidos?

### Estructura estándar (secciones obligatorias)

Según el análisis de múltiples ejemplos (Block Agency 2026, CreateToday 2026, Pantastic 2025, Wix, Lovable, InstantCafeSite), una web de restaurante exitoso incluye siempre:

| Sección | Propósito | Elementos clave |
|---------|-----------|-----------------|
| **Hero** | Causar impacto inmediato, vender la experiencia | Imagen de fondo full-bleed (plato o interior), titular corto, CTA "Reservar mesa" |
| **Sobre nosotros / Historia** | Conectar emocionalmente, contar el concepto | Texto breve, valores, equipo, fotos del local |
| **Menú / Carta** | Mostrar oferta gastronómica | Categorías (entradas, principales, postres), fotos de platos, precios, descripciones, alérgenos |
| **Reservas** | Convertir visita en reserva | Formulario integrado (fecha, hora, comensales), botón CTA visible en todo el sitio |
| **Galería** | Generar deseo visual | Fotos de alta calidad de platos, interior, barra, eventos |
| **Contacto** | Ubicación y formas de llegar | Dirección, mapa, teléfono, WhatsApp, email, horario de cocina y de atención |
| **Footer** | Información legal y redes | Logo, redes sociales, horarios, derechos reservados |

### Tendencias de diseño 2026 observadas

- **Fotografía full-bleed de alta calidad**: el food styling es el protagonista; imágenes oscuras con luz dirigida hacen que los platos "resplandezcan".
- **Tipografía mixta**: serif elegante para encabezados (transmite lujo/tradición) + sans-serif limpia para cuerpo (legibilidad móvil).
- **Colores oscuros o neutros con acento cálido**: negro, carbón, crema, verde profundo, dorado, ámbar. Se siente "caro" sin ser frío.
- **Navegación fija (sticky)**: el botón "Reservar" siempre visible.
- **Dual-logo**: logo completo + monograma/reducido para móvil y footer.
- **Espacio en blanco generoso**: evita saturación, transmite exclusividad.
- **Responsive obligatorio**: +70% de las visitas vienen de móvil.
- **Microinteracciones**: hover suave en botones, carousel de fotos, desplegables de menú por categorías.

### Referencias directas encontradas

| Referencia | Estilo | Lo que se aplica aquí |
|------------|--------|----------------------|
| Amalur Ibiza | Haute cuisine, elegante finca | Narrativa visual, bar y cócteles, menú creativo |
| Blauet (Innobing) | Elegante, sofisticado | Tipografía serif ornamentada, dorado oscuro, sobriedad |
| Kadon Cuenca | Fusión tradición-modernidad | Navegación clara: Inicio, Acerca, Carta, Reservar, Contacto |
| Sectra / Lovable | Fine dining moderno | Serif para encabezados, paleta neutra, tres columnas, badges dietéticos |
| InstantCafeSite "Fine Dining" | Tema oscuro upscale | Colores profundos, sensación cara sin ser fría |

---

## 2. Identidad visual propuesta: Green en la Casa Blanca

### Concepto
"Green en la Casa Blanca" = elegancia neoclásica (estructura, simetría, molduras, columnas implícitas) + modernidad (líneas limpias, tipografía contemporánea, fotografía editorial) + verde como identidad de marca (frescura, naturaleza, distinción).

### Paleta de colores

| Rol | Color | Uso | Sensación |
|-----|-------|-----|-----------|
| Primario | Verde bosque profundo `#1B4332` | Fondos principales, encabezados, botones primarios | Naturaleza, elegancia, serenidad |
| Secundario | Verde salvia suave `#2D6A4F` | Secundarios, bordes, acentos sutiles | Frescura, modernidad |
| Acento neoclásico | Dorado apagado `#B8860B` o `#D4AF37` | Líneas divisorias, iconos, detalles tipográficos | Lujo, tradición, clase |
| Fondo | Crema cálido `#F5F5DC` o `#FAFAF0` | Fondos de secciones, tarjetas | Limpieza, calidez, neoclasicismo |
| Texto principal | Carbón suave `#2C2C2C` | Cuerpo de texto | Legibilidad, seriedad |
| Texto claro | Blanco `#FFFFFF` | Texto sobre fondos oscuros | Contraste, modernidad |

### Tipografía

| Uso | Fuente | Estilo | Por qué |
|-----|--------|--------|---------|
| Encabezados (h1-h3) | Playfair Display o Cinzel | Serif, elegante | Evoca neoclasicismo, lujo, imprenta tradicional |
| Cuerpo de texto | Lato o Montserrat | Sans-serif, limpio | Legibilidad en móvil, modernidad, contraste con serif |
| Logo / Monograma | Cinzel Decorative o Trajan Pro | Serif mayúsculas, espaciado amplio | Identidad de marca fuerte, arquitectura neoclásica |

### Componentes visuales

- **Líneas divisorias doradas**: separan secciones, recuerdan molduras neoclásicas.
- **Tarjetas con sombra suave y borde fino**: menú, platos, equipos.
- **Botones**: verde bosque con texto blanco, hover dorado; o blanco con borde verde y texto verde.
- **Imágenes**: estilo editorial, luz dirigida sobre platos, interiores con maderas y plantas (green), simetría en composiciones.
- **Iconografía**: fina, lineal, dorada o verde (teléfono, ubicación, reloj, whatsapp).

---

## 3. Estructura de páginas (sitemap)

```
Green en la Casa Blanca
├── Inicio (/)
│   ├── Hero (imagen principal + CTA reservar)
│   ├── Nosotros (historia, concepto neoclásico-moderno)
│   ├── Carta destacada (platos estrella con fotos)
│   ├── Disponibilidad / reservas (formulario rápido)
│   └── Galería (interior, platos, barra)
├── Carta / Menú (/carta)
│   ├── Entradas
│   ├── Platos principales
│   ├── Postres
│   ├── Bebidas / coctelería
│   └── Filtros y badges (vegetariano, sin gluten, recomendado)
├── Reservas (/reservas)
│   ├── Formulario completo (fecha, hora, comensales, ocasión)
│   ├── Confirmación y contacto directo (WhatsApp/teléfono)
│   └── Política de cancelación
├── Nosotros (/nosotros)
│   ├── Historia del restaurante
│   ├── El equipo (chef, sala)
│   ├── Filosofía (producto local, green)
│   └── Espacios (comedor, barra, salón privado)
├── Contacto (/contacto)
│   ├── Dirección con mapa
│   ├── Horarios (cocina y atención)
│   ├── Teléfono, WhatsApp, email
│   └── Formulario de contacto
├── Privado (backend API existente)
│   ├── Login / Registro
│   ├── Mis reservas
│   └── Panel admin (usuarios, mesas, productos, pedidos)
└── Error 404
```

---

## 4. Detalle de la página de inicio (hero y secciones)

### Hero (pantalla completa)
- Fondo: imagen de alta calidad del comedor o un plato signature con tono verde/dorado.
- Overlay oscuro semi-transparente para legibilidad.
- Título: "Green en la Casa Blanca" (fuente Cinzel/Playfair, blanco, grande).
- Subtítulo: "Elegancia neoclásica. Sabor moderno." (Lato, crema).
- CTA principal: "Reservar mesa" (botón verde bosque, blanco texto).
- CTA secundario: "Ver carta" (botón transparente, borde dorado).
- Monograma pequeño arriba a la izquierda.

### Sección "Nosotros" (inicio)
- Dos columnas: texto a la izquierda, imagen a la derecha (o viceversa, alternando).
- Título: "Una casa para el buen gusto."
- Texto corto (2-3 párrafos) sobre el concepto: neoclásico en la forma, moderno en la cocina.
- Badges: "Cocina de autor", "Productos de temporada", "Atención personalizada".

### Sección "Carta destacada"
- 3-4 platos estrella en tarjetas horizontales.
- Cada tarjeta: foto del plato, nombre, descripción breve, precio, badge "Recomendado".
- Enlace: "Ver carta completa".

### Sección "Reservas rápidas"
- Formulario mínimo: fecha, hora, comensales, botón "Reservar".
- Texto de confianza: "Confirmación inmediata" o "Llama al XXXX".

### Footer
- Logo blanco o dorado sobre fondo verde bosque.
- Enlaces: Carta, Reservas, Nosotros, Contacto.
- Redes sociales: Instagram, Facebook (iconos dorados o blancos).
- Dirección, teléfono, WhatsApp.
- Derechos reservados.

---

## 5. Página de Carta / Menú

- Filtros por categoría (píldoras: Entradas, Principales, Postres, Bebidas).
- Vista en tarjetas (desktop: 3 columnas; móvil: 1 columna).
- Cada plato: foto, nombre, descripción, precio, ingredientes clave, badges dietéticos.
- Botón "Añadir a reserva" o simplemente visual (según alcance del MVP).
- Diseño aireado, mucho espacio entre tarjetas.

---

## 6. Página de Reservas

- Formulario centrado, tarjeta blanca sobre fondo verde suave.
- Campos: nombre, email, teléfono, fecha, hora, número de comensales, ocasión especial, comentarios.
- Botón: "Enviar reserva" (verde bosque).
- Mensaje de éxito: "Reserva enviada. Te confirmaremos por WhatsApp."
- Teléfono y WhatsApp visibles como alternativa directa.

---

## 7. Integración con el backend existente

La API ya está en `http://localhost/Yensel/Morday/public/` con estos endpoints disponibles:

| Frontend necesita | Endpoint backend | Estado |
|-------------------|------------------|--------|
| Ver disponibilidad de mesas | `GET /api/mesas` | ✅ Listo |
| Crear reserva | `POST /api/reservas` | ✅ Listo (requiere auth) |
| Ver carta de productos | `GET /api/productos` | ✅ Listo |
| Login de usuario | `POST /api/auth/login` | ✅ Listo |
| Ver reservas propias | `GET /api/reservas` | ✅ Listo (requiere auth) |

**Nota:** Para el MVP inicial, el frontend puede ser principalmente informativo (carta, contacto, reservas vía formulario que envía email/WhatsApp) y conectar luego a la API de reservas cuando haya autenticación de cliente implementada en el front.

---

## 8. Estilos CSS sugeridos (tokens)

```css
/* Colores */
--color-primary: #1B4332;      /* Verde bosque */
--color-secondary: #2D6A4F;    /* Verde salvia */
--color-accent: #B8860B;       /* Dorado neoclásico */
--color-bg: #F5F5DC;           /* Crema cálido */
--color-text: #2C2C2C;         /* Carbón */
--color-text-light: #FFFFFF;   /* Blanco */

/* Tipografía */
--font-heading: 'Cinzel', serif;
--font-body: 'Lato', sans-serif;

/* Espaciado */
--space-xs: 0.5rem;
--space-sm: 1rem;
--space-md: 2rem;
--space-lg: 4rem;

/* Componentes */
--btn-primary: var(--color-primary);
--btn-primary-hover: var(--color-accent);
--border-radius: 4px;
--shadow: 0 4px 20px rgba(0,0,0,0.08);
```

---

## 9. Próximos pasos para implementar el frontend

1. Crear carpeta `public/assets/css/` y `public/assets/js/`.
2. Crear `public/index.html` (página de entrada con hero + reservas rápidas).
3. Crear `public/carta.html`, `public/reservas.html`, `public/nosotros.html`, `public/contacto.html`.
4. Conectar formulario de reservas al backend (`POST /api/reservas`) una vez que haya login de cliente.
5. Probar en móvil y escritorio (responsive).
6. Subir a producción cuando el backend esté validado.

---

*Documento basado en investigación de restaurantes referentes (2025-2026) y adaptado al concepto "Green en la Casa Blanca": elegante, moderno, estilo neoclásico.*
