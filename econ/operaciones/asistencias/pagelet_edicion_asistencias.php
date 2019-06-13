<?php
namespace econ\operaciones\asistencias;

//use kernel\interfaz\pagelet;
use kernel\kernel;
//use siu\guarani;

class pagelet_edicion_asistencias extends \siu\operaciones\asistencias\pagelet_edicion_asistencias
{
	protected $encabezado;
	protected $renglon = false;
	
    function get_nombre()
    {
        return 'edicion_asistencias';
    }
	
	/**
	 * @return carga_notas_cursada
	 */
	protected function modelo()
	{
		return $this->controlador->modelo();
	}
	
	protected function get_clase_encabezado_econ()
	{
		return $this->controlador->get_clase_encabezado_econ();
	}
	
	protected function get_clase_detalle()
	{
		// se pide al controlador para hacer validaci�n con los datos enviados al guardar
		$detalle = $this->controlador->get_clase_detalle();
		
		return $detalle;
	}

	protected function get_id_clase()
	{
		return $this->controlador->clase_id;
	}
	
	protected function get_url_guardar() 
	{
		return kernel::vinculador()->crear('asistencias', 'editar', $this->controlador->clase_id);
	}
	
	function set_highlight($renglon)
	{
		$this->renglon = $renglon;
	}
	
    function prepare()
    {
		$this->data = array();
		$this->data['clase_detalle'] = $this->get_clase_detalle();
		$this->data['clase_id'] = $this->controlador->clase_id;
                $this->data['materia'] = $this->controlador->materia;
                $this->data['dia_semana'] = $this->controlador->dia_semana;
                $this->data['fecha'] = $this->controlador->fecha;
                $this->data['hs_comienzo_clase'] = $this->controlador->hs_comienzo_clase;
		$this->data['filas'] = $this->controlador->filas;
		$this->data['url_guardar'] = $this->get_url_guardar();
		$this->add_var_js('highlight_renglon', $this->renglon);
    }

}
?>
