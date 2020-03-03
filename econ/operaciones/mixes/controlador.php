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
    protected $mensaje_error;
    
    function modelo()
    {
       return null;
    }

    function accion__index()
    {

    }
    
    function accion__modificar()
    {
        if (kernel::request()->isPost()) 
        {
            $this->carrera = $this->validate_param('carrera', 'post', validador::TIPO_TEXTO);     
        }        
    }
    
    function accion__eliminar()
    {
        if (kernel::request()->isPost()) 
        {
            $parametros = $this->get_parametros_eliminar(); 
            try
            {
                kernel::db()->abrir_transaccion();
                catalogo::consultar('mixes', 'del_materia_de_mix', $parametros);
                kernel::db()->cerrar_transaccion();
                $this->set_carrera($parametros['carrera']);         
            }
            catch (error_guarani $e)
            {
                $msj = $e->getMessage();
                kernel::db()->abortar_transaccion($msj);
                $this->set_carrera($parametros['carrera']);
                $this->set_mensaje_error($msj);
            }
        }        
    }
    
    private function get_parametros_eliminar()
    {
        $datos = array();
        $datos['carrera'] = $this->validate_param('carrera', 'post', validador::TIPO_TEXTO);  
        $datos['anio'] = $this->validate_param('anio', 'post', validador::TIPO_TEXTO);     
        $datos['mix'] = $this->validate_param('mix', 'post', validador::TIPO_TEXTO);     
        $datos['materia'] = $this->validate_param('materia_del', 'post', validador::TIPO_TEXTO);     
//        print_r('<br>Datos: ');
//        print_r($datos);
        return $datos;
    }
    
    function accion__agregar()
    {
        $carrera = $this->get_carrera();
//        print_r($carrera);
        if (kernel::request()->isPost()) 
        {
            $parametros = $this->get_parametros_agregar(); 
            try
            {
                if (isset($parametros['materia']) && $parametros['materia'] <> '0')
                {
                    kernel::db()->abrir_transaccion();
                    catalogo::consultar('mixes', 'add_materia_a_mix', $parametros);
                    kernel::db()->cerrar_transaccion();
                    
                }
            }
            catch (error_guarani $e)
            {
                $msj = $e->getMessage();
                kernel::db()->abortar_transaccion($msj);
                $this->set_mensaje_error($msj);
            }
            $this->set_carrera($parametros['carrera']);
        }  
        else
        {
            $this->set_carrera($carrera);
        }
    }
    
    private function get_parametros_agregar()
    {
        $datos = array();
        $datos['carrera'] = $this->validate_param('carrera', 'post', validador::TIPO_TEXTO);  
        $datos['anio'] = $this->validate_param('anio', 'post', validador::TIPO_TEXTO);     
        $datos['mix'] = $this->validate_param('mix', 'post', validador::TIPO_TEXTO);     
        $datos['materia'] = $this->validate_param('materia_add', 'post', validador::TIPO_TEXTO); 

        $plan_ver = catalogo::consultar('mixes', 'get_plan_y_version_actual_de_materia', $datos);
        $datos['plan'] = $plan_ver[0]['PLAN'];
        $datos['version'] = $plan_ver[0]['VERSION'];
        return $datos;
    }

    function accion__volver()
    {
        //print_r('entro x accion__volver()');
        $this->accion = 'vista';
    }
    
    function set_carrera($carrera)
    {
        $this->carrera = $carrera;
    }
    
    function get_carrera()
    {
        return $this->carrera;
    }
    
    function set_mensaje_error($mensaje)
    {
        $this->mensaje_error = $mensaje;
    }
    
    function get_mensaje_error()
    {
        return $this->mensaje_error;
    }
    
    function get_clase_vista()
    {
        switch ($this->accion) {
            case 'modificar': 
                    return 'vista_modificar';
            case 'eliminar': 
                    return 'vista_modificar';
            case 'agregar': 
                    return 'vista_modificar';
            default: 
                    return 'vista';
        }
    }
}
