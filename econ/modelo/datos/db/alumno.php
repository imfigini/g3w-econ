<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
use siu\conf\datos_comunes_actas;
use siu\operaciones\_comun\util\caracteres_especiales;

class alumno extends \siu\modelo\datos\db\alumno
{
	/**
	 * parametros: _ua, term, legajo_doc
	 * cache: no
	 * no_quote: term
	 * filas: n
	 */
	function buscar_alumno_de_docente($parametros)
	{
        $termino = strtolower($parametros['term']);
        $delimitadores = array(",", ";");
        $termino = trim(str_replace($delimitadores, " ", $termino));
        $terminos = explode(" ", $termino);
        $no_vacio = function($string) {
            return $string != "";
        };
        $terminos = array_filter($terminos, $no_vacio);//quito los terminos vacios "".
        $terminos = array_unique($terminos);//quito los terminos repetidos.

        $like = "";
        if(count($terminos) > 0){
            $i = 0;
            $like .= " AND (";
            foreach($terminos as $term){

                if($i != 0){
                    $like .= " AND ";
                }

                $like .= "LOWER(p.apellido || ' ' || p.nombres || ' ' || a.legajo || ' ' || p.nro_documento) LIKE ".kernel::db()->quote_like($term, '%', '%');
                $i++;
            }
            $like .= ")";
        }

		//Iris: Se controla que sólo recupere alumnos inscriptos a examenes o del acta donde el docente forma parte de las mesas del llamado actual
        $sql = "
				SELECT
					DISTINCT p.nro_inscripcion as nro_inscripcion,
					'(' || td.desc_abreviada || ': ' || p.nro_documento || ') ' || p.apellido || ', ' || p.nombres || ' (' || a.legajo || ')' as descripcion,
					p.apellido || ', ' || p.nombres as descripcion_persona
				FROM
					sga_alumnos as a
					JOIN sga_personas as p ON (p.unidad_academica = a.unidad_academica AND p.nro_inscripcion = a.nro_inscripcion)
					JOIN mdp_tipo_documento td ON (p.tipo_documento = td.tipo_documento)
				WHERE a.unidad_academica  = {$parametros['_ua']}
					AND (	a.legajo IN (SELECT I.legajo FROM sga_insc_examen I
										JOIN sga_mesas_examen M ON (I.unidad_academica = M.unidad_academica AND I.materia = M.materia AND I.anio_academico = M.anio_academico AND I.turno_examen = M.turno_examen AND I.mesa_examen = M.mesa_examen)
										JOIN sga_docentes_llama D ON (D.unidad_academica = I.unidad_academica AND D.materia = I.materia AND D.anio_academico = I.anio_academico AND D.turno_examen = I.turno_examen AND D.mesa_examen = I.mesa_examen AND D.llamado = I.llamado )
										JOIN sga_turnos_examen T ON (T.anio_academico = M.anio_academico AND T.turno_examen = M.turno_examen AND TODAY BETWEEN fecha_inicio AND fecha_fin)
											WHERE D.legajo = {$parametros['legajo_doc']}
										)
							OR a.legajo IN (SELECT DA.legajo FROM sga_detalle_acta DA
										JOIN sga_actas_examen ACTA ON (ACTA.unidad_academica = DA.unidad_academica AND ACTA.tipo_acta = DA.tipo_acta AND ACTA.acta = DA.acta)
										JOIN sga_turnos_examen T ON (T.anio_academico = ACTA.anio_academico AND T.turno_examen = ACTA.turno_examen AND TODAY BETWEEN fecha_inicio AND fecha_fin)
										JOIN sga_docentes_llama D ON (D.unidad_academica = ACTA.unidad_academica AND D.materia = ACTA.materia AND D.anio_academico = ACTA.anio_academico AND D.turno_examen = ACTA.turno_examen AND D.mesa_examen = ACTA.mesa_examen AND D.llamado = ACTA.llamado )
										WHERE D.legajo = {$parametros['legajo_doc']}
										)
						)
				{$like} 
				ORDER BY 2";
		$datos = kernel::db()->consultar($sql, db::FETCH_NUM);
		
		$nuevo = array();
		if (!empty($datos)){
			foreach($datos as $key => $value) {
				$nuevo[$key][catalogo::id] = catalogo::generar_id($value[0]);
				$nuevo[$key][0] = $value[0];
				$nuevo[$key][1] = $value[1];
				$nuevo[$key][2] = $value[2];
			}
		}
		return $nuevo;
	}
}
