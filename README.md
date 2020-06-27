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


### config.php
Ver en el config.php de guarani, dónde se quieren guardar efectivamente las fotos de los DNI.

Sugerencia: 
```
'dir_attachment' => '/var/guarani3w/files',
```
Verifcar que exista el directorio, y dar los permisos necesarios de escrituta al usuario apache. 


### Configurar backup de los archivos subidos

Ver de configurar algún backup externo de las fotos de los DNI subdidos. 
A modo sugerencia, puede ser con algo de este estilo, con un archivo dentro de /etc/cron.daily:

Si se configuró 'dir_attachment' => '/var/guarani3w/files':
```
#!/bin/bash
set -u
DST_USR=
DST_HOST=
DST_DIR=
rsync -rtucz --delete /var/guarani3w/files/FCE/ $DST_USR@$DST_HOST:$DST_DIR
```
## Scripts a correr en la base 

~/pers/econ/scripts/etapa3

- dbaccess siu_guarani 800572/script_00_agrega_control.sql
- dbaccess siu_guarani 800572/script_01_crea_procedure.sql
- dbaccess siu_guarani 800573/script_00_agrega_control.sql
- dbaccess siu_guarani 800573/script_01_crea_procedure.sql
- dbaccess siu_guarani econ-script-ETAPA3.sql

## Actualizar código 

Descargar de la rama “produccion” de git el nuevo contenido.

Compilar recursos: 
- cd /var/guarani3w/bin 
- ./guarani compilar_recursos

Regenerar el catálogo: 
- cd /var/guarani3w/bin 
- ./guarani generar_catalogo FCE
