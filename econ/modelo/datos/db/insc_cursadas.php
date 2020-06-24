<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;

class insc_cursadas
{
	/**
	* parametros: anio_academico, periodo, calidad
	* param_null: calidad
    * cache: no
    * filas: n
    */
	function get_alumnos_calidad_inscripcion($parametros)
	{
		$sql = "SELECT 	I.legajo, 
						P.apellido || ', ' || P.nombres as alumno, 
						I.carrera,
						R.nombre as carrera_nombre, 
						C.materia, 
						M.nombre as materia_nombre, 
						C.comision, 
						I.calidad_insc
				FROM sga_insc_cursadas I, sga_comisiones C, sga_alumnos A, sga_personas P, sga_carreras R, sga_materias M
				WHERE I.comision = C.comision 
					and C.anio_academico = {$parametros['anio_academico']}
					and C.periodo_lectivo = {$parametros['periodo']}
					and C.escala_notas IN (4)
					and I.plan IN
							(SELECT plan FROM sga_planes
							WHERE carrera = I.carrera
								AND plan = I.plan
								AND version_actual = I.version
								AND estado = 'V')							
					and A.unidad_academica = I.unidad_academica 
					and A.carrera = I.carrera
					and A.legajo = I.legajo
					and P.unidad_academica = A.unidad_academica
					and P.nro_inscripcion = A.nro_inscripcion
					and R.carrera = I.carrera
					and M.materia = C.materia ";
					
		if (isset($parametros['calidad']) && ($parametros['calidad'] == "'P'" || $parametros['calidad'] == "'R'")) {
			$sql .= " AND calidad_insc = {$parametros['calidad']} ";
		}
		$sql .=  " ORDER BY 6,2";
		
		return kernel::db()->consultar($sql, db::FETCH_ASSOC);				
	}

	/**
	* parametros: carrera, legajo, comision, calidad
    * cache: no
    * filas: 1
    */
	function update_calidad_insc_cursada($parametros)
	{
		$sql = "UPDATE sga_insc_cursadas
					SET calidad_insc = {$parametros['calidad']}
				WHERE carrera = {$parametros['carrera']}
				AND legajo = {$parametros['legajo']}
				AND comision = {$parametros['comision']}";
		return kernel::db()->ejecutar($sql);
	}
}

