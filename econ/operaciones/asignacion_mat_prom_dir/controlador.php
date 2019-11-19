<?php
namespace econ\operaciones\asignacion_mat_prom_dir;

use kernel\kernel;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use kernel\util\validador;


class controlador extends controlador_g3w2
{
    protected $datos_filtro = array('anio_academico'=>"", 'periodo'=>"", 'mensaje'=>"", 'mensaje_error'=>"");
    
    function modelo()
    {
    }

    // function get_clase_vista()
    // {
    //     switch ($this->accion) {
    //         case 'materia': 
    //                 return 'vista_materia';
    //         default: 
    //                 return 'vista';
    //     }
    // }

    function accion__index()
    {
        if (!empty($_GET['formulario_filtro'])) {
            $this->datos_filtro = $_GET['formulario_filtro'];
        }
    }
     
    
    function get_materias_promo_directa($anio_academico_hash, $periodo_hash=null)
    {
        $periodo = null;
        $anio_academico = null;
        //$operacion = kernel::ruteador()->get_id_operacion();

        if (!empty($anio_academico_hash))
        {
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            if (!empty($anio_academico))
            {
                if (!empty($periodo_hash))
                {
                    $periodo = $this->decodificar_periodo($periodo_hash, $anio_academico);
	                $parametros = array(
                                'nro_inscripcion' =>  kernel::persona()->get_nro_inscripcion(),
                                'anio_academico' => $anio_academico,
                                'periodo' => $periodo
    	                );
        	        return catalogo::consultar('prom_directa', 'get_datos_materias_promo_directa', $parametros);
 				}
            }
        }
        return false;
    }

    function get_periodo()
    {
        return $this->datos_filtro['periodo'];
    }

    function get_anio_academico()
    {
        return $this->datos_filtro['anio_academico'];
    }

    function get_mensaje()
    {
        return $this->datos_filtro['mensaje'];
    }
    
    function get_mensaje_error()
    {
        return $this->datos_filtro['mensaje_error'];
    }
    
    function set_periodo($periodo)
    {
        $this->datos_filtro['periodo'] = $periodo;
    }

    function set_anio_academico($anio_academico)
    {
        $this->datos_filtro['anio_academico'] = $anio_academico;
    }

    function set_mensaje($mensaje)
    {
        $this->datos_filtro['mensaje'] = $mensaje;
    }

    function set_mensaje_error($mensaje)
    {
        $this->datos_filtro['mensaje_error'] = $mensaje;
    }
    
    function accion__grabar()
    {
        if (kernel::request()->isPost()) 
        {
			$parametros = $this->get_parametros_grabar();
			try 
			{
				kernel::db()->abrir_transaccion(); 
				$materias = $parametros['materias'];
				catalogo::consultar('prom_directa', 'resetear_prom_directa', $parametros);
				foreach($materias as $materia=>$prom_dir)
				{
					$parametros['materia'] = $materia;
					catalogo::consultar('prom_directa', 'set_prom_directa', $parametros);
				}
				kernel::db()->cerrar_transaccion();
			}
	        catch (error_guarani $e)
        	{
				kernel::db()->abortar_transaccion($e->getMessage());
            	$this->set_mensaje_error($e->getMessage());
			}
			$this->set_anio_academico($parametros['anio_academico_hash']);
			$this->set_periodo($parametros['periodo_hash']);
        }        
    }
    
    function get_parametros_grabar()
    {
        $parametros = array();
        
        $parametros['materias'] = $this->validate_param('materias', 'post', validador::TIPO_TEXTO);      
        $parametros['anio_academico_hash'] = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
        $parametros['periodo_hash'] = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO);  
		$parametros['anio_academico'] = $this->decodificar_anio_academico($parametros['anio_academico_hash']);
		$parametros['periodo'] = $this->decodificar_periodo($parametros['periodo_hash'], $parametros['anio_academico']);
	
		kernel::log()->add_debug('get_parametros_grabar', $parametros);
        return $parametros;        
    }

        
    // function get_materia($anio_academico_hash = null, $periodo_hash = null, $materia_hash = null)
    // {
    //     $materia_actual = null;
        
    //     $materias = $this->get_materias_en_comision($anio_academico_hash, $periodo_hash);
    //     foreach($materias as $materia){
    //         if ($materia['__ID__'] == $materia_hash)
    //         {
    //             $materia_actual = $materia;
    //         }
    //     }
    //     return $materia_actual;
    // }        
        
        
    function accion__buscar_periodos() 
    {
            $anio_academico_hash = $this->validate_param('anio_academico', 'get', validador::TIPO_TEXTO);
            $anio_academico = $this->decodificar_anio_academico($anio_academico_hash);
            $datos = array();
            if (!is_null($anio_academico)){
                    $datos = catalogo::consultar('unidad_academica', 'buscar_periodos_lectivos', array('anio' => $anio_academico));
            }
            $this->render_raw_json($datos);
    }
    
    /**
    * @return guarani_form
    */
    function get_form()
    {
        return $this->get_form_builder()->get_formulario();
    }

    function get_vista_form()
    {
        return $this->get_form_builder()->get_vista();
    }

    /**
    * @return builder_form_filtro
    */
    function get_form_builder()
    {
        if (! isset($this->form_builder)) {
                        $this->form_builder = kernel::localizador()->instanciar('operaciones\asignacion_mat_prom_dir\filtro\builder_form_filtro');
        }

        return $this->form_builder;
    }

    function decodificar_anio_academico($anio_hash) 
    {
        $datos = catalogo::consultar('unidad_academica_econ', 'anios_academicos');
        if (!empty($datos)) {
                foreach($datos as $value){
                        if ($value['_ID_'] == $anio_hash){
                                return $value['ANIO_ACADEMICO'];
                        }
                }
        }
        return false;
    }

    function decodificar_periodo($periodo_hash = null, $anio_academico = null) 
    {
        if (!is_null($anio_academico)){
                $periodos = catalogo::consultar('unidad_academica', 'buscar_periodos_lectivos', array('anio' => $anio_academico));
                if (!is_null($periodos)) {
                        foreach($periodos as $value){
                                if ($value['ID'] == $periodo_hash){
                                        return $value['PERIODO_LECTIVO'];
                                }
                        }
                }
        }
        return false;
    }
    
    // /** Replica los coordinadores asignados el cuatrimestre anterior */
    // function accion__replicar_coordinadores()
    // {
    //     if (kernel::request()->isPost()) 
    //     {
    //         $parametros = array();
    //         $anio_academico_hash = $this->validate_param('anio_academico_hash', 'post', validador::TIPO_TEXTO);
    //         $periodo_hash = $this->validate_param('periodo_hash', 'post', validador::TIPO_TEXTO); 
    //         $parametros['anio_academico'] = $this->decodificar_anio_academico($anio_academico_hash);
    //         $parametros['periodo'] = $this->decodificar_periodo($periodo_hash, $parametros['anio_academico']);
            
    //         try
    //         {
    //             if (isset($parametros['periodo']))
    //             {
    //                 kernel::db()->abrir_transaccion();
    //                 if (strpos($parametros['periodo'], '1') !== false)
    //                 {
    //                     $parametros['anio_academico_anterior'] = $parametros['anio_academico']-1;
    //                     $parametros['periodo_anterior'] = str_replace('1','2',$parametros['periodo']);
    //                 }
    //                 else 
    //                 {
    //                     $parametros['anio_academico_anterior'] = $parametros['anio_academico'];
    //                     $parametros['periodo_anterior'] = str_replace('2','1',$parametros['periodo']);
    //                 }
    //                 catalogo::consultar('coord_materia', 'replicar_coordinador', $parametros);
                    
    //                 kernel::db()->cerrar_transaccion();
                    
    //             }
    //             $this->set_anio_academico($anio_academico_hash);
    //             $this->set_periodo($periodo_hash);
    //             $this->set_mensaje('Se replicaron los corrdinadores del cuatrimestre anterior');
    //         } 
    //         catch (error_guarani $e)
    //         {
    //             $msj = $e->getMessage();
    //             kernel::db()->abortar_transaccion($msj);
    //             $this->set_anio_academico($parametros['anio_academico_hash']);
    //             $this->set_periodo($parametros['periodo_hash']);
    //             $this->set_mensaje_error($msj);
    //         }
    //     }
    // }
}
?>
