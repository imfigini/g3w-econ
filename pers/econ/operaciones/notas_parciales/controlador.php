<?php

namespace econ\operaciones\notas_parciales;

use siu\guarani;
use kernel\kernel;
use siu\errores\error_guarani_procesar_renglones;
use kernel\util\validador;
use siu\modelo\datos\catalogo;

class controlador extends \siu\operaciones\notas_parciales\controlador
{

	function accion__crear_evaluacion()
	{
		$comision = $this->validate_param('comision', 'post', validador::TIPO_ALPHANUM);
		$tipo	  = $this->validate_param('tipo',     'post', validador::TIPO_INT);
		$escala   = $this->validate_param('escala',   'post', validador::TIPO_INT);
		try
		{
			$fecha = $this->validate_param('fecha',    'post', validador::TIPO_DATE, array('format' => 'Y-m-d'));
		}
		catch(\kernel\error_kernel_validacion $e) {
			throw new \siu\errores\error_guarani_usuario('La fecha es inválida. Por favor ingrese una fecha en el formato día/mes/año.');
		}
		
		//$hora = kernel::request()->getPost('hora').':'.kernel::request()->getPost('minutos').':00';
		$hora = $this->validate_param('hora', 'post', validador::TIPO_INT).':'.$this->validate_param('minutos', 'post', validador::TIPO_INT).':00';
		$this->validate_value($hora, validador::TIPO_TIME, array('format' => 'H:i:s'));

		$msg = $this->modelo()->alta_evaluacion_parcial($comision, $tipo, $escala, $fecha, $hora);
		if ($msg != 'OK')
			$this->render_ajax('', array('success' => false, 'mensaje' => $msg));

		/*$pagelet = $this->vista()->pagelet('lista_materias');
		$pagelet->set_estado_info('crear_parcial');
		kernel::renderer()->add($pagelet);*/
		$evaluaciones = $this->modelo()->info__lista_evaluaciones();
		foreach($evaluaciones as $materia)
		{
			if (	$materia['COMISION_URL'] == $comision
				&&	isset($materia['EVALUACION_COD_TIPO'])
				&&	$materia['EVALUACION_COD_TIPO'] == $tipo	)
			{
				$evaluacion_id = $materia[catalogo::id];
				$html = kernel::load_template('lista_materias/parcial.twig')->render(array(
					'nombre' => $materia['EVALUACION_NOMBRE'],
					'tipo' => $tipo,
					'porcentaje' => 0,
					'cant_inscriptos' => $materia['CANT_INSCRIPTOS'],
					'url_listar' => kernel::vinculador()->crear('notas_parciales', 'listar', $evaluacion_id),
					'url_editar' => kernel::vinculador()->crear('notas_parciales', 'editar', $evaluacion_id),
					'evaluacion_id' => $evaluacion_id,
					'fecha' => date("d/m/Y H:i", strtotime($materia['EVALUACION_FECHA']))
				));

				$pagelet = $this->vista()->pagelet('lista_materias');
				$materias = $pagelet->get_lista_materias();

				$completo = false;
				if (isset($materias[$materia['MATERIA']]['COMISIONES'][$materia['COMISION']]['COMPLETA']))
					$completo = $materias[$materia['MATERIA']]['COMISIONES'][$materia['COMISION']]['COMPLETA'];
				$this->render_ajax('', array('success' => true, 'html' => $html, 'completo' => $completo));
			}
		}
	}

}
?>