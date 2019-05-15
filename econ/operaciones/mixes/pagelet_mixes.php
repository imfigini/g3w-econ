<?php
namespace econ\operaciones\mixes;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\modelo\datos\catalogo;

class pagelet_mixes extends pagelet {
	
        public function get_nombre()
        {
            return 'mixes';
	}

        function prepare()
        {
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
        }
}
