# Tercera etapa de implementación

> Tiempo requerido: 30-45 min

> Requerimientos: acceso a la base de datos y acceso al servidor donde esté corriendo la web de producción.

## Configuración de directorios para la subida de archivos

### php.ini
Si no está seteado "upload_tmp_dir" en el php.ini, hay que hacerlo para que ande la carga de archivos (fotos de los DNI).
- php -i | grep upload_tmp_dir

Identificar el php.ini cargado:
- php -i | grep 'php.ini'

Modificar la línea donde se especifica upload_tmp_dir:
```
upload_tmp_dir = /var/tmp/adjuntos_guarani
```
Verificar que el directorio exista, sino crearlo. Y darle los permisos necesarios para que el apache pueda subir cosas. 

### Directorio donde su guardan las imagenes
Por el momento es un directorio fijo:
```
const DIR_FOTOS ="/var/www/documentacion_preinscripcion/";
```
Dentro de dicho directorio, se guardan las fotos de los DNI dentro de subdirectrios identificados por el nro de DNI de cada alumno.

Esta carpeta se comparte con la documentación subida desde el módulo de preinscripción. 

Copiar la imagen "no_imagen.png" en la raiz de DIR_FOTOS.

- cp /var/guarani3w/src/pers/econ/www/img/no_imagen.png /var/www/documentacion_preinscripcion/


## Scripts a correr en la base 

~/pers/econ/scripts/etapa3

- dbaccess siu_guarani 800572/script_00_agrega_control.sql | tee -a log-script_800572_00.log
- dbaccess siu_guarani 800572/script_01_crea_procedure.sql | tee -a log-script_800572_01.log
- dbaccess siu_guarani 800573/script_00_agrega_control.sql | tee -a log-script_800573_00.log
- dbaccess siu_guarani 800573/script_01_crea_procedure.sql | tee -a log-script_800573_01.log
- dbaccess siu_guarani econ-script-ETAPA3.sql | tee -a log-script_ETAPA3.log


## Actualizar código 

Descargar de la rama “produccion” de git el nuevo contenido.

Compilar recursos: 
- cd /var/guarani3w/bin 
- ./guarani compilar_recursos

Regenerar el catálogo: 
- cd /var/guarani3w/bin 
- ./guarani generar_catalogo FCE
