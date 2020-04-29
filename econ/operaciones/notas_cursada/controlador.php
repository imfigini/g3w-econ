<?php

namespace econ\operaciones\notas_cursada;

use kernel\kernel;
use kernel\util\validador;
use siu\errores\error_guarani;
use siu\modelo\datos\catalogo;

class controlador extends \siu\operaciones\notas_cursada\controlador
{
	/**
	 * Autocalcular acta
	 */
	function accion__autocalcular()
	{
		$comision = $this->validate_param('comision', 'get', validador::TIPO_ALPHANUM);
		$legajo = $this->validate_param('legajo', 'get', validador::TIPO_ALPHANUM);
		
		$resultado = $this->modelo()->info__autocalcular_nota_alumno($comision, $legajo);

		//kernel::log()->add_debug('accion__autocalcular $resultado', $resultado);
		if ($resultado) {
			$this->render_raw_json($resultado);
		} else {
			$this->render_raw_json(Array());
		}
	}

	function pertenece_mix_cincuentenario($materia)
	{
		return catalogo::consultar('mixes', 'pertenece_mix_cincuentenario', Array('materia'=>$materia));
	}

}

?>
