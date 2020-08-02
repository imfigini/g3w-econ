# Etapa de implementación 3.2
Icluye funcionalidad para que los alumnos deban aceptar los "Términos y Condiciones" para poder rendir Integrador. Y habilita a los dcoenets a ver la ficha académica de los mismos. 

> Tiempo requerido: 5 min

> Requerimientos: acceso a la base de datos y acceso al servidor donde esté corriendo la web de producción.

## Scripts a correr en la base 

~/pers/econ/scripts/

- dbaccess siu_guarani econ-script-ETAPA3.2.sql


## Actualizar código 

Descargar de la rama “produccion” de git el nuevo contenido.

Compilar recursos: 
- cd /var/guarani3w/bin 
- ./guarani compilar_recursos

Regenerar el catálogo: 
- cd /var/guarani3w/bin 
- ./guarani generar_catalogo FCE
