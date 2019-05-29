<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
use siu\modelo\entidades\alumno_foto;

class carga_asistencias extends \siu\modelo\datos\db\carga_asistencias
{
/**
	 * parametros: _ua, legajo
	 * cache: no
	 * filas: n
	 */
    function listado_comisiones_docente($parametros)
    {
        $sql = "SELECT	(SELECT COUNT(*) FROM sga_subcomisiones 
				WHERE	sga_subcomisiones.comision = sga_comisiones.comision) as cnt_subcomisiones,
						sga_comisiones.materia,
						sga_materias.nombre as materia_nombre,
						sga_comisiones.comision,
						sga_comisiones.nombre as comision_nombre,
						sga_comisiones.anio_academico,
						sga_comisiones.periodo_lectivo,
						sga_comisiones.catedra,
						DECODE(sga_comisiones.turno,'T', 'Tarde', 'N', 'Noche', 'M', 'Mañana', 'No informa') as turno,
						sga_sedes.sede,
						sga_sedes.nombre as sede_nombre,
						(SELECT COUNT(*)  FROM sga_insc_cursadas WHERE comision = sga_comisiones.comision AND estado in ('A','P','E')) as cant_inscriptos,
						(SELECT COUNT (DISTINCT sga_inasis_acum.legajo) FROM sga_inasis_acum WHERE sga_inasis_acum.comision = sga_comisiones.comision AND sga_inasis_acum.estado = 'L') as cant_libres 
				FROM	sga_docentes_com
				JOIN	sga_comisiones		ON (sga_comisiones.comision = sga_docentes_com.comision)
				JOIN	sga_periodos_lect	ON (sga_periodos_lect.anio_academico = sga_comisiones.anio_academico
											AND sga_periodos_lect.periodo_lectivo = sga_comisiones.periodo_lectivo)
				JOIN	sga_materias		ON (sga_materias.unidad_academica = sga_comisiones.unidad_academica
											AND sga_materias.materia = sga_comisiones.materia)
				JOIN	sga_sedes			ON (sga_sedes.sede = sga_comisiones.sede)
			
				WHERE	sga_docentes_com.legajo =  {$parametros['legajo']}
				AND sga_periodos_lect.fecha_inactivacion >= TODAY
				ORDER BY sga_comisiones.anio_academico, sga_comisiones.periodo_lectivo";
				
				$datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
				$resultado = array();
				foreach(array_keys($datos) as $id) {
					$resultado[$datos[$id]['COMISION']] = $datos[$id];
					$resultado[$datos[$id]['COMISION']][catalogo::id] = catalogo::generar_id($datos[$id]['COMISION']);
				}
				return $resultado;
    }

}
?>