<?php

namespace econ\operaciones\inicio_alumno;

use kernel\kernel;
use siu\modelo\datos\catalogo;
use siu\guarani;
use kernel\nucleo\controlador as controlador_base;

class controlador extends \siu\operaciones\inicio_alumno\controlador
{
    /**
     *
     * @return \siu\modelo\transacciones\inscripcion_cursada 
     */
    function modelo()
    {
        return null;
    }

    function accion__index()
    {
        $nombre = kernel::persona()->get_nombre();

        $parametros['nro_inscripcion'] = kernel::persona()->get_nro_inscripcion();
        $tiene_noticias = catalogo::consultar('noticias', 'tiene_noticias_por_persona', $parametros);
        $this->vista()->agregar_a_contexto('tiene_noticias', $tiene_noticias);
		$this->vista()->pagelet('lista_encuestas_pendientes')->data['tiene_noticias'] = $tiene_noticias;
    }
	
	function info_periodos_lectivos()
	{
		return catalogo::consultar('unidad_academica', 'periodos_lectivos');
	}
	
	function info_turnos_examen()
	{
		return catalogo::consultar('unidad_academica', 'info_turnos_examen');			
	}
	
	function info_encuestas_pendientes()
	{
		$encuestas_pendientes = kernel::persona()->get_encuestas_pendientes();
		// corte
		$corte = array();
		foreach($encuestas_pendientes as $encuesta){
			$corte[$encuesta['id_grupo']]['desplegar_encuestas'] = $encuesta['desplegar_encuestas'];
			$encuesta['link_encuesta'] = kernel::vinculador()->crear('encuestas_kolla', 'index', $encuesta['id']);
			if ($encuesta['desplegar_encuestas'] == 1){
				$corte[$encuesta['id_grupo']]['titulo_grupo'] = $encuesta['titulo_grupo'];
				$corte[$encuesta['id_grupo']]['encuestas'][] = $encuesta;
			} else {
				$corte[$encuesta['id_grupo']] = $encuesta;
			}
			
		}
		return $corte;
	}
	
    function info_lista_noticias(){
        $parametros['nro_inscripcion'] = kernel::persona()->get_nro_inscripcion();
        return catalogo::consultar('noticias', 'noticias_por_persona', $parametros);
	}
	

	// Funcionalidad para aceptar los terminos y condiciones

	function is_periodo_integrador()
	{
		$periodo_integrador = catalogo::consultar('terminos_condiciones', 'periodo_integrador', null);
		$fecha_inicial = strtotime ( '-5 day' , strtotime ( $periodo_integrador['FECHA_PRIMERA'] ) ) ;
		$fecha_inicial = date ( 'Y-m-d' , $fecha_inicial);

		$fecha_final = strtotime ( $periodo_integrador['FECHA_ULTIMA'] );
		$fecha_final = date ( 'Y-m-d' , $fecha_final);

		$hoy = date('Y-m-d');

		// kernel::log()->add_debug('fecha_inicial', $fecha_inicial);
		// kernel::log()->add_debug('fecha_final', $fecha_final);
		// kernel::log()->add_debug('hoy', $hoy);
		if ($fecha_inicial <= $hoy && $hoy <= $fecha_final)	{
			return true;
		}
		return false;
	}

	function acepto_terminos_y_condiciones()
	{
		$parametros['legajo'] = kernel::persona()->get_id_legajo_activo();
		$acepto = catalogo::consultar('terminos_condiciones', 'acepto_terminos_y_condiciones', $parametros);
		if (isset($acepto['FECHA'])) {
			return true;
		}
		return false;
	}  

	function accion__grabar_acept_term_cond()
	{
		$parametros['legajo'] = kernel::persona()->get_id_legajo_activo();
		$resultado = catalogo::consultar('terminos_condiciones', 'grabar_acept_term_cond', $parametros);
		kernel::log()->add_debug('entro a grabar', $resultado);

	}
	// function info_lista_term_cond(){

    //     $parametros['nro_inscripcion'] = kernel::persona()->get_nro_inscripcion();
	// 	$pp = catalogo::consultar('persona', 'datos_basicos', $parametros);
	// 	kernel::log()->add_debug('pp', $pp);
	// 	return $pp;
	// 	//catalogo::consultar('noticias', 'noticias_por_persona', $parametros);
    // }
}
?>
