EXECUTE PROCEDURE dba.sui_mensajes ( 800572, 3, "Para inscribirte te debe faltar solo 1 materia o estar cursando una correlativa", 
"Para inscribirte te debe faltar solo 1 materia o estar cursando una correlativa", 1, 1 );

INSERT INTO par_implem_control (stored_procedure, parametros) VALUES ('ctr_econ_corr', 's,s,s,s');

INSERT INTO par_controles (control, stored_procedure, desc_abreviada, descripcion) VALUES
(800572, 'ctr_econ_corr', 
'Controla solo 1 materia o estar cursando una correlativa', 
'solo 1 materia o estar cursando una correlativa');

INSERT INTO par_cont_x_punto (punto_de_control, control) VALUES (8, 800572);

Execute Procedure sp_ConfCont_Cont (8, 800572);

/*
 DELETE FROM sga_conf_controles WHERE control = "800572";	
 DELETE FROM par_cont_x_oper WHERE control = "800572";
 DELETE FROM par_cont_x_evento WHERE control = "800572";
 DELETE FROM par_cont_x_punto WHERE control = "800572";
 DELETE FROM par_controles WHERE control = "800572";
 DELETE FROM par_implem_control WHERE stored_procedure = "ctr_econ_corr";
*/
	
	


