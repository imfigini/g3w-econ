<?php
namespace econ\operaciones\asistencias;

use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\modelo\datos\catalogo;
use siu\operaciones\_comun\operaciones\reporte\generador_pdf;

class pagelet_resumen extends pagelet
{
    public $comisiones = '';
    
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

        public function set_comisiones($comisiones)
        {
            $this->comisiones = $comisiones;
        }

        public function get_comisiones()
        {
            return $this->comisiones;
        }
        
	function prepare()
	{
            $operacion = kernel::ruteador()->get_id_operacion();
            //$operacion = 'asistencias'
//            kernel::log()->add_debug('prepare resumen GET: ', $_GET);
//            kernel::log()->add_debug('prepare resumen POST: ', $_POST);
            $this->data['resumen'] = $this->controlador->get_resumen();
            $this->data['comisiones'] = $this->get_comisiones();
            $link = kernel::vinculador()->crear($operacion, 'generar_excel');
            $this->data['url']['generar_excel'] = $link;

            $this->add_var_js('comisiones', $this->controlador->comisiones);           
	}
        
  }      
?>