<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
use siu\conf\datos_comunes_actas;
use siu\operaciones\_comun\util\caracteres_especiales;

class terminos_condiciones
{
	/**
	 * parametros: 
	 * cache: no
	 * filas: 1
	 */
	function periodo_integrador($parametros)
	{
		/*	Contabilizo 30 días antes al actual, para que tome el cuatrimetrse deseado, 
			ya que los integradores se desfasaron del cuatrimestre por la pandemia */
		$sql = "SELECT 	MIN(E.fecha_hora)::DATE AS fecha_primera, 
						MAX(E.fecha_hora)::DATE AS fecha_ultima
				FROM sga_cron_eval_parc E, sga_comisiones C, sga_periodos_lect P
					WHERE E.comision = C.comision
						AND C.anio_academico = P.anio_academico 
						AND C.periodo_lectivo = P.periodo_lectivo
						AND TODAY BETWEEN P.fecha_inicio AND P.fecha_fin
						AND E.evaluacion = 14 ";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	 * parametros: _ua, legajo
	 * cache: no
	 * filas: 1
	 */
	function acepto_terminos_y_condiciones($parametros)
	{
		/*	Contabilizo 30 días antes al actual, para que tome el cuatrimetrse deseado, 
			ya que los integradores se desfasaron del cuatrimestre por la pandemia */
		$sql = "SELECT fecha 
				FROM ufce_acpt_term_cond T,  sga_periodos_lect P
				WHERE T.anio_academico = P.anio_academico 
					AND T.periodo_lectivo = P.periodo_lectivo 
					AND TODAY BETWEEN P.fecha_inicio AND P.fecha_fin
					AND T.legajo = {$parametros['legajo']}	";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

/**
	 * parametros: _ua, nro_inscripcion
	 * cache: no
	 * filas: 1
	 */
	function get_acepto_term_y_cond($parametros)
	{
		/*	Contabilizo 30 días antes al actual, para que tome el cuatrimetrse deseado, 
			ya que los integradores se desfasaron del cuatrimestre por la pandemia */
		$sql = "SELECT DISTINCT fecha 
				FROM ufce_acpt_term_cond T, sga_alumnos A, sga_periodos_lect P
				WHERE T.anio_academico = P.anio_academico 
					AND T.periodo_lectivo = P.periodo_lectivo 
					AND TODAY BETWEEN P.fecha_inicio AND P.fecha_fin
					AND T.legajo = A.legajo
					AND A.nro_inscripcion = {$parametros['nro_inscripcion']} ";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	 * parametros: _ua, legajo
	 * cache: no
	 * filas: 1
	 */
	function grabar_acept_term_cond($parametros)
	{
		/*	Contabilizo 30 días antes al actual, para que tome el cuatrimetrse deseado, 
			ya que los integradores se desfasaron del cuatrimestre por la pandemia */
		$sql = "SELECT anio_academico, periodo_lectivo 
					FROM sga_periodos_lect
					WHERE TODAY BETWEEN fecha_inicio AND fecha_fin ";
		$datos = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		$anio = $datos['ANIO_ACADEMICO'];
		$periodo = $datos['PERIODO_LECTIVO'];

		$sql = "INSERT INTO ufce_acpt_term_cond(legajo, anio_academico, periodo_lectivo, fecha)
				VALUES ({$parametros['legajo']}, $anio, '$periodo', CURRENT YEAR TO SECOND) ";
		kernel::log()->add_debug('grabar_acept_term_cond', $sql);
		return kernel::db()->ejecutar($sql);
	}

}
