=== Despacho vía Starken Pro para WooCommerce ===
Contributors: AndresReyesDev
Tags: woocommerce, shipping, chile, starken, turbus, despacho
Donate link: https://andres.reyes.dev
Requires at least: 4.5
Tested up to: 6.0
Stable tag: trunk
License: MIT License
License URI: https://opensource.org/licenses/MIT

Plugin de cálculo de despacho para WooCommerce en línea con Starken Pro. Incluye despacho a domicilio express y agencias (normal y express).

== Description ==

¿Estás actualizando desde una versión previa? Lee completo esto antes de actualizar.

La actualización 2022.05.23 es obligatoria y, si estabas registrado previamente, debes volver a registrarte. Sin ella no podrán realizar cálculos de despacho.

Es importante entender que desde la versión 2022.05.23 es requerida una API gratis desde https://www.anyda.xyz que sea válida con el dominio. Si esta no está creada no funcionará la aplicación.

Esta API Key permite que el dominio pueda realizar un máximo de 500 cotizaciones (cálculos de despacho) exitosos. Si necesitas más de 500 cotizaciones puede pasar a la versión Premium (e ilimitada) del plugin en www.anyda.xyz.

Realiza cálculo de despacho en línea (valor real entregado por Starken) basándose en el tamaño y peso del envío.

El plugin funciona de manera nativa con las Regiones de Chile (States) incluidas desde la versión 6.0 de WooCommerce. Estas son normadas bajo ISO 3166-2:CL.

Es muy probable que si usas algún plugin para modificar el checkout (como el de MkRapel, MasterBip o inclusive uno mio) no funcionará el plugin de Starken ¿la razón? La mencionada normal ISO y el hecho que Starken requiere que sus comunas tengan un formato y nombre específico.

Por otra parte, como cada Courier usa el nombre que le parece adecuado para llamar a sus comunas, ciudades o troncales, es evidente que este plugin no funcionará con otros como el de Chilexpress.

Finalmente, en un par de versiones más añadiré la posibilidad de emisión de etiquetas y seguimiento de envíos en la misma plataforma.

¿Dudas? ¿Consultas? ¿Mejoras? ¿Comentarios? ¡Házmelo saber! Envíame un correo a andres(a)reyes.dev

== Installation ==

Instalar plugin e ingresar a https://www.anyda.xyz para generar una API Gratis (para prevenir abusos). 

Posterior configurar en el apartado WooCommerce -> Ajustes -> Envío -> Starken.

Dentro seleccione el lugar de Origen (la sucursal más cercana que servirá de base para los cálculos).

OPCIONES DEL PLUGIN

* Activo: Sirve para activar y desactivar el plugin (... duh?... )
* Título: El nombre con el cual se mostrará el despacho visible al cliente (cuando calcula y paga)
* API: La API generada para el dominio en https://www.anyda.xyz
* Sucursal Origen: Base para el cálculo de despacho.

Todos las opciones son obligatorias.

== Frequently Asked Questions ==
= ¿No calcula bien? =

La API no está correctamente configurada, los productos no tienen peso y/o tamaño correcto.

= ¿La API es gratis? =

Si, es gratis hasta 500 cotizaciones mensuales. Esto, según el registro del servicio, servirá para el 90% de los sitios que actualmente usan la aplicación. El restante 10% puede pasar a la versión Premium (e ilimitada) del plugin en www.anyda.xyz.

= ¿Se asegura el funcionamiento? =

Este plugin depende de, entre otras cosas, los sistemas de Starken. Si estos están funcionando todos somos felices. Dicho esto no podemos dar garantía de funcionalidad 100%, pero si haremos nuestro mejor esfuerzo por hacer funcionar la aplicación.

== Screenshots ==
1. Pantalla de Administración
2. Cálculo de Despacho

== Changelog ==

= 2022.05.28 =

Se agrega funcionalidad de despacho mínimo
Mejoras de código
Añade opción de logo en finalizar compra
Ordenan los assets

= 2022.05.26 =

Se agrega notice al Dashboard de WordPress con instrucciones para obtener API.
Se agrega indicación sobre despacho desde provincia de Santiago (incluido Puente Alto y San Bernando) ya que todos estos lugares deben seleccionar SANTIAGO como origen.

= 2022.05.24 =

Se corrigen algunos errores de la nueva versión.
Actualizado a la versión de API de Starken Pro.
Incluye despacho a domicilio express y agencias (normal y express).
Incluye regiones y comunas.
Filtra las comunas basado en la región seleccionada.

= 2022.05.23 =

Se actualiza el plugin para la nueva API de Starken Pro.

= 2020.12.26 =

Se mejora el sistema de cache de la API para evitar realizar la misma consulta una y otra vez al servidor. Esto mejora significativamente los tiempos de respuesta.

= 2.2.0 =

Se eliminan referencias de código externo.

= 2.0.1 = 

Mejoras en Cálculo de Despacho cortesía de @melvisnap

= 2.1.0 =

Optimización de código

= 2.0.0 = 

Se cambia la manera de conectar a la API, se corrigen errores y fallas. Se hace obligatorio el uso de la API gratis que sea válida para el dominio.

= 1.2.0 =

* Nuevo: Se deja la opción de asignar tamaño y peso por defecto para los productos que no lo tengan. Esto permite que el usuario siempre muestre el despacho independiente si los productos tienen dimensiones y peso. Por defecto viene con un tamaño de 25cm x 25cm x 25cm y 500grs de peso. Es importante que modifiquen estos parámetros o los desactiven.
* Nuevo: Opción de redondear el despacho (si sale $2345 puedes dejarlo en $2400, o $3000).
* Mejoras de código cortesía de @neoixan
* Se realizan mejoras en el código.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==
Nada que hacer aún...