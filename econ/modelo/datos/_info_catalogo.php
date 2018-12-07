<?php
namespace econ\modelo\datos;

class _info_catalogo
{
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
  'cache' => 'memoria',
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
  'param_null' => 
  array (
    0 => 'coordinador',
  ),
  'cache' => 'memoria',
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
  'cache' => 'memoria',
  'filas' => 'n',
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

	static function cursos__get_dias_de_comision()
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

	static function cursos__get_fechas_clase()
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

	static function cursos__get_fechas_eval_asignadas()
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

	static function cursos__get_fechas_no_validas()
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
  ),
  'param_null' => 
  array (
    0 => 'legajo',
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

	static function cursos__get_porcentajes_instancias()
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

	static function cursos__get_posibles_fechas_eval()
	{
		return array (
  'parametros' => 
  array (
    0 => 'anio_academico',
    1 => 'periodo',
    2 => 'comision',
    3 => 'pertenece_fundamento',
    4 => 'evaluacion',
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

	static function cursos__set_porcentajes_instancias()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'porc_parciales',
    2 => 'porc_integrador',
    3 => 'porc_trabajos',
  ),
  'cache' => 'memoria',
  'filas' => 'n',
);
	}

	static function cursos__update_porcentajes_instancias()
	{
		return array (
  'parametros' => 
  array (
    0 => 'comision',
    1 => 'porc_parciales',
    2 => 'porc_integrador',
    3 => 'porc_trabajos',
  ),
  'cache' => 'memoria',
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

	static function evaluaciones_parciales__strToMDY()
	{
		return array (
  'parametros' => 
  array (
    0 => 'strFecha',
  ),
  'cache' => 'no',
  'filas' => 'n',
  'El formato de strFecha debe ser' => 'Y-m-d',
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

}

?>