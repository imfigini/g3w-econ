EXECUTE PROCEDURE dba.sui_mensajes ( 800574, 3, "Para inscribirte te deben faltar a lo sumo 10 finales", 
"Para inscribirte te deben faltar a lo sumo 10 materias", 1, 1 );

INSERT INTO par_implem_control (stored_procedure, parametros) VALUES ('ctr_econ_10finales', 's,s,s,s');

INSERT INTO par_controles (control, stored_procedure, desc_abreviada, descripcion) VALUES
(800574, 'ctr_econ_10finales', 
'Controla le falten a lo sumo 10 materias', 
'Controla le falten a lo sumo 10 materias');

INSERT INTO par_cont_x_punto (punto_de_control, control) VALUES (8, 800574);

Execute Procedure sp_ConfCont_Cont (8, 800574);

{
 DELETE FROM sga_conf_controles WHERE control = "800574";	
 DELETE FROM par_cont_x_oper WHERE control = "800574";
 DELETE FROM par_cont_x_evento WHERE control = "800574";
 DELETE FROM par_cont_x_punto WHERE control = "800574";
 DELETE FROM par_controles WHERE control = "800574";
 DELETE FROM par_implem_control WHERE stored_procedure = "ctr_econ_10finales";
}
	
	


