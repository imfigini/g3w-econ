<?php
namespace econ\operaciones\mixes;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\modelo\datos\catalogo;

class pagelet_modificar extends pagelet {
	
        public function get_nombre()
        {
            return 'modificar';
	}
        
        function prepare()
        {
            $carrera =  $this->controlador->get_carrera();
            $datos = array();
            for ($anio = 1; $anio<=5; $anio++)
            {
                $mixs = array('A', 'B');
                foreach ($mixs AS $mix)
                {
                    $parametros = array('carrera' => $carrera, 
                                        'anio_de_cursada' => $anio, 
                                        'mix' => $mix);
                    $materias_del_mix = catalogo::consultar('mixes', 'get_materias_mix', $parametros);
                    $dato['anio']= $anio;
                    $dato['mix'] = $mix;
                    $dato['materias'] = $materias_del_mix;
                    $datos[] = $dato;
                }
            }
            $this->data['datos'] = $datos;
            $this->data['carrera'] = $carrera;
            $carrera_nombre = catalogo::consultar('mixes', 'get_carrera_nombre', array('carrera' => $carrera));
            $this->data['carrera_nombre'] = $carrera_nombre[0]['CARRERA_NOMBRE'];
            
            $this->data['materias_sin_mix'] = catalogo::consultar('mixes', 'get_materias_sin_mix', array('carrera' => $carrera));
                    
            $link_form_del = kernel::vinculador()->crear('mixes', 'eliminar');
            $this->data['form_url_del'] = $link_form_del;
            
            $link_form_add = kernel::vinculador()->crear('mixes', 'agregar');
            $this->data['form_url_add'] = $link_form_add;
        }
}
