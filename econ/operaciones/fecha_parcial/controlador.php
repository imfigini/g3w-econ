<?php
namespace econ\operaciones\fecha_parcial;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use kernel\util\validador;
use siu\modelo\datos\catalogo;
use kernel\error_kernel_validacion;

class controlador extends controlador_g3w2
{
	protected $form_builder;
	protected $datos_filtro;
	function modelo()
	{
		return null;
	}
	
	function accion__index()
	{
        $form = $this->get_form();

        if (!empty($_GET['formulario_filtro'])) 
        {
			$this->recuperar_filtro();
            $filtros = $form->get_datos();

            $carrera = "";
			if($filtros['carrera'] != "") $carrera = $this->decodificar_carrera($filtros['carrera']);
            
            $plan = "";
            if($carrera && ($filtros['plan'] != "")) $plan = $this->decodificar_plan($filtros['plan'], $carrera);
            
            $anio_cursada = "";
            if ($filtros['anio_cursada'] > 0) {
                $anio_cursada = $filtros['anio_cursada'];
            }

            $materia = "";
            $materia_descr = "";
            if($filtros['materia'] != ""){
                list($materia, $materia_descr) = $this->decodificar_materia($filtros['materia'], $carrera, $plan);
            }
            $this->datos_filtro["materia_descr"] = $materia_descr;
            $form->set_datos(array("materia_descr" => $materia_descr));
            
			$materias = array();

            $parametros = array(
                'carrera'   => $carrera,
                'plan'   => $plan,
                'anio_cursada' => $anio_cursada,
                'materia'   => $materia
            );

            $datos = catalogo::consultar('usuario_anonimo', 'fechas_parciales_usuario_anonimo', $parametros);
            // print_r('<br>');
            // print_r($datos);

            //Hago corte de control por materia
            $cant = count($datos);
            $i = 0;
            while ($i < $cant)
            {
                $dato = $datos[$i];
                $materias[$dato['MATERIA']]['MATERIA_NOMBRE'] = $dato['NOMBRE_MATERIA'];
                $materias[$dato['MATERIA']]['MATERIA'] = $dato['MATERIA'];

                $materias[$dato['MATERIA']]['comisiones'][$dato['COMISION']]['COMISION'] = $dato['COMISION'];
                $materias[$dato['MATERIA']]['comisiones'][$dato['COMISION']]['COMISION_NOMBRE'] = $dato['COMISION_NOMBRE'].' ('.$dato['COMISION'].')';

                //Hago corte de control por comisión
                $j = $i;
                $parciales = array();
                while ($j < $cant) 
                {
                    $dato2 = $datos[$j];
                    if ($dato['MATERIA'] == $dato2['MATERIA'] && $dato['COMISION'] == $dato2['COMISION'])
                    {
                        $parciales[] = array(
                            'EVAL_PARCIAL' => $dato2['EVAL_PARCIAL'],
                            'FECHA_PARCIAL' => $dato2['FECHA_PARCIAL'],
                            'HORA_INICIO' => $dato2['HORA_INICIO'],
                            'AULA' => $dato2['AULA'],
                            'EDIFICIO' => $dato2['EDIFICIO']
                        );
                        $j++;
                    }
                    else {
                        break;
                    }

                }
                $i = $j;
                $materias[$dato['MATERIA']]['comisiones'][$dato['COMISION']]['parciales'] = $parciales;
            }

			$this->vista()->pagelet('filtro')->data['cuadro'] = $materias;
		}
	}
	
	function decodificar_carrera($carrera_hash) {
		$datos = catalogo::consultar('plan_estudios', 'carreras');
		if (!empty($datos)) {
			foreach($datos as $value){
				if ($value['_ID_'] == $carrera_hash){
					return $value['CARRERA'];
				}
			}
		}
		return false;
	}
    
    function decodificar_plan($plan_hash, $carrera) {
        $datos = catalogo::consultar('plan_estudios', 'planes', array('carrera' => $carrera));
        if (!empty($datos)) {
            foreach($datos as $value){
                if ($value['_ID_'] == $plan_hash){
                    return $value['PLAN'];
                }
            }
        }
        return false;
    }

    function decodificar_materia($materia_hash, $carrera, $plan) {
        $datos = catalogo::consultar('usuario_anonimo', 'lista_materias_carrera', array('carrera' => $carrera, 'plan' => $plan, 'materia' => ""));
        if (!empty($datos)) {
            foreach($datos as $value){
                if ($value['_ID_'] == $materia_hash){
                    return array($value['MATERIA'], $value['NOMBRE']);
                }
            }
        }
        return false;
    }
	
	/**
	 * Solo la vista 'vista' tiene el form. Falla en otro caso
	 */
	private function recuperar_filtro()
	{
		$form = $this->get_form();
		if (!$form->procesar('GET')) {
			throw new error_kernel_validacion("Error al validar el filtro");
		}

		//si no se llega aca, aplican los valores default
		$this->datos_filtro = $form->get_datos();
	}
	
	/**
	 * @return guarani_form
	 */
	function get_form()
	{
		return $this->get_form_builder()->get_formulario();
	}

	function get_vista_form(){
		return $this->get_form_builder()->get_vista();
	}
	
	/**
	* @return builder_form_filtro
	*/
	function get_form_builder()
	{
		// se hace esta función para no construir cada vez el builder
		if (! isset($this->form_builder)) {
				$this->form_builder = kernel::localizador()->instanciar('operaciones\fecha_parcial\filtro\builder_form_filtro');
		}

		return $this->form_builder;
	}

    function accion__buscar_planes(){

        $carrera_hash = $this->validate_param('carrera', 'get', validador::TIPO_ALPHANUM);
        $carrera = "";
        if($carrera_hash != "") $carrera = $this->decodificar_carrera($carrera_hash);
        $datos = array();
        $datos['cod'] = 1;
        $datos['planes'] = catalogo::consultar('plan_estudios', 'planes', array('carrera' => $carrera));
        $this->render_raw_json($datos);

    }

    function accion__buscar_materias() {
        $term = $this->validate_param('term', 'get', validador::TIPO_TEXTO);
        $term = utf8_decode($term);
        kernel::log()->add_debug('term: ', $term);
        
        $carrera_hash = $this->validate_param('carrera', 'get', validador::TIPO_ALPHANUM, array('default' => ''));
        $carrera = "";
        if($carrera_hash != "") $carrera = $this->decodificar_carrera($carrera_hash);

        $plan_hash = $this->validate_param('plan', 'get', validador::TIPO_ALPHANUM, array('default' => ''));
        $plan = "";
        if($carrera && ($plan_hash != "")) $plan = $this->decodificar_plan($plan_hash, $carrera);

        $anio_cursada = $this->validate_param('anio_cursada', 'get', validador::TIPO_ALPHANUM, array('default' => ''));

        $paquete = array();
        if (!is_null($term)){

            $parametros = array(    'carrera' => $carrera,
                                    'plan' => $plan,
                                    'anio_cursada' => $anio_cursada,
                                    'materia' => $term);
            $datos = catalogo::consultar('usuario_anonimo', 'lista_materias_carrera_x_anio', $parametros);
            foreach ($datos as $dato){
                $paquete[] = array(
                    'id' => $dato['_ID_'],
                    'label' => $dato['NOMBRE_MATERIA']
                );
            }

        }

        $this->render_raw_json($paquete);
    }
}
