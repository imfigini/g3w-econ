# Tercera etapa de implementación

> Tiempo requerido: 1-2 hs 

> Requerimientos: acceso a la base de datos y acceso al servidor donde esté corriendo la web de producción.

## Asignar un directorio temporal para la subida de archivos

1) Si no está seteado "upload_tmp_dir" en el php.ini, hay que hacerlo para que ande la carga de archivos (fotos de los DNI).
- php -i | grep upload_tmp_dir

Identificar el php.ini cargado:
- php -i | grep 'php.ini'

Modificar la línea donde se especifica upload_tmp_dir:
```
upload_tmp_dir = /var/tmp/adjuntos_guarani
```
Verificar que el directorio exista, sino crearlo. Y darle los permisos necesarios para que el apache pueda subir cosas. 


2) Ver en el config.php de guarani, dónde se quieren guardar las fotos de los DNI.

Sugerencia: 
```
'dir_attachment' => '/var/guarani3w/files',
```
Verifcar que exista el directorio, y dar los permisos necesarios de escrituta al usuario apache. 

## Script a correr en la base 

~/pers/econ/scripts/economicas-script-ETAPA3.sql

dbaccess siu_guarani economicas-script-ETAPA3.sql

## Actualizar código 

Descargar de la rama “produccion” de git el nuevo contenido.

Compilar recursos: 
- cd /var/guarani3w/bin 
- ./guarani compilar_recursos

Regenerar el catálogo: 
- cd /var/guarani3w/bin 
- ./guarani generar_catalogo FCE
