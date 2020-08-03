<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;

class insc_cursadas
{

	/**
     * parametros: anio_academico, periodo_lectivo
     * cache: no
     * filas: n
     */
    function get_materias_promo_con_comision($parametros)
    {
		//escala_notas: 4	Reales Promoció
        $sql = "SELECT DISTINCT ATR.materia, ATR.nombre_materia
				FROM sga_atrib_mat_plan ATR, sga_planes PL
					WHERE ATR.unidad_academica = PL.unidad_academica
					AND ATR.carrera = PL.carrera
					AND ATR.plan = PL.plan
					AND ATR.version = PL.version_actual
					AND ATR.tipo_materia <> 'G'
					AND ATR.materia IN (SELECT materia FROM sga_comisiones
											WHERE anio_academico = {$parametros['anio_academico']}
											AND periodo_lectivo = {$parametros['periodo_lectivo']} 
											AND escala_notas = 4
										)  
					ORDER BY 2 ";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}
	

	/**
	* parametros: anio_academico, periodo, calidad, materia
	* param_null: materia
    * cache: no
    * filas: n
    */
	function get_alumnos_calidad_inscripcion($parametros)
	{
		$sql = "SELECT 	I.legajo, 
						P.apellido || ', ' || P.nombres as alumno, 
						I.carrera,
						R.nombre_reducido as carrera_nombre, 
						C.materia, 
						M.nombre as materia_nombre, 
						C.comision, 
						C.nombre AS comision_nombre,
						I.calidad_insc
				FROM sga_insc_cursadas I, sga_comisiones C, sga_alumnos A, sga_personas P, sga_carreras R, sga_materias M
				WHERE I.comision = C.comision 
					and C.anio_academico = {$parametros['anio_academico']}
					and C.periodo_lectivo = {$parametros['periodo']}
					and C.escala_notas IN (4)
					and A.unidad_academica = I.unidad_academica 
					and A.carrera = I.carrera
					and A.legajo = I.legajo
					and P.unidad_academica = A.unidad_academica
					and P.nro_inscripcion = A.nro_inscripcion
					and R.carrera = I.carrera
					and M.materia = C.materia ";
					// and I.plan IN
					// 		(SELECT plan FROM sga_planes
					// 		WHERE carrera = I.carrera
					// 			AND plan = I.plan
					// 			AND version_actual = I.version
					// 			AND estado = 'V')							

		if (isset($parametros['calidad']) && ($parametros['calidad'] == "'P'" || $parametros['calidad'] == "'R'")) {
			$sql .= " AND calidad_insc = {$parametros['calidad']} ";
		}
		
		if (isset($parametros['materia']) && $parametros['materia'] != "''") {
			$sql .= " AND C.materia = {$parametros['materia']} ";
		}
		$sql .=  " ORDER BY 6,2";
		
		return kernel::db()->consultar($sql, db::FETCH_ASSOC);				
	}

	/**
	* parametros: carrera, legajo, comision, calidad_asignar
    * cache: no
    * filas: 1
    */
	function update_calidad_insc_cursada($parametros)
	{
		$sql = "UPDATE sga_insc_cursadas
					SET calidad_insc = {$parametros['calidad_asignar']}
				WHERE carrera = {$parametros['carrera']}
				AND legajo = {$parametros['legajo']}
				AND comision = {$parametros['comision']}";
		$resultado = kernel::db()->ejecutar($sql);
		
		// si se cambia la calidad a PROMO, hay un bug que no le asigna nro de acta promo (si folio y renglón, pero no el nro de acta)
		if (trim($parametros['calidad_asignar']) == 'P')
		{
			$sql = "SELECT acta_promocion, acta_regular
						FROM sga_curs_pendiente
						WHERE comision = {$parametros['comision']}
						AND legajo = {$parametros['legajo']} ";
			$actas = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
			if ( (isset($actas['ACTA_REGULAR']) && $actas['ACTA_REGULAR'] != '')
					&& (!isset($actas['ACTA_PROMOCION']) || $actas['ACTA_PROMOCION'] == '') )
			{
				$sql = "SELECT DISTINCT acta_promocion
							FROM sga_curs_pendiente
							WHERE comision = {$parametros['comision']}
							AND acta_promocion IS NOT NULL ";
				
				$acta_promo = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
				$acta_promo = $acta_promo['ACTA_PROMOCION'];
				$sql = "UPDATE sga_curs_pendiente
							SET acta_promocion = $acta_promo
						WHERE comision = {$parametros['comision']}
						AND legajo = {$parametros['legajo']} ";
				kernel::db()->ejecutar($sql);
			}
		}
		else //si se cambia la calidad a REGULAR, hay un bug que no le borra el nro de acta promo (si folio y renglón, pero no el nro de acta)
		{
			$sql = "UPDATE sga_curs_pendiente
						SET acta_promocion = null
					WHERE comision = {$parametros['comision']}
					AND legajo = {$parametros['legajo']} ";
			kernel::db()->ejecutar($sql);
		}
		return $resultado;
	}

	/**
	* parametros: _ua, anio_academico, periodo, materia, legajo
    * cache: no
    * filas: 1
    */
	function existe_en_cursada_pdte($parametros)
	{
		$sql = "SELECT P.acta_promocion, P.acta_regular
				FROM 	sga_curs_pendiente P, 
						sga_comisiones C
				WHERE P.comision = C.comision
				AND C.anio_academico = {$parametros['anio_academico']} 
				AND C.periodo_lectivo = {$parametros['periodo']}
				AND C.materia = {$parametros['materia']}
				AND (P.resultado IS NOT NULL OR P.nota IS NOT NULL)
				AND P.legajo = {$parametros['legajo']} ";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
	* parametros: _ua, comision
    * cache: no
    * filas: 1
    */
	function estado_acta_promo($parametros)
	{
		$sql = "SELECT estado 
					FROM sga_actas_promo 
				WHERE comision = {$parametros['comision']} ";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

}

