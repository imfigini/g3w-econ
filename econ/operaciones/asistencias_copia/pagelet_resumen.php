<?php
namespace econ\operaciones\asistencias;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\modelo\datos\catalogo;
use siu\operaciones\_comun\operaciones\reporte\generador_pdf;

class pagelet_resumen extends pagelet
{
    function get_nombre()
    {
        return 'resumen';
    }
	
	function get_js_files()
	{
		$js = parent::get_js_files();
		$archivos = array_merge($js, $this->get_vista_form()->get_js_files());
                $archivos[] = kernel::vinculador()->vinculo_recurso("js/date-es-AR.js");
		return $archivos;
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
		return $this->controlador->get_form_builder();
	}
	

//	public function get_comision_hash(){
//		return $this->controlador->comision_hash;
//	}

	function prepare()
	{
            $operacion = kernel::ruteador()->get_id_operacion();
            //$operacion = 'asistencias'
            kernel::log()->add_debug('prepare GET: ', $_GET);
            kernel::log()->add_debug('prepare POST: ', $_POST);
            $this->data['resumen'] = $this->controlador->get_resumen();
            
            $link = kernel::vinculador()->crear($operacion, 'generar_pdf');
            $this->data['url']['generar_pdf'] = $link;
//            print_r('<br>Planilla: ');
//            print_r($this->data['planilla']);
//             die(__FILE__.':'.__LINE__);
            $this->add_var_js('clase_id', $this->controlador->clase_id);           
            $this->add_var_js('materia', $this->controlador->materia);
            $this->add_var_js('dia_semana', $this->controlador->dia_semana);
            $this->add_var_js('hs_comienzo_clase', $this->controlador->hs_comienzo_clase);
            $this->add_var_js('modo', $this->pantalla);           
	}
        
  }      
?>