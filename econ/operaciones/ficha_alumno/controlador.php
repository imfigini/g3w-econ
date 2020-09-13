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
		
        //$term = utf8_decode($term);
		$parametros = array('term' => utf8_decode($term));
		
		/* Si el perfil d�nde se est� consultando la ficha del alumno es DOCENTE, s�lo puede cosultar:
			- los alumnos que est�n en una de sus mesa de examen durante el turno del mismo
			- los alumnos iscriptos a su comisi�n, duarante el per�odo del integrador
		*/
		if ($this->is_perfil_docente())
        {
			$parametros['legajo_doc'] = kernel::persona()->get_legajo_docente();
			$raw_data_1 = $raw_data_2 = array();
			
			$turno_examen_actual = $this->get_fechas_turno_examen_actual();
			if (isset($turno_examen_actual['FECHA_INICIO']) && isset($turno_examen_actual['FECHA_FIN'])) {
				$raw_data_1 = catalogo::consultar('alumno', 'buscar_alumno_de_docente_en_examen', $parametros);
			}

			$periodo_integrador_actual = $this->get_periodo_integrador_actual();
			if (isset($periodo_integrador_actual['FECHA_INICIO']) && isset($periodo_integrador_actual['FECHA_FIN'])) {
				$raw_data_2 = catalogo::consultar('alumno', 'buscar_alumno_de_docente_en_comision', $parametros);
			}
			$raw_data = array_merge($raw_data_1, $raw_data_2);
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
		$turno_examen = catalogo::consultar('generales', 'get_fechas_turno_examen_actual', null);
		if (isset($turno_examen['FECHA_INICIO'])) {
			$date1 = date_create($turno_examen['FECHA_INICIO']);
			$turno_examen['FECHA_INICIO'] = date_format($date1, 'd/m/Y');
			$date2 = date_create($turno_examen['FECHA_FIN']);
			$turno_examen['FECHA_FIN'] = date_format($date2, 'd/m/Y');
		}
		else
		{
			$turno_examen['FECHA_INICIO'] = null;
			$turno_examen['FECHA_FIN'] = null;
		}
		return $turno_examen;
	}

	function get_periodo_integrador_actual()
	{
		$periodo_integrador_actual = catalogo::consultar('terminos_condiciones', 'periodo_integrador', null);
		kernel::log()->add_debug('iris $periodo_integrador_actual', $periodo_integrador_actual);
		if (isset($periodo_integrador_actual['FECHA_PRIMERA']) && isset($periodo_integrador_actual['FECHA_ULTIMA'])) 
		{
			$date1 = date_create($periodo_integrador_actual['FECHA_PRIMERA']);
			$periodo_integrador_actual['FECHA_INICIO'] = date_format($date1, 'd/m/Y');
			$date2 = date_create($periodo_integrador_actual['FECHA_ULTIMA']);
			$periodo_integrador_actual['FECHA_FIN'] = date_format($date2, 'd/m/Y');
		}
		else
		{
			$periodo_integrador_actual['FECHA_INICIO'] = null;
			$periodo_integrador_actual['FECHA_FIN'] = null;
		}
		return $periodo_integrador_actual;
	}


}
?>