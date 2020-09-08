--Desactivar control que verifica que adeude 1 sóla materia o esté cursando la correlativa poara poder inscribirse a final
UPDATE sga_conf_controles  
SET activo = 'N'
WHERE control IN ('800572')
AND operacion = 'exa00006';

--Activar controles que verifica que adeude a lo sumo 10 materias para poder inscribirse a final
UPDATE sga_conf_controles  
SET activo = 'S'
WHERE control IN ('800574')
AND operacion = 'exa00006';

UPDATE par_cont_x_oper 
SET es_valido = 'S'
WHERE operacion = 'exa00006'
AND control IN (800574);

