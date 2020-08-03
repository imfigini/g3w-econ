<?php
namespace econ\modelo\transacciones;

use kernel\kernel;
use siu\modelo\datos\catalogo;
use siu\modelo\entidades\alumno_foto;
use siu\errores\error_guarani_procesar_renglones;

class carga_evaluaciones_parciales extends \siu\modelo\transacciones\carga_evaluaciones_parciales
{
    function info__tipo_evaluacion()
    {
        return catalogo::consultar('carga_evaluaciones_parciales', 'listado_tipo_evaluacion_econ',null);
    }
    
    //---------------------------------------------
    //	LISTA EVALUACIONES
    //---------------------------------------------
    function info__lista_evaluaciones()
    {
        $parametros = array('legajo' => kernel::persona()->get_legajo_docente());
        return catalogo::consultar('carga_evaluaciones_parciales', 'listado_evaluaciones_parciales_econ', $parametros);
    }
    
    //---------------------------------------------
    //	CARGA NOTAS
    //---------------------------------------------	

    function info__escala_notas($escala_notas)
    {
        $parametros = array('escala_notas' => $escala_notas);
        //return catalogo::consultar('carga_evaluaciones_parciales', 'escala_notas', $parametros);
        return catalogo::consultar('sistema', 'escala_notas_econ', $parametros);
	}
	
	function info__evaluacion_detalle($seleccion)
	{
		// Control derechos
		$parametros = array();
		kernel::log()->add_debug('info__evaluacion_detalle_seleccion: ', $seleccion);
		$datos_eval = $this->get_evaluacion_enviada($seleccion);
		$parametros['comision'] = $datos_eval['COMISION'];
        $parametros['evaluacion'] = $datos_eval['EVALUACION'];
		
		$datos = catalogo::consultar('carga_evaluaciones_parciales', 'evaluacion_detalle', $parametros);

		$nuevo = array();
		//kernel::log()->add_debug('evaluacion_detalle', $datos);
		if (count($datos) > 0) {
			$materia_in_mix = catalogo::consultar('mixes', 'pertenece_mix_cincuentenario', Array('materia' => $datos[0][1]));
		}

		$id = 0;
		foreach($datos as $dato) {

			$param = Array('_ua' => $dato[0],
							'anio_academico' => $dato[3],
							'periodo' => $dato[4],
							'legajo' => $dato[16],
							'carrera' => $dato[14],
							'comision' => $dato[5],
							'evaluacion' => $dato[9],
							'materia' => $dato[1] );
			if ($materia_in_mix) {							
				$puede_rendir = $this->puede_rendir_instancia($param);
				if (!$puede_rendir) {
					continue;
				}
			}
			$nuevo[$id]['CALIDAD'] = $dato[12];
			$nuevo[$id]['ESTADO'] = $dato[13];
			$nuevo[$id]['CARRERA'] = $dato[14];
			$nuevo[$id]['LEGAJO'] = $dato[16];
			$nuevo[$id]['NOMBRE'] = $dato[17];
			$nuevo[$id]['FECHA_HORA'] = $dato[18];
			$nuevo[$id]['NOTA'] = $dato[19];
			$nuevo[$id]['RESULTADO'] = $dato[20];
			$nuevo[$id]['CORREGIDO_POR'] = $dato[21];
			$nuevo[$id]['OBSERVACIONES'] = $dato[22];
			$nuevo[$id]['ID_IMAGEN'] = $dato[23]; //-- EN QUE POSICION VIENE?
			if (!empty($nuevo[$id]['ID_IMAGEN'])) {
				$nuevo[$id]['URL_IMAGEN'] = alumno_foto::url_imagen($nuevo[$id]['ID_IMAGEN']);
				$nuevo[$id]['URL_IMAGEN_GRANDE'] = alumno_foto::url_imagen($nuevo[$id]['ID_IMAGEN'], alumno_foto::TAMANIO_GRANDE);
			} else {
				$nuevo[$id]['URL_IMAGEN'] = kernel::vinculador()->vinculo_recurso('img/iconos/mm.png');
				$nuevo[$id]['URL_IMAGEN_GRANDE'] = kernel::vinculador()->vinculo_recurso('img/iconos/mm_grande.png');
			}
			$id++;
		}
		kernel::log()->add_debug('info__evaluacion_detalle', $nuevo);
		return $nuevo;	
	}

	/* parametros: _ua, anio_academico, periodo, legajo, carrera, comision, evaluacion, materia */
    function puede_rendir_instancia($parametros)
    {
		/* Instancias de evaluacion: 
			22: 1er Parcial
			23: 2do Parcial
			24: Recuperatorio Global
			14: Integrador
		*/
		
		if ($parametros['anio_academico'] < 2019) {
			return true;
		}
		//Las instancias de evaluacion parcial 1 y 2 las pueden rendir todos. Las notas de TP se cargan para todos tambi?n
		if ($parametros['evaluacion'] == 22 || $parametros['evaluacion'] == 23 || $parametros['evaluacion'] == 15) {
			return true;
		}
		$parametros['porc_asist'] = 60;
		$tiene_asistencia = catalogo::consultar('carga_evaluaciones_parciales', 'tiene_asistencia', $parametros);
		
		if (!$tiene_asistencia) {
			return false;
		}

		$asistio_parcial1 = catalogo::consultar('carga_evaluaciones_parciales', 'asistio_evaluacion', Array('legajo' => $parametros['legajo'],
																										'comision' => $parametros['comision'],
																										'evaluacion' => 22));
		$asistio_parcial2 = catalogo::consultar('carga_evaluaciones_parciales', 'asistio_evaluacion', Array('legajo' => $parametros['legajo'],
																										'comision' => $parametros['comision'],
																										'evaluacion' => 23));
		if (!$asistio_parcial1 && !$asistio_parcial2) {
			return false;
		}				
		
		// Integrador 
		if ($parametros['evaluacion'] == 14) 
		{
			if (!$asistio_parcial1 || !$asistio_parcial2) {
				return false;
			}
			return $this->puede_rendir_integrador($parametros);
		}

		// Recuperatorio Global
		if ($parametros['evaluacion'] == 24) 
		{
			return $this->puede_rendir_recup_global($parametros);
		}
		return true;
	}

	function puede_rendir_integrador($parametros)
	{
		$fecha = catalogo::consultar('generales', 'get_fecha_ctr_correlativas', $parametros);
		$parametros['fecha'] = date("d-m-Y", strtotime($fecha['FECHA']));
		$tiene_correlativas_cumplidas = catalogo::consultar('carga_evaluaciones_parciales', 'tiene_correlativas_cumplidas', $parametros);
		if (!($tiene_correlativas_cumplidas[0] == 1)) {
			return false;
		}
		$nota_parcial1 = catalogo::consultar('carga_evaluaciones_parciales', 'get_nota_parcial', Array('legajo' => $parametros['legajo'], 
																									'comision' => $parametros['comision'], 
																									'evaluacion' => 22) );
		$nota_parcial2 = catalogo::consultar('carga_evaluaciones_parciales', 'get_nota_parcial', Array('legajo' => $parametros['legajo'], 
																									'comision' => $parametros['comision'], 
																									'evaluacion' => 23) );
		$is_promo_directa = catalogo::consultar('prom_directa', 'is_promo_directa', $parametros);
		if ($is_promo_directa)
		{
			if ($nota_parcial1['NOTA'] >= 8 && $nota_parcial2['NOTA'] >= 8)	{
				return false;
			}
		}
		$prom_parciales = ( $nota_parcial1['NOTA'] + $nota_parcial2['NOTA'] ) / 2;
		if ($prom_parciales < 6) {
			return false;
		}

		//Si se está dentro del período del integrador, verifica que haya aceptado los términos y condiciones para rendir en la modalidad virtual
		$periodo_integrador_actual = catalogo::consultar('terminos_condiciones', 'periodo_integrador', null);
		if (isset($periodo_integrador_actual['FECHA_PRIMERA']) && isset($periodo_integrador_actual['FECHA_ULTIMA'])) {
			$acepto_term_y_cond = catalogo::consultar('terminos_condiciones', 'acepto_terminos_y_condiciones', $parametros);
			if (!isset($acepto_term_y_cond['FECHA'])) {
				return false;
			}
		}

		return true;
	}
    
	function puede_rendir_recup_global($parametros)
	{
		$nota_parcial1 = catalogo::consultar('carga_evaluaciones_parciales', 'get_nota_parcial', Array('legajo' => $parametros['legajo'], 
																									'comision' => $parametros['comision'], 
																									'evaluacion' => 22) );
		$nota_parcial2 = catalogo::consultar('carga_evaluaciones_parciales', 'get_nota_parcial', Array('legajo' => $parametros['legajo'], 
																									'comision' => $parametros['comision'], 
																									'evaluacion' => 23) );

		//Si estuvo ausente en ambos parciales, no puede recuperar
		if ( (empty($nota_parcial1) or $nota_parcial1['RESULTADO'] == 'U')
				and (empty($nota_parcial2) or $nota_parcial2['RESULTADO'] == 'U') ) {
			return false;
		}
		//Si estuvo ausente en un parcial, si puede recuperar
		if ( (empty($nota_parcial1) or empty($nota_parcial2))
				or ($nota_parcial1['RESULTADO'] == 'U' or $nota_parcial2['RESULTADO'] == 'U') ) {
			return true;
		}
		//Si estuvo en los 2 parciales, y el promedio es >= 4 ya aprobo, no recupera
		$prom_parciales = ( $nota_parcial1['NOTA'] + $nota_parcial2['NOTA'] ) / 2;
		if ($prom_parciales >= 4) {
			return false;
		}
		return true;
	}

	function info__evaluacion_cabecera($seleccion)
	{
		// Control derechos
		$parametros = array();
		$datos_eval = $this->get_evaluacion_enviada($seleccion);
		$parametros['comision'] = $datos_eval['COMISION'];
		$parametros['evaluacion'] = $datos_eval['EVALUACION'];
        $resultado = catalogo::consultar('carga_evaluaciones_parciales', 'evaluacion_cabecera',$parametros);
        //kernel::log()->add_debug('cabecera: ', $resultado);
        if ($resultado['EVALUACION'] == '15') {
            $resultado['FECHA_HORA'] = NULL;
        }
        return $resultado;
	}	

	function evt__procesar_evaluaciones($seleccion, $renglones)
	{
		$datos = $this->info__evaluacion_cabecera($seleccion);		
		$error = new error_guarani_procesar_renglones('Error cargando notas');
		
        foreach($renglones as $k=>$renglon) {
			try {
				$renglon['CARRERA'] = $renglon['CARRERA'];
				$renglon['LEGAJO'] = $renglon['LEGAJO'];
				$renglon['COMISION'] = $datos['COMISION'];
				$renglon['EVALUACION'] = $datos['EVALUACION'];
				if ($datos['EVALUACION'] != 15) {		//Sólo si no es TP 
					$renglon['FECHA_HORA'] = $datos['FECHA_HORA'];
				}
				$renglon['ESCALA_NOTAS'] = $datos['ESCALA_NOTAS'];	
								
                kernel::log()->add_debug('evt__procesar_evaluaciones renglon '.$k,$renglon);
                $ok = catalogo::consultar('carga_evaluaciones_parciales', 'guardar_renglon', $renglon);
                if($ok[0]) {
                    $parametros = array();
                    $parametros['carrera'] = $renglon['CARRERA'];
                    $parametros['legajo'] = $renglon['LEGAJO'];
                    $parametros['comision'] = $renglon['COMISION'];
					$clase = catalogo::consultar('carga_evaluaciones_parciales', 'get_clase', Array('comision'=>$renglon['COMISION'], 'fecha'=>substr($renglon['FECHA_HORA'], 0, 10)));
					//kernel::log()->add_debug('$clase renglon ', $clase);
					//Si existe la clase y no es TP debe registrar la asistencia
                    if (isset($clase) && isset($clase['CLASE']) && $datos['EVALUACION'] != 15) {
						$parametros['clase'] = $clase['CLASE'];
						if ($renglon['NOTA'] > 0) {
							$parametros['cant_inasist'] = 0;
							$parametros['justific'] = 0;
						} else {
							$parametros['cant_inasist'] = 1;
							$parametros['justific'] = 0;
						}
						catalogo::consultar('carga_asistencias', 'guardar', $parametros);
					}
                }
				if($ok[0]!=1) {
					$error->add_renglon($renglon['RENGLON'], util::mensaje($ok[1]), $renglon);
					kernel::log()->add_debug("RENG_ERROR", $ok);
					kernel::log()->add_error(new error_guarani($ok[1]));	
				}
			} catch (error_kernel $e) {
				$error->add_renglon($renglon['RENGLON'], $e->getMessage(), $renglon);
				kernel::log()->add_error($e);
			}
		}
		if($error->hay_renglones()) {
			throw $error;
		}
    }
}
