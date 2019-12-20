<?php
namespace econ\modelo\datos;

class _info_catalogo
{
	static function carga_asistencias__clase_detalle()
	{
		return array (
  'parametros' => 
  array (
    0 => 'clase',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__get_alumnos_inscriptos_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_asignac_com()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_calidad_inscripcion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'legajo',
    1 => 'comision',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_cant_inasistencias_justificadas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'legajo',
    1 => 'comision',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_clase_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'fecha',
    2 => 'hs_comienzo_clase',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_clases_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'comision',
    2 => 'filas',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__get_clases_comisiones_docente()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'comision',
    2 => 'filas',
  ),
  'cache' => 'memoria',
  'cache_expiracion' => '3600',
  'filas' => 'n*',
);
	}

	static function carga_asistencias__get_clases_subcomisiones_docente()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'legajo',
    2 => 'comision',
    3 => 'filas',
  ),
  'cache' => 'memoria',
  'cache_expiracion' => '3600',
  'filas' => 'n*',
);
	}

	static function carga_asistencias__get_comisiones_en_clase()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'materia',
    2 => 'tipo_clase',
    3 => 'dia_semana',
    4 => 'hs_comienzo_clase',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__get_datos_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_datos_comision_enviada()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'fecha',
    2 => 'hs_comienzo_clase',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_docentes_com()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_horarios_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__get_inasistencias_alumno()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'legajo',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_inscriptos()
	{
		return array (
  'parametros' => 
  array (
    0 => 'clase',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__get_justificacion_inasist()
	{
		return array (
  'parametros' => 
  array (
    0 => 'legajo',
    1 => 'clase',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_materias_y_dias_clases()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'legajo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__get_motivos_inasistencia()
	{
		return array (
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__get_nombre_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__get_planilla()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'fecha',
    2 => 'cantidad',
    3 => 'tipo',
    4 => 'tipo_clase',
  ),
  'param_null' => 
  array (
    0 => 'fecha',
    1 => 'cantidad',
    2 => 'tipo',
    3 => 'tipo_clase',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__get_subcomisiones()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__guardar()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'carrera',
    2 => 'legajo',
    3 => 'comision',
    4 => 'clase',
    5 => 'cant_inasist',
    6 => 'justific',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_asistencias__listado_alumnos_libres()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__listado_comisiones_docente()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'legajo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__listado_comisiones_filtro()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'docente',
    2 => 'per_lect',
    3 => 'materia',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__recuperar_generar_asistencias()
	{
		return array (
  'parametros' => 
  array (
    0 => 'clase',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_asistencias__tiene_cargadas_asistencias()
	{
		return array (
  'parametros' => 
  array (
    0 => 'clase',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__alta_evaluacion_parcial()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
    2 => 'escala_notas',
    3 => 'fecha_hora',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__asistio_evaluacion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'legajo',
    1 => 'comision',
    2 => 'evaluacion',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_evaluaciones_parciales__baja_evaluacion_parcial()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__escala_notas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'escala_notas',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__evaluacion_cabecera()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__evaluacion_detalle()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__get_cant_clases_al_dia_de_hoy()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'si',
  'filas' => '1',
);
	}

	static function carga_evaluaciones_parciales__get_ciclo_materia()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__get_clase()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'fecha',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_evaluaciones_parciales__get_nota_parcial()
	{
		return array (
  'parametros' => 
  array (
    0 => 'legajo',
    1 => 'comision',
    2 => 'evaluacion',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_evaluaciones_parciales__get_ultima_fecha_fin_turno_examen_regular()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_evaluaciones_parciales__guardar_renglon()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'carrera',
    2 => 'legajo',
    3 => 'comision',
    4 => 'evaluacion',
    5 => 'fecha_hora',
    6 => 'escala_notas',
    7 => 'nota',
    8 => 'corregido_por',
    9 => 'observaciones',
  ),
  'cache' => 'no',
  'param_tipo' => 
  array (
    0 => 'str/20',
    1 => 'str/20',
    2 => 'str/20',
    3 => 'int',
    4 => 'int',
    5 => 'str/25',
    6 => 'int',
    7 => 'str/10',
    8 => 'str/40',
    9 => 'str/80',
  ),
  'param_null' => 
  array (
    0 => 'corregido_por',
    1 => 'observaciones',
    2 => 'nota',
  ),
  'filas' => '1',
);
	}

	static function carga_evaluaciones_parciales__listado_escala_notas()
	{
		return array (
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__listado_evaluaciones_parciales()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'legajo',
  ),
  'cache' => 'no',
  'cache_expiracion' => '3600',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__listado_evaluaciones_parciales_econ()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'legajo',
  ),
  'cache' => 'no',
  'cache_expiracion' => '3600',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__listado_tipo_evaluacion()
	{
		return array (
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__listado_tipo_evaluacion_econ()
	{
		return array (
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function carga_evaluaciones_parciales__tiene_asistencia()
	{
		return array (
  'parametros' => 
  array (
    0 => 'legajo',
    1 => 'comision',
    2 => 'porc_asist',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function carga_evaluaciones_parciales__tiene_correlativas_cumplidas()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'anio_academico',
    2 => 'periodo',
    3 => 'legajo',
    4 => 'carrera',
    5 => 'materia',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function comisiones__get_datos_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function comisiones__get_parciales()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'visible_al_alumno',
  ),
  'param_null' => 
  array (
    0 => 'visible_al_alumno',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function comisiones__get_parciales_alumnos()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'visible_al_alumno',
  ),
  'param_null' => 
  array (
    0 => 'visible_al_alumno',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function comisiones__get_parciales_alumnos_econ()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function comisiones__get_resultados_acta_cursada()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function comisiones__get_resultados_acta_promocion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function comisiones__lista_comisiones_docente()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'nro_inscripcion',
    2 => 'anio_academico',
    3 => 'periodo',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function coord_materia__del_coordinador_anterior()
	{
		return array (
  'parametros' => 
  array (
    0 => 'coord_anterior',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function coord_materia__get_coordinador()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
    1 => 'anio_academico',
    2 => 'periodo',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function coord_materia__get_docentes_de_materia()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
    1 => 'anio_academico',
    2 => 'periodo',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function coord_materia__get_materias_en_comisiones()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function coord_materia__is_usuario_coord()
	{
		return array (
  'parametros' => 
  array (
    0 => 'coordinador',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function coord_materia__replicar_coordinador()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'anio_academico_anterior',
    3 => 'periodo_anterior',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function coord_materia__set_coordinador()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
    1 => 'anio_academico',
    2 => 'periodo',
    3 => 'coordinador',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function coord_materia__set_usuario_coord()
	{
		return array (
  'parametros' => 
  array (
    0 => 'coordinador',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function coord_materia__update_coordinador()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
    1 => 'anio_academico',
    2 => 'periodo',
    3 => 'coordinador',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__alta_evaluacion_parcial()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
    2 => 'fecha_hora',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__alta_propuesta_evaluacion_parcial()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
    2 => 'fecha_hora',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__baja_evaluacion_parcial()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__existe_evaluacion_parcial()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
  ),
  'cache' => 'no',
);
	}

	static function cursos__get_ciclo_de_materias()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_comisiones_de_materia_con_dias_de_clase()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_comisiones_promo_de_materia()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_dias_clase()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_estado_comision_fecha()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
    2 => 'fecha',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function cursos__get_evaluacion_asignada()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function cursos__get_evaluaciones_de_materia()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
    1 => 'anio_academico',
    2 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_evaluaciones_existentes()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_fecha_solicitada()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function cursos__get_fechas_eval_asignadas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
    1 => 'anio_academico',
    2 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_fechas_no_validas_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_hora_comienzo_clase()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'fecha',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_hora_inicio()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'dia_semana',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_materia()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_materias_cincuentenario()
	{
		return array (
  'parametros' => 
  array (
    0 => 'legajo',
    1 => 'carrera',
    2 => 'mix',
  ),
  'param_null' => 
  array (
    0 => 'legajo',
    1 => 'carrera',
    2 => 'mix',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_materias_mismo_mix()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_nombre_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function cursos__get_nombre_materia()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function cursos__get_nombre_materia_de_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function cursos__get_periodos_evaluacion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__get_tipo_escala_de_materia()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function cursos__set_evaluaciones_observaciones()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
    1 => 'anio_academico',
    2 => 'periodo_lectivo',
    3 => 'observaciones',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__existe_periodo_evaluacion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'orden',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__existe_periodo_solicitud_fecha()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__get_carreras()
	{
		return array (
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__get_cuatrimestre()
	{
		return array (
  'parametros' => 
  array (
    0 => 'fecha',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function evaluaciones_parciales__get_dias_no_laborales()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__get_evaluaciones_aceptadas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'carrera',
    3 => 'anio_cursada',
    4 => 'mix',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__get_evaluaciones_pendientes()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'carrera',
    3 => 'anio_cursada',
    4 => 'mix',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__get_mixs()
	{
		return array (
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__get_periodo()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'orden',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function evaluaciones_parciales__get_periodo_lectivo()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__get_periodo_solicitud_fecha()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__get_periodos_evaluacion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__insert_periodo_solicitud_fecha()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'fecha_inicio',
    3 => 'fecha_fin',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__insert_periodos_evaluacion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'orden',
    3 => 'fecha_inicio',
    4 => 'fecha_fin',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__set_periodo_solicitud_fecha()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'fecha_inicio',
    3 => 'fecha_fin',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__set_periodos_evaluacion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'orden',
    3 => 'fecha_inicio',
    4 => 'fecha_fin',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__set_validez_clases()
	{
		return array (
  'parametros' => 
  array (
    0 => 'fecha_inicio',
    1 => 'fecha_fin',
    2 => 'valido',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__strToMDY()
	{
		return array (
  'parametros' => 
  array (
    0 => 'strFecha',
  ),
  'cache' => 'no',
  'filas' => 'n',
  'El formato de strFecha debe ser' => 'd/m/Y ó Y-m-d',
);
	}

	static function evaluaciones_parciales__update_periodo_solicitud_fecha()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'fecha_inicio',
    3 => 'fecha_fin',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales__update_periodos_evaluacion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'orden',
    3 => 'fecha_inicio',
    4 => 'fecha_fin',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales_calendario__get_fechas_propuestas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
    3 => 'evaluacion',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function evaluaciones_parciales_calendario__get_hora_comienzo_clase()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function evaluaciones_parciales_calendario__tiene_notas_cargadas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'evaluacion',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function fechas_parciales__get_comisiones_de_materia_con_dias_de_clase()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
  ),
  'param_null' => 
  array (
    0 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function fechas_parciales__get_datos_comision()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function fechas_parciales__get_dias_clase()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function fechas_parciales__get_evaluaciones_observaciones()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
    1 => 'anio_academico',
    2 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function fechas_parciales__get_fechas_asignadas_o_solicitadas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function fechas_parciales__get_fechas_eval_asignadas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function fechas_parciales__get_fechas_eval_ocupadas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'materia',
    1 => 'anio_academico',
    2 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function fechas_parciales__get_fechas_eval_solicitadas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function fechas_parciales__get_fechas_no_validas_materia()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function fechas_parciales__hoy_dentro_de_periodo()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function insc_cursadas__get_alumnos_calidad_inscripcion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'calidad',
  ),
  'param_null' => 
  array (
    0 => 'calidad',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function insc_cursadas__update_calidad_insc_cursada()
	{
		return array (
  'parametros' => 
  array (
    0 => 'carrera',
    1 => 'legajo',
    2 => 'comision',
    3 => 'calidad',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function mixes__add_materia_a_mix()
	{
		return array (
  'parametros' => 
  array (
    0 => 'carrera',
    1 => 'plan',
    2 => 'version',
    3 => 'materia',
    4 => 'anio',
    5 => 'mix',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function mixes__del_materia_de_mix()
	{
		return array (
  'parametros' => 
  array (
    0 => 'carrera',
    1 => 'anio',
    2 => 'mix',
    3 => 'materia',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function mixes__get_anios_de_cursada_con_mix()
	{
		return array (
  'parametros' => 
  array (
    0 => 'carrera',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function mixes__get_carrera_nombre()
	{
		return array (
  'parametros' => 
  array (
    0 => 'carrera',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function mixes__get_carreras_grado()
	{
		return array (
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function mixes__get_materias_mix()
	{
		return array (
  'parametros' => 
  array (
    0 => 'carrera',
    1 => 'anio_de_cursada',
    2 => 'mix',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function mixes__get_materias_sin_mix()
	{
		return array (
  'parametros' => 
  array (
    0 => 'carrera',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function mixes__get_mixes_del_anio()
	{
		return array (
  'parametros' => 
  array (
    0 => 'carrera',
    1 => 'anio_de_cursada',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function mixes__get_plan_y_version_actual_de_materia()
	{
		return array (
  'parametros' => 
  array (
    0 => 'carrera',
    1 => 'materia',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function parametros__get_parametro()
	{
		return array (
  'parametros' => 
  array (
    0 => 'operacion',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function ponderacion_notas__eliminar_ponderacion()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
    3 => 'calidad',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function ponderacion_notas__get_ponderaciones_notas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
    3 => 'calidad',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function ponderacion_notas__set_ponderaciones_notas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
    3 => 'calidad',
    4 => 'porc_parciales',
    5 => 'porc_integrador',
    6 => 'porc_trabajos',
  ),
  'param_null' => 
  array (
    0 => 'porc_integrador',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function ponderacion_notas__update_ponderaciones_notas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
    3 => 'calidad',
    4 => 'porc_parciales',
    5 => 'porc_integrador',
    6 => 'porc_trabajos',
  ),
  'param_null' => 
  array (
    0 => 'porc_integrador',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function prom_directa__alta_materias_prom_directa()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function prom_directa__get_datos_materias_promo_directa()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function prom_directa__is_promo_directa()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

	static function prom_directa__replicar_materias_promo_directa()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'anio_academico_anterior',
    3 => 'periodo_anterior',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function prom_directa__resetear_prom_directa()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function prom_directa__set_prom_directa()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'materia',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function sistema__cache_lista_alumnos_carrera()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'CARRERA',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function sistema__cache_lista_alumnos_solicitud_actualizacion()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function sistema__cache_lista_alumnos_ua()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function sistema__cache_lista_carreras()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
  ),
  'cache' => 'no',
  'filas' => 'n',
);
	}

	static function sistema__controles_activos_punto()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'operacion',
    2 => 'evento',
    3 => '_interfaz',
    4 => 'punto_control',
  ),
  'cache' => 'memoria',
  'cache_expiracion' => '120',
  'filas' => 'n',
);
	}

	static function sistema__escala_notas()
	{
		return array (
  'parametros' => 
  array (
    0 => 'escala_notas',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function sistema__escala_notas_econ()
	{
		return array (
  'parametros' => 
  array (
    0 => 'escala_notas',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function sistema__fecha_actual()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
  ),
  'cache' => 'memoria',
  'cache_expiracion' => '60',
  'filas' => '1',
);
	}

	static function sistema__fecha_actual_formato()
	{
		return array (
  'parametros' => 
  array (
    0 => '_ua',
    1 => 'formato',
  ),
  'cache' => 'memoria',
  'cache_expiracion' => '60',
  'filas' => '1',
);
	}

	static function sistema__mensaje()
	{
		return array (
  'parametros' => 
  array (
    0 => 'mensaje',
  ),
  'cache' => 'memoria',
  'filas' => '1',
);
	}

	static function sistema__parametro()
	{
		return array (
  'parametros' => 
  array (
    0 => 'parametro',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function sistema__sedes()
	{
		return array (
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function sistema__version_base_valida()
	{
		return array (
  'parametros' => 
  array (
    0 => 'conversion',
    1 => 'creacion',
    2 => 'script_corrido',
  ),
  'cache' => 'memoria',
);
	}

	static function unidad_academica_econ__anios_academicos()
	{
		return array (
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function unidad_academica_econ__get_limites_periodo()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
  ),
  'cache' => 'no',
  'filas' => '1',
);
	}

}

?>