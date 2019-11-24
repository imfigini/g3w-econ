<?php

namespace econ\operaciones\notas_cursada;

//use siu\guarani;
//use siu\operaciones\_comun\controlador_carga_notas;

use kernel\kernel;
//use siu\errores\error_guarani;
//use siu\errores\error_guarani_procesar_renglones;
use kernel\util\validador;
// use siu\modelo\entidades\alumno_foto;
// use \siu\modelo\entidades\parametro;
// use siu\modelo\datos\catalogo;

class controlador extends \siu\operaciones\notas_cursada\controlador
{
	// public $acta, $folio;
	
	// function get_clase_vista()
	// {
	// 	if ($this->accion == 'index') { // se muestra la lista de actas
	// 		return 'vista_actas';
	// 	} else {
	// 		return 'vista_folio';
	// 	}
	// }
	
    // /**
    //  * @return econ\modelo\transacciones\carga_notas_cursada
    //  */
    // function modelo()
    // {
    //     return guarani::carga_notas_cursada();
    // }

    // function accion__index()
    // {
    // }
	
	// function accion__ficha_alumno()
	// {
	// 	$legajo = $this->validate_param('legajo', 'get', validador::TIPO_TEXTO);
	// 	$carrera = $this->validate_param('carrera', 'get', validador::TIPO_TEXTO);
	// 	$comision = $this->validate_param('comision', 'get', validador::TIPO_TEXTO);
	// 	$id = $this->validate_param('id', 'get', validador::TIPO_TEXTO);
	// 	$url_imagen = alumno_foto::url_imagen($id, alumno_foto::TAMANIO_GRANDE);
	// 	$clase = 'operaciones\_comun\pagelets\pagelet_ficha_alumno';
	// 	$pagelet = kernel::localizador()->instanciar($clase, 'ficha_alumno');
	// 	$pagelet->set_data($this->modelo()->info__parciales($carrera, $legajo, $comision));
	// 	$pagelet->set_url_imagen($url_imagen);
	// 	kernel::renderer()->add($pagelet);
	// }
	
	// function accion__edicion()
	// {
	// 	$this->load_params();
		
	// 	$encabezado = $this->modelo()->info__acta_cabecera($this->acta);
	// 	if (!empty($encabezado['ESTADO']) && $encabezado['ESTADO'] != "A"){
	// 		catalogo::limpiar_cache('carga_notas_cursada', 'lista_actas_abiertas_docente', array('nro_inscripcion' => guarani::persona()->get_nro_inscripcion()));
	// 		$url = kernel::vinculador()->crear('acta_cursadas', 'info_acta', array('accion' => $this->acta));
	// 		$this->nuevo_request_url($url);
	// 	}
		
	// 	$renglon = $this->validate_param('renglon', 'get', validador::TIPO_INT, array('default' => ''));
		
	// 	$this->vista()->pagelet('renglones')->set_highlight($renglon);
	// }

	// function accion__info_escala()
	// {
	// 	$escala_id = $this->validate_param(0, 'get', validador::TIPO_INT);
	// 	if($escala_id==4 || $escala_id==6)
	// 		$escala_id = 1;
	// 	$escala_notas = $this->modelo()->info__escala_notas($escala_id);
	// 	$this->render_raw_json($escala_notas);
	// }
	
	// function accion__guardar()
	// {
	// 	try {
	// 		$this->load_params();
	// 		$this->guardar_folio(kernel::request()->getPost('renglones'));
	// 	} catch (\siu\errores\error_guarani_acta_cursada_cerrada $e) {
	// 		kernel::log()->add_error($e);
	// 		$error = new \siu\errores\error_guarani_usuario(kernel::traductor()->trans('acta_cerrada_comunicarse_oficina_alumnos', array(
	// 			'%1%' => kernel::vinculador()->crear('notas_cursada')
	// 		)));
	// 		$error->set_codigo_response(-5);
	// 		throw $error;
	// 	} catch (error_guarani_procesar_renglones $e) {
	// 		kernel::log()->add_debug('error al procesar renglones', $e);
	// 		$this->vista()->pagelet('renglones')->set_error_guarani_procesar_renglones($e);
	// 	}
		
	// 	// Se indica que cuando llegue el pedido ajax a esta acción la respuesta
	// 	// debe ser solamente el contenido del pagelet renglones, no toda la operación
	// 	kernel::renderer()->add($this->vista()->pagelet('renglones'));
	// 	$this->accion__edicion();
	// }
	
	/**
	 * Autocompletado de alumno 
	 */
	// function accion__auto_alumno()
	// {
	// 	$acta = $this->validate_param(0, 'get', validador::TIPO_ALPHANUM);
	// 	$term = $this->validate_param('term', 'get', validador::TIPO_TEXTO);
		
	// 	if (empty($term)) {
	// 		$this->render_raw_json(array());
	// 		return;
	// 	}
		
	// 	$raw_data = $this->modelo()->info__busqueda_alumno($acta, $term);
		
	// 	$data = array();
		
	// 	foreach ($raw_data as $alumno) {
	// 		$data[] = array(
	// 			'id' => kernel::vinculador()->crear('notas_cursada', 'edicion', array(
	// 				0 => $acta, // acta
	// 				1 => $alumno[1], // folio
	// 				'renglon' => $alumno[2] // rengl?n
	// 			)),
	// 			'label' => $alumno[3]
	// 		);
	// 	}
		
	// 	$this->render_raw_json($data);
	// }
	
	/***********************************************************************/
	// UTIL guardado y validación de renglones de folio
	/***********************************************************************/
	
	/**
	 * Busca en el get el acta y el folio
	 */
	// protected function load_params()
	// {
	// 	$this->acta  = $this->validate_param(0, 'get', validador::TIPO_ALPHANUM);
	// 	$this->folio = $this->validate_param(1, 'get', validador::TIPO_INT, array('default' => 1));
	// }
	
	// protected function get_clave_sesion_folio()
	// {
	// 	return "folio[$this->acta,$this->folio]";
	// }
	
	// function get_encabezado()
	// {
	// 	$encabezado = $this->modelo()->info__acta_cabecera($this->acta);
	// 	return $encabezado;
	// }
	
	
	// function get_folio()
	// {
	// 	$renglones = $this->modelo()->info__acta_folio($this->acta, $this->folio);
		
	// 	// se guardan en sesi?n las filas para dp actualizar, ver m?todo guardar folio
	// 	kernel::sesion()->set($this->get_clave_sesion_folio($this->acta, $this->folio), $renglones);
	// 	return $renglones;
	// }
	
	// protected function vacio_a_null($valor)
	// {
	// 	if (trim($valor) == '') {
	// 		return null;
	// 	}
		
	// 	return $valor;
	// }
	
// 	protected function guardar_folio($renglones_post)
// 	{
// 		$renglones_sesion = kernel::sesion()->get($this->get_clave_sesion_folio());
// 		$renglones_modificados = array();
// 		$encabezado = $this->get_encabezado();
// 		$cierre_parcial = $this->cierre_parcial();
// 		$cierre_parcial_modifica_notas = $this->cierre_parcial_modifica_notas();
// 		foreach($renglones_sesion as $id => $fila_sesion) {
// 			$renglon = $fila_sesion['RENGLON'];
// 			if(isset($renglones_post[$renglon])) {
// 				$cambiaron_valores =   ( $renglones_post[$renglon]['nota']			!= $fila_sesion['NOTA'] ) 
// 									|| ( $renglones_post[$renglon]['asistencia']	!= $fila_sesion['ASISTENCIA'] )
// 									|| ( $renglones_post[$renglon]['condicion']		!= $fila_sesion['CONDICION'] )
// 									|| ( $renglones_post[$renglon]['fecha']			!= $fila_sesion['FECHA'] );
			
// //				$hubo_cambios_fila =	$cambiaron_valores 
// //										// acta de promoción cerrada y resultado P (promocionó)
// //										&& ! ($encabezado['ESTADO'] != 'A' && $fila_sesion['RESULTADO'] == 'P')
// //										// parametro cierre_parcial_acta_cursados activado y el alumno ya tiene nota
// //										&& !($cierre_parcial && $fila_sesion['TIENE_NOTA'] == "S");

// 				$hubo_cambios_fila = $cambiaron_valores; 
// 				// acta de promoción cerrada y resultado P (promocionó)
// 				if ($hubo_cambios_fila) {
// 				$hubo_cambios_fila = !($encabezado['PROM_ABIERTA'] == false && $fila_sesion['RESULTADO'] == 'P');
// 				}
// 				// parametro cierre_parcial_acta_cursados activado y hubo cambios el alumno ya tiene nota y permite modificar
// 				// notas de alumnos que estan en cierre parcial.
// 				if ($cierre_parcial && $hubo_cambios_fila && $cierre_parcial_modifica_notas) {
// 				$hubo_cambios_fila = true;
// 				}   
// 				// Si hay cierre parcial y no permito modificar notas en cierres parciales y ya tenia nota cargada, 
// 				// entonces seteo como que no hubo cambios. (aunque no debio haber permitido modificar las filas)
// 				if ($cierre_parcial && !$cierre_parcial_modifica_notas && $fila_sesion['TIENE_NOTA'] == "S") {
// 				$hubo_cambios_fila = false;
// 				}
				
// 				if ($hubo_cambios_fila) {
// 					$renglones_modificados[$id] = $fila_sesion;
// 					$renglones_modificados[$id]['NOTA'] = $this->vacio_a_null($renglones_post[$renglon]['nota']);
// 					$renglones_modificados[$id]['ASISTENCIA'] = $this->vacio_a_null($renglones_post[$renglon]['asistencia']);
// 					$renglones_modificados[$id]['CONDICION'] = $this->vacio_a_null($renglones_post[$renglon]['condicion']);
// 					$renglones_modificados[$id]['FECHA'] = $this->vacio_a_null($renglones_post[$renglon]['fecha']);
// 				}
// 			}
// 		}

	// 	$asentar_notas_actas_regulares = $this->asentar_notas_actas_regulares();
	// 	$param_sistema = array('asentar_notas_actas_regulares' => $asentar_notas_actas_regulares, 'cierre_parcial' => $cierre_parcial, 'cierre_parcial_modifica_notas' => $cierre_parcial_modifica_notas);
		
	// 	if ($renglones_modificados){
	// 		kernel::log()->add_debug('filas__',$renglones_modificados);
	// 		$this->modelo()->evt__procesar_folio($this->acta, $renglones_modificados, $param_sistema);
	// 	}
	// }
	
	// function cierre_parcial()
	// {
	// 	$cierre_parcial = parametro::cierre_parcial_acta_cursados();
	// 	return $cierre_parcial == 'S';
	// }
	
	// function asentar_notas_actas_regulares()
	// {
	// 	return parametro::asentar_notas_actas_regulares();
	// }
	
	// function cierre_parcial_modifica_notas()
	// {
	// 	return  parametro::cierre_parcial_modifica_notas() == 'S';
	// }

	/**
	 * Autocalcular acta
	 */
	function accion__autocalcular()
	{
		
		$comision = $this->validate_param('comision', 'get', validador::TIPO_ALPHANUM);
		$legajo = $this->validate_param('legajo', 'get', validador::TIPO_ALPHANUM);

		$resultado = $this->modelo()->info__get_posible_nota_alumno($comision, $legajo);

		//$resultado = Array('nota'=>7, 'resultado'=>'P', 'condicion'=>'5', 'asistencia'=>80, 'estado'=>'falta_tp');
		// $resultado = Array('nota'=>7, 'resultado'=>'P', 'condicion'=>'5', 'asistencia'=>80, 'estado'=>'va_integ');
		// if ($legajo == 'FCE-757138' || $legajo == 'FCE-713203') {
		// 	$resultado = Array('nota'=>5, 'resultado'=>'A', 'condicion'=>'4', 'asistencia'=>60, 'estado'=>'listo');
		// }
		// if ($legajo == 'FCE-766033') {
		// 	$resultado = Array('nota'=>'', 'resultado'=>'U', 'condicion'=>'2', 'asistencia'=>40, 'estado'=>'abandono');
		// }
		// if ($legajo == 'FCE-731811' || $legajo == 'FCE-682539') {
		// 	$resultado = Array('nota'=>3, 'resultado'=>'R', 'condicion'=>'3', 'asistencia'=>60, 'estado'=>'va_recup');
		// }
		// print_r('$resultado ');
		// print_r($resultado);

		kernel::log()->add_debug('accion__autocalcular $resultado', $resultado);
		if ($resultado) {
			$this->render_raw_json($resultado);
		} else {
			$this->render_raw_json(Array());
		}
	}

}

?>
