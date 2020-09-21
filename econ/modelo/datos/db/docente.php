<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
use siu\conf\datos_comunes_actas;

class docente extends \siu\modelo\datos\db\docente
{
	/**
	 * parametros: _ua, nro_inscripcion
	 * cache: memoria
	 * cache_expiracion: 600
	 * filas: n
	 */
	function agenda_examenes($parametros)
	{
		$sql = "execute procedure sp_AgendaDocExa(" . $parametros["_ua"] . ", " . $parametros["nro_inscripcion"] .")";
		$datos = kernel::db()->consultar($sql, db::FETCH_NUM);
		kernel::log()->add_debug('iris datos_conuslta', $datos);
		$nuevo = array();
		foreach($datos as $id => $dato) {
			$nuevo[$id]['MATERIA_NOMBRE_COMPLETO'] = $dato[2]." (".$dato[1].")";
			$nuevo[$id]['MATERIA'] = $dato[1];
			$nuevo[$id]['MATERIA_NOMBRE'] = $dato[2];
			$nuevo[$id]['ANIO_ACADEMICO'] = $dato[3];
			$nuevo[$id]['TURNO_EXAMEN'] = $dato[4];
			$nuevo[$id]['MESA_EXAMEN'] = $dato[5];
			$nuevo[$id]['LLAMADO'] = $dato[6];
			$nuevo[$id]['LEGAJO'] = $dato[7];
			$nuevo[$id]['DOCENTE_NOMBRE'] = $dato[8];
			$nuevo[$id]['DOCENTE_ROL'] = $dato[9];
			$nuevo[$id]['FECHA'] = $dato[11];
			$nuevo[$id]['HORA_INI'] = $dato[12];
			$nuevo[$id]['HORA_FIN'] = $dato[17];
			$nuevo[$id]['AULA'] = $dato[13];
			$nuevo[$id]['AULA_NOMBRE'] = $dato[14];
			$nuevo[$id]['EDIFICIO'] = $dato[15];
			$nuevo[$id]['EDIFICIO_NOMBRE'] = $dato[16];
			$nuevo[$id]['SEDE'] = $dato[18];
			$nuevo[$id]['SEDE_NOMBRE'] = $dato[19];
			$nuevo[$id]['SEDE_NOMBRE_COMPLETO'] = $dato[19];
			$nuevo[$id]['TIPO_MESA'] = $this->get_tipo_mesa($nuevo[$id]);
			foreach($nuevo[$id] as $clave => $valor){
				if (empty($valor)){
					$nuevo[$id][$clave] = "No informa";
				}
			}
		}
		return $nuevo;
	}

	/**
	 * parametros: MATERIA, ANIO_ACADEMICO, TURNO_EXAMEN, MESA_EXAMEN
	 * cache: no
	 * filas: 1
	 */
	private function get_tipo_mesa($parametros)
	{
		$sql = "SELECT sga_tipos_mesa.descripcion
					FROM sga_mesas_examen, sga_tipos_mesa
					WHERE materia = '{$parametros["MATERIA"]}'
					AND anio_academico = '{$parametros["ANIO_ACADEMICO"]}'
					AND turno_examen = '{$parametros["TURNO_EXAMEN"]}'
					AND mesa_examen = '{$parametros["MESA_EXAMEN"]}'
					AND sga_tipos_mesa.tipo_mesa = sga_mesas_examen.admite_libres";
		$tipo_mesa = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		return $tipo_mesa['DESCRIPCION'];
	}

	/**
	 * parametros: _ua, nro_inscripcion, filtro_inscripciones
	 * cache: no
	 * filas: n
	*/
	function lista_mesas_vigentes($parametros)
	{
		//Se modificó para que en vez de mostrar el nombre de la mesa, muestre el tipo de mesa
		$sql_filtro_insc = "";
		$param = substr ( $parametros['filtro_inscripciones'] , 1,1);

		if ($param == 'C') {
		//if (strcmp($parametros['filtro_inscripciones'], 'C')  == 0) {
			$sql_filtro_insc = " AND ((SELECT COUNT(*) FROM sga_insc_examen as i WHERE 
					i.unidad_academica		= sga_llamados_mesa.unidad_academica
					AND i.materia			= sga_llamados_mesa.materia
					AND i.anio_academico	= sga_llamados_mesa.anio_academico
					AND i.turno_examen		= sga_llamados_mesa.turno_examen
					AND i.mesa_examen		= sga_llamados_mesa.mesa_examen
					AND i.llamado			= sga_llamados_mesa.llamado) > 0) ";
		};
			
		if ($param == 'S') {
			$sql_filtro_insc = " AND ((SELECT COUNT(*) FROM sga_insc_examen as i WHERE 
					i.unidad_academica		= sga_llamados_mesa.unidad_academica
					AND i.materia			= sga_llamados_mesa.materia
					AND i.anio_academico	= sga_llamados_mesa.anio_academico
					AND i.turno_examen		= sga_llamados_mesa.turno_examen
					AND i.mesa_examen		= sga_llamados_mesa.mesa_examen
					AND i.llamado			= sga_llamados_mesa.llamado) = 0) ";
		};
			

         // Lista de mesas de examen de fechas de hoy o futuras	
         // Mesas de examen donde se encuentra el docente.
         $sql = "SELECT
                    sga_docentes_llama.materia as materia,
                    sga_materias.nombre as materia_nombre,
                    sga_materias.nombre_reducido as materia_nombre_red,
                    sga_docentes_llama.anio_academico as anio_academico,
                    sga_docentes_llama.turno_examen as turno_examen,
                    sga_docentes_llama.mesa_examen as mesa_examen,
					sga_tipos_mesa.descripcion AS tipo_mesa,
                    sga_docentes_llama.llamado as llamado,
                    nvl(TO_CHAR(sga_prestamos.fecha , "."'%d/%m/%Y'"."),' ') ||' ' ||nvl( TO_CHAR(sga_prestamos.hora_inicio, "."'%H:%M'".") , ' ') as momento,
                    sga_sedes.sede as sede,
                    sga_sedes.nombre as sede_nombre,
                    sga_prestamos.fecha as fecha,
                    sga_prestamos.hora_inicio as hora_inicio,
                    (SELECT COUNT(*) FROM sga_insc_examen as i WHERE 
						i.unidad_academica		= sga_llamados_mesa.unidad_academica
						AND i.materia			= sga_llamados_mesa.materia
						AND i.anio_academico	= sga_llamados_mesa.anio_academico
						AND i.turno_examen		= sga_llamados_mesa.turno_examen
						AND i.mesa_examen		= sga_llamados_mesa.mesa_examen
						AND i.llamado			= sga_llamados_mesa.llamado) as cantidad_inscr
         
               FROM sga_docentes,
                    sga_docentes_llama,
                    sga_llamados_mesa,
                    sga_materias,
                    sga_prestamos,
                    sga_mesas_examen,
					sga_sedes, 
					sga_tipos_mesa
         
                WHERE sga_docentes.unidad_academica		 = {$parametros["_ua"]}
                  AND sga_docentes.nro_inscripcion  	 = {$parametros["nro_inscripcion"]}
                  AND sga_docentes_llama.legajo  		 = sga_docentes.legajo
                  AND sga_llamados_mesa.unidad_academica = sga_docentes_llama.unidad_academica
                  AND sga_llamados_mesa.materia          = sga_docentes_llama.materia
                  AND sga_llamados_mesa.anio_academico   = sga_docentes_llama.anio_academico
                  AND sga_llamados_mesa.turno_examen     = sga_docentes_llama.turno_examen
                  AND sga_llamados_mesa.mesa_examen      = sga_docentes_llama.mesa_examen
                  AND sga_llamados_mesa.llamado          = sga_docentes_llama.llamado
           
                  AND sga_materias.unidad_academica      = sga_llamados_mesa.unidad_academica
                  AND sga_materias.materia               = sga_llamados_mesa.materia
           
                  AND sga_prestamos.prestamo             = sga_llamados_mesa.prestamo
				  AND sga_prestamos.fecha                >= TODAY
           
                  AND sga_llamados_mesa.unidad_academica = sga_mesas_examen.unidad_academica
                  AND sga_llamados_mesa.materia          = sga_mesas_examen.materia
                  AND sga_llamados_mesa.anio_academico   = sga_mesas_examen.anio_academico
                  AND sga_llamados_mesa.turno_examen     = sga_mesas_examen.turno_examen
				  AND sga_llamados_mesa.mesa_examen      = sga_mesas_examen.mesa_examen
				  AND sga_tipos_mesa.tipo_mesa 			 = sga_mesas_examen.admite_libres
                  AND sga_mesas_examen.sede = sga_sedes.sede".$sql_filtro_insc.
                " ORDER BY sga_materias.nombre, 
                         sga_prestamos.fecha, 
                         sga_prestamos.hora_inicio, 
                         sga_docentes_llama.anio_academico, 
                         sga_docentes_llama.llamado
                ";         
		
		$datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);

		$nuevo = array();
		foreach($datos as $dato) {
			$id = catalogo::generar_id($dato['MATERIA'].$dato['ANIO_ACADEMICO'].$dato['TURNO_EXAMEN'].$dato['MESA_EXAMEN'].$dato['LLAMADO']);
			$nuevo[$id][catalogo::id] = $id;
			$nuevo[$id]['MATERIA'] = $dato['MATERIA'];
			$nuevo[$id]['MATERIA_NOMBRE'] = $dato['MATERIA_NOMBRE'];
			$nuevo[$id]['MATERIA_NOMBRE_COMPLETO'] = $dato['MATERIA_NOMBRE']." (".$dato['MATERIA'].")";
			$nuevo[$id]['ANIO_ACADEMICO'] = $dato['ANIO_ACADEMICO'];
			$nuevo[$id]['TURNO_EXAMEN'] = $dato['TURNO_EXAMEN'];
			$nuevo[$id]['MESA_EXAMEN'] = $dato['MESA_EXAMEN'];
			$nuevo[$id]['MESA_EXAMEN'] = $dato['TIPO_MESA'];
			$nuevo[$id]['LLAMADO'] = $dato['LLAMADO'];
			$nuevo[$id]['MOMENTO'] = $dato['MOMENTO'];
			$nuevo[$id]['SEDE'] = $dato['SEDE'];
			$nuevo[$id]['CANTIDAD_INSCR'] = $dato['CANTIDAD_INSCR'];
		}
		return $nuevo;
	
	}

}
?>