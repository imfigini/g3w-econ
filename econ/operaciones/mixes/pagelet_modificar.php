<?php
namespace econ\operaciones\mixes;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\modelo\datos\catalogo;

class pagelet_modificar extends pagelet {
//class pagelet_mixes extends \siu\operaciones\_comun\operaciones\reporte\pagelet_reporte {
	
        public function get_nombre()
        {
            return 'modificar';
	}

        function prepare()
        {
//            $this->data['mensaje'] = 'modif';
            var_dump('Entró en: prepare() del pagelet_modificar'); //die;
            $pp          =  $this->controlador->get_carrera();
            //$this->validate_param('carrera', 'post', validador::TIPO_TEXTO);   
            var_dump(' - carrera: '.$pp);
            /*
            $carreras = catalogo::consultar('mixes', 'get_carreras_grado', null);
            $datos = $carreras;
            
            foreach ($carreras AS $c=>$carrera)
            {
                $anios = catalogo::consultar('mixes', 'get_anios_de_cursada_con_mix', array('carrera' => $carrera['CARRERA']));
                $datos[$c]['ANIOS'] = $anios;
                foreach($anios AS $a=>$anio)
                {
                    $mixs = catalogo::consultar('mixes', 'get_mixes_del_anio', array('carrera' => $carrera['CARRERA'], 'anio_de_cursada' => $anio['ANIO_DE_CURSADA']));
                    $datos[$c]['ANIOS'][$a]['MIXES'] = $mixs;
                    
                    foreach ($mixs AS $m=>$mix)
                    {
                        $parametros = array('carrera' => $carrera['CARRERA'], 
                                            'anio_de_cursada' => $anio['ANIO_DE_CURSADA'], 
                                            'mix' => $mix['MIX']);
                        $materias_del_mix = catalogo::consultar('mixes', 'get_materias_mix', $parametros);
                        $datos[$c]['ANIOS'][$a]['MIXES'][$m]['MATERIAS'] = $materias_del_mix;
                    }
                    
                }
           }
            //print_r($datos); die;
            $this->data['datos'] = $datos;
            
            $link_form = kernel::vinculador()->crear('mixes', 'modificar');
            $this->data['form_url'] = $link_form;        
             * 
             */
        }
        /*
	public function c
	{   
            $operacion = kernel::ruteador()->get_id_operacion();
            $this->add_var_js('url_buscar_periodos', kernel::vinculador()->crear($operacion, 'buscar_periodos'));

//            $this->add_var_js('msg_guardado_exitoso', trans('datos_censales.guardado_exitoso'));
//            $this->add_var_js('msg_error_al_guardar', trans('datos_censales.error_al_guardar'));
           
            $periodo_hash = $this->get_periodo();
            $anio_academico_hash = $this->get_anio_academico();
            $form = $this->get_form_builder();
            $form->set_anio_academico($anio_academico_hash);
            $form->set_periodo($periodo_hash);
            
            $this->data['mensaje'] = $this->get_mensaje();
            
            $this->add_var_js('periodo_hash', $periodo_hash);
            $this->add_var_js('anio_academico_hash', $anio_academico_hash);        
            $this->data['periodo_hash'] = $periodo_hash;
            $this->data['anio_academico_hash'] = $anio_academico_hash;

            $materias = $this->controlador->get_materias_cincuentenario();
            
            $datos = array();
            $cant = count($materias);
            for ($i=0; $i<$cant; $i++)
            {
                $comisiones = $this->controlador->get_comisiones_promo($anio_academico_hash, $periodo_hash, $materias[$i]['MATERIA']);
                if (count($comisiones) > 0)
                {
                    $materias[$i]['comisiones'] = $comisiones;
                    $datos[] = $materias[$i];
                }
            }
            
            $this->data['datos'] = $datos;
	        
            $link_form_comision = kernel::vinculador()->crear('definicion_cursos', 'grabar_comision');
            $this->data['form_url_comision'] = $link_form_comision;     
            
            $link_form_materia = kernel::vinculador()->crear('definicion_cursos', 'grabar_materia');
            $this->data['form_url_materia'] = $link_form_materia;     
            
        }
         * 
         */
}
