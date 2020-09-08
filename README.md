# Tercera etapa de implementación

> Tiempo requerido: 10 min

> Requerimientos: acceso a la base de datos y acceso al servidor donde esté corriendo la web de producción.


## Scripts a correr en la base 

~ pers/econ/scripts/etapa3.3

- dbaccess siu_guarani 800574/script_00_agrega_control.sql
- dbaccess siu_guarani 800574/script_01_crea_procedure.sql
- dbaccess siu_guarani econ-script-ETAPA3.3.sql


## Actualizar código 

Descargar de la rama “produccion” de git el nuevo contenido.

Compilar recursos: 
- cd /var/guarani3w/bin 
- ./guarani compilar_recursos

Regenerar el catálogo: 
- cd /var/guarani3w/bin 
- ./guarani generar_catalogo FCE
