<?php
namespace econ\operaciones\mixes;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
use siu\modelo\guarani_notificacion;

class controlador extends controlador_g3w2
{       
    protected $carrera; 
    
    function modelo()
    {
        return null;
    }

    function accion__index()
    {
    }
    
    function accion__modificar()
    {
        if (kernel::request()->isPost()) {
            
            var_dump('Entr� en: accion__modificar()'); //die;
            
            $this->carrera = $this->validate_param('carrera', 'post', validador::TIPO_TEXTO);     
//            var_dump($pp);
            
            /*
             * $parametros = $this->get_parametros_grabar();
            catalogo::consultar('coord_materia', 'set_coordinador', $parametros);
               
            $this->set_anio_academico($parametros['anio_academico_hash']);
            $this->set_periodo($parametros['periodo_hash']);
             * 
             */
        }        
    }
    
    function get_carrera()
    {
        return $this->carrera;
    }
    
    function get_clase_vista()
    {
        //console.log($this);
        switch ($this->accion) {
            case 'modificar': 
                    return 'vista_modificar';
            default: 
                    return 'vista';
        }
    }
}
