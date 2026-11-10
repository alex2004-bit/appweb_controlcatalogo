# Prompt para opencode — Vista de Detalle de Producto

## Contexto
Basado en la vista de producto de Digicorp Bolivia (digicorp.com.bo), 
producto: Grabador Cooper 4CH Wizsense (Dahua).

---

## Prompt

Quiero que crees la vista de detalle de producto (Product Detail Page) de un e-commerce,
con el siguiente layout y comportamiento:

### LAYOUT GENERAL (2 columnas en desktop, apiladas en mobile)

#### Columna izquierda: Galería de imágenes
- Imagen principal grande centrada, fondo blanco.
- Flechas de navegación (`<` `>`) a los costados de la imagen principal para pasar
  entre fotos del producto.
- Debajo, un carrusel de miniaturas (thumbnails) dentro de un contenedor con borde:
  - Cada miniatura es un cuadro pequeño con la foto del producto.
  - La miniatura seleccionada tiene un borde resaltado (color verde/lima).
  - Al hacer click en una miniatura, cambia la imagen principal.

#### Columna derecha: Información del producto
1. Nombre del producto (título en mayúsculas, tipografía grande y en negrita).
2. Bloque de identificadores:
   - "Código:" + valor (código interno/SKU del fabricante).
   - "SKU:" + valor (código interno de la tienda).
3. Logo de la marca del producto (ej. Dahua), como imagen clickeable.
4. Sección "Descripción del Producto":
   - Título de sección.
   - Línea divisoria debajo del título.
   - Párrafo de texto con la descripción técnica/comercial del producto.
5. Bloque de disponibilidad (**condicional según stock**):

   **CASO A — Sin stock:**
   - Ícono de camión/entrega + texto "Sin fecha de llegada"
     con nota pequeña "* Sujeto a modificaciones".
   - Botón pill de color verde/lima a la derecha: "🔔 Avisarme"
     (suscribe al usuario para notificarle cuando vuelva el stock).

   **CASO B — Con stock:**
   - Mostrar el precio del producto (número grande, moneda local).
   - Selector de cantidad (input numérico con botones +/-).
   - Botón principal ancho de color azul/violeta: "Agregar al carrito".

6. Debajo de la disponibilidad, un botón/banner ancho de color azul/violeta
   que ocupa todo el ancho de la columna:
   - Si el usuario **NO está logueado**: "Inicia sesión para ver disponibilidad y precio"
     (redirige a login).
   - Si el usuario **SÍ está logueado**: en este mismo espacio se debe mostrar
     el precio y el botón "Agregar al carrito" (según CASO B arriba).

### SECCIÓN INFERIOR (ancho completo, debajo de las 2 columnas)

7. "Características":
   - Título de sección con línea divisoria debajo.
   - Lista de características en formato bullet points (texto descriptivo,
     algunos en cursiva para destacar puntos clave).
   - Grid de íconos de especificaciones técnicas: cada ícono es un cuadro
     redondeado con un pictograma arriba y una etiqueta corta abajo
     (ej: "Two-way talk", "SMD Plus", "1080p HDMI/VGA"). Se acomodan en fila,
     con wrap a nueva fila si no entran.

8. "Productos Relacionados":
   - Título de sección.
   - Carrusel/grid horizontal de tarjetas de productos similares,
     cada una con imagen del producto (mismo estilo que la galería principal).

### ELEMENTOS FLOTANTES (fixed, siempre visibles)

9. Widget de chat flotante en la esquina inferior derecha:
   - Botón circular verde con ícono de chat.
   - Al lado, un globo/tooltip con logo de la empresa, nombre y mensaje
     de bienvenida tipo "Hola! Estamos para servirle. En caso de tener
     alguna consulta, no dudes en escribirnos."
   - Botón (x) para cerrar el tooltip.

10. Botón flotante "scroll to top" (flecha hacia arriba, circular)
    en la esquina inferior derecha, sobre el widget de chat.

### Comportamiento al "Agregar al carrito"

- Al hacer click en "Agregar al carrito": mostrar una notificación/toast
  o un mini-drawer lateral confirmando que el producto se agregó,
  con miniatura del producto, nombre, cantidad y subtotal.
- Actualizar el ícono del carrito (en el header) con el contador de ítems.

### Paleta de colores
- Blanco de fondo.
- Azul/violeta para botones primarios y CTAs de login/carrito.
- Verde/lima para acciones de disponibilidad (avisarme) y acentos.
- Gris oscuro para textos.

---

## Pendiente / a definir
> ⚠️ Falta aclarar: ¿la vista de producto cambia de diseño según el **tipo de producto**
> que se cargue (ej. una cámara vs. un cable tienen distinta cantidad de specs/íconos),
> o te referís al **formulario de admin** donde se carga un producto nuevo?
> Definir esto antes de implementar, ya que cambia bastante el alcance del prompt.
