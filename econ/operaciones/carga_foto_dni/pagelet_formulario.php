<?php
namespace econ\operaciones\carga_foto_dni;

use kernel\interfaz\pagelet;
use kernel\kernel;

class pagelet_formulario extends pagelet {
	
    public function get_nombre()
    {
        return 'formulario';
    }
    

    function get_mensaje()
	{
        return $this->controlador->get_mensaje();
	}
        
    function get_mensaje_error()
	{
        return $this->controlador->get_mensaje_error();
    }
    
    function get_foto_dni_cargada()
	{
        return $this->controlador->get_foto_dni_cargada();
    }
    
    function prepare() 
    {
        $this->data['foto_contenido'] = $this->get_foto_dni_cargada();
        
        $this->data['mensaje'] = $this->get_mensaje();
        $this->data['mensaje_error'] = $this->get_mensaje_error();
        
        $link_form = kernel::vinculador()->crear('carga_foto_dni', 'grabar');
        $this->data['form_url'] = $link_form;     
    }  

}
?>