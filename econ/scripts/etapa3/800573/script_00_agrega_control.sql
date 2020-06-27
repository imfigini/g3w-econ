EXECUTE PROCEDURE dba.sui_mensajes ( 800573, 3, "Para inscribirte debes tenes los datos censales completos (foto del DNI, email, y celular)", 
"Para inscribirte debes tenes los datos censales completos (foto del DNI, email y celular)", 1, 1 );

INSERT INTO par_implem_control (stored_procedure, parametros) VALUES ('ctr_econ_dni_foto', 's,s,s,s');

INSERT INTO par_controles (control, stored_procedure, desc_abreviada, descripcion) VALUES
(800573, 'ctr_econ_dni_foto', 
'Controla foto del DNI, meail y celular', 
'Controla foto del DNI, meail y celular');

INSERT INTO par_cont_x_punto (punto_de_control, control) VALUES (8, 800573);

Execute Procedure sp_ConfCont_Cont (8, 800573);

{
 DELETE FROM sga_conf_controles WHERE control = "800573";	
 DELETE FROM par_cont_x_oper WHERE control = "800573";
 DELETE FROM par_cont_x_evento WHERE control = "800573";
 DELETE FROM par_cont_x_punto WHERE control = "800573";
 DELETE FROM par_controles WHERE control = "800573";
 DELETE FROM par_implem_control WHERE stored_procedure = "ctr_econ_dni_foto";
}
	
	


