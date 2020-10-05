DROP VIEW "dba".vw_datos_censales_actuales;

CREATE VIEW "dba".vw_datos_censales_actuales (unidad_academica, nro_inscripcion, fecha_relevamiento, fecha_actualiz, estado_civil, turno_preferido, e_mail, tipo_visa, otorgamiento_visa, vencimiento_visa, tipo_residencia, tipo_res_per_lect, 
	calle_per_lect, numero_per_lect, piso_per_lect, dpto_per_lect, unidad_per_lect, loc_per_lect, cp_per_lect, te_per_lect, calle_proc, numero_proc, piso_proc, dpto_proc, unidad_proc, loc_proc, cp_proc, te_proc, otros_estud_super, 
	viven_padres, fliares_cargo_alum, sit_laboral_alu, categ_ocup_alum, act_econom_alum, det_rama_act_alum, hora_sem_trab_alum, rel_trab_carrera, sit_laboral_padre, ult_est_cur_padre, act_econom_padre, det_rama_act_padre, categ_ocup_padre, 
	sit_laboral_madre, ult_est_cur_madre, act_econom_madre, det_rama_act_madre, categ_ocup_madre, nombre_pers_alleg, calle_pers_alleg, nro_pers_alleg, piso_pers_alleg, dpto_pers_alleg, unidad_pers_alleg, loc_pers_alleg, cp_pers_alleg, 
	te_pers_alleg, empresa, empresa_otra, sector, subsector, facilidad_est_emp, facilidad_est_inst, antiguedad,  apellido_pers_alleg, tipo_allegado)
	AS
	SELECT * FROM "dba".sga_datos_censales D
	WHERE D.fecha_relevamiento = (SELECT MAX(D2.fecha_relevamiento) FROM "dba".sga_datos_censales D2
					WHERE D2.unidad_academica = D.unidad_academica
					AND D2.nro_inscripcion = D.nro_inscripcion);

CREATE VIEW "dba".vw_datos_cen_aux_actuales (unidad_academica, nro_inscripcion, fecha_relevamiento, sec_egreso, sec_anio_ingreso, sec_anio_admision, o_est_ter_estado, o_est_uni_estado, sit_actual_padre, sit_actual_madre, tit_obt_padre, tit_obt_madre, cant_fami_cargo, cant_empl_cargo, barrio_per_lec, barrio_proc, barrio_alleg, pais_nacionalidad, existe_trab_alum, cant_hijos_alum, vive_actual_con, obra_social_alu, obra_social_trab, obra_social_fami, obra_social_univ, costea_estudios, tiene_beca, tiene_beca_univ, tiene_beca_nacio, tiene_beca_inter, remuneracion, horario_trabajo, practica_deportes, hace_dep_univ, hace_dep_gim_priv, hace_dep_partic, hace_dep_otros, prac_dep_futbol, prac_dep_basquet, prac_dep_voley, prac_dep_gimnasia, prac_dep_tenis, prac_dep_natacion, prac_dep_handball, prac_dep_otros, padre_vive, madre_vive, vive_con_conyuge, vive_con_padre, vive_con_madre, vive_con_hijos, vive_con_hermanos, celular_numero, celular_compania)
	AS
	SELECT * FROM "dba".sga_datos_cen_aux D
	WHERE D.fecha_relevamiento = (SELECT MAX(D2.fecha_relevamiento) FROM "dba".sga_datos_censales D2
					WHERE D2.unidad_academica = D.unidad_academica
					AND D2.nro_inscripcion = D.nro_inscripcion);

--SELECT * FROM vw_datos_censales_actuales;
--SELECT * FROM vw_datos_cen_aux_actuales;

--GRANT SELECT ON "dba".vw_datos_censales_actuales TO PUBLIC; 
