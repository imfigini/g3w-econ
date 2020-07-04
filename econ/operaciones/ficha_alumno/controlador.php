<?php
namespace econ\operaciones\ficha_alumno;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use kernel\util\validador;
use siu\modelo\datos\catalogo;

class controlador extends \siu\operaciones\ficha_alumno\controlador
{

	public function is_perfil_docente()
	{
		$perfil = kernel::persona()->perfil()->get_id();
		if ($perfil == 'DOC') {
			return true;
		}
		return false;		
	}

	/**
	 * Autocompletado de alumno 
	 */
	function accion__auto_alumno()
	{
		$term = $this->validate_param('term', 'get', validador::TIPO_TEXTO);
		
		if (empty($term)) {
			$this->render_raw_json(array());
			return;
		}
		
        $term = utf8_decode($term);
		$parametros = array('term' => $term);
		
		//Si el perfil dónde se está consultando la ficha del alumno es DOCENTE, sólo puede cosultar los alumnos que estén en una mesa de examen
		if ($this->is_perfil_docente())
        {
            $parametros['legajo_doc'] = kernel::persona()->get_legajo_docente();
			$raw_data = catalogo::consultar('alumno', 'buscar_alumno_de_docente', $parametros);
		}
		else
		{
			$raw_data = catalogo::consultar('alumno', 'buscar_alumno', $parametros);
		}
				
		$data = array();
		foreach ($raw_data as $alumno) {
			$data[] = array(
				'id' => kernel::vinculador()->crear('ficha_alumno', 'index', array(
					0	=> $alumno['__ID__'],
					'term' => $term,
					'r' => 1,
				)),
				'label' => $alumno[1]
			);
		}

		$this->render_raw_json($data);
	}

	function get_fechas_turno_examen_actual()
	{
		$turno_examen = catalogo::consultar('examenes', 'get_fechas_turno_examen_actual', null);
		$date = date_create($turno_examen['FECHA_INICIO']);
		$turno_examen['FECHA_INICIO'] = date_format($date, 'd/m/Y');
		$date = date_create($turno_examen['FECHA_FIN']);
		$turno_examen['FECHA_FIN'] = date_format($date, 'd/m/Y');
		return $turno_examen;
	}
}
?>