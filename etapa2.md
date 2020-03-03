Segunda etapa de implementación

Tiempo requerido: 1-2 hs
Requerimientos: acceso a la base de datos y acceso al servidor donde esté corriendo la web de producción.

Actualizar plugins
Los archivos que se actualizaron fueron los siguientes:
~/siu/www/js/fullcalendar.js
~/siu/www/css/fullcalendar.css
~/siu/www/js/lib/jquery-1.12.4.min.js
~/siu/www/js/jquery.timepicker.js
~/siu\/ww/css/jquery.timepicker.css

Para copiarlos usar estas líneas de código de ejemplo (desde el servidor de producción):

scp root@velben:/var/guarani3w/src/siu/www/js/lib/jquery-1.12.4.min.js /var/guarani3w/src/siu/www/js/lib/
scp root@velben:/var/guarani3w/src/siu/www/js/fullcalendar.js /var/guarani3w/src/siu/www/js/
scp root@velben:/var/guarani3w/src/siu/www/js/jquery.timepicker.js /var/guarani3w/src/siu/www/js/
scp root@velben:/var/guarani3w/src/siu/www/css/fullcalendar.css /var/guarani3w/src/siu/www/css/
scp root@velben:/var/guarani3w/src/siu/www/css/jquery.timepicker.css /var/guarani3w/src/siu/www/css/

Modificar los siguientes archivos: 
~/siu/operaciones/_comun/templates/res.twig (línea 1)
{% set js_jquery = url_recursos ~ 'js/lib/jquery-1.12.4.min.js' %}

Luego, compilar los recursos: 
cd /var/guarani3w/bin
./guarani compilar_recursos

Modificar funcionalidad del envío de mail 
Modificar el siguiente archivo: 
~/src/siu/lib/kernel/util/mail.php (línea 48)
       //Iris: Que controle que reply_to no haya sido asignado
       if (! isset($this->reply_to)) {
           $this->reply_to = $this->datos_configuracion['reply_to'];
       }

Script a correr en la base
~/pers/econ/scripts/economicas-script-ETAPA2.sql

Actualizar código
Descargar de la rama “produccion” de git el nuevo contenido.

Compilar recursos: 
cd /var/guarani3w/bin
./guarani compilar_recursos

Regenerar el catálogo:
cd /var/guarani3w/bin
./guarani generar_catalogo FCE

Crear cuenta de mail: sistema-guarani@econ.unicen.edu.ar
Con una respuesta automática del tipo "Por favor, NO responda a este mensaje, es un envío automático. El mismo ha sido generado a través del sistema SIU-Guarani."
*Nota: Se minimizará el uso de esta casilla, es sólo para los casos que es el mismo sistema el que envía avisos, no un usuario en particular. 


