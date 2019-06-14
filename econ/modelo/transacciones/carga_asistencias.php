<?php
namespace econ\modelo\transacciones;

use kernel\kernel;
use siu\modelo\datos\catalogo;
use siu\errores\error_guarani;
use siu\errores\error_guarani_procesar_renglones;

class carga_asistencias extends \siu\modelo\transacciones\carga_asistencias
{

	//---------------------------------------------
	//	LISTA CLASES
	//---------------------------------------------
	
	function listado_dias_clases_docente() 
	{
		$parametros = array('legajo' => kernel::persona()->get_legajo_docente());
		return catalogo::consultar('carga_asistencias', 'listado_dias_clases_docente', $parametros);
	}
	
//	function get_lista_dias_clase($materia) 
//	{
//		$parametros = array('legajo' => kernel::persona()->get_legajo_docente(), 'materia' => $materia);
//		return catalogo::consultar('carga_asistencias', 'listado_dias_clase_docente', $parametros);
//	}

        function get_clases_especificas($materia, $dia_semana, $hs_comienzo, $cant)
	{
                $parametros = array('legajo' => kernel::persona()->get_legajo_docente(), 'materia'=>$materia, 'dia_semana'=>$dia_semana, 'hs_comienzo'=>$hs_comienzo, 'filas'=>$cant);
                $resultado['clases'] = catalogo::consultar('carga_asistencias', 'get_clases_especificas_docente', $parametros);
                
		//$parametros['filas'] = $cant;

		foreach ($resultado['clases'] as $key => $clase) {
			$parametros['clase_id'] = $clase[catalogo::id];
                        $parametros['fecha'] = $clase['FECHA'];
			$resultado['clases'][$key]['URL_EDITAR'] = kernel::vinculador()->crear('asistencias', 'editar', $parametros);
		}
		return $resultado;
	}
        
        function info__clase_cabecera($seleccion_clase, $materia, $dia_semana, $hs_comienzo_clase, $filas)
	{
            $datos_clase = $this->get_clase_especifica_enviada($seleccion_clase, $materia, $dia_semana, $hs_comienzo_clase, $filas);
            $datos_clase['MATERIA'] = $datos_clase['MATERIA'];
            $datos_clase['MATERIA_NOMBRE'] = $datos_clase['MATERIA_NOMBRE'];
            return $datos_clase;
	}	
	
        function info__clase_detalle($seleccion_clase, $materia, $dia_semana, $hs_comienzo_clase,  $filas)
	{
            $datos_clase = $this->get_clase_especifica_enviada($seleccion_clase, $materia, $dia_semana, $hs_comienzo_clase, $filas);
            $parametros = array('materia'=>$materia, 'fecha_clase'=>$datos_clase['FECHA_CLASE'], 'hs_comienzo_clase'=>$hs_comienzo_clase);
            $clases = catalogo::consultar('carga_asistencias', 'get_clases_materia_dia_hs', $parametros);
            $result = array();
            foreach ($clases AS $clase)
            {
                $parametros['clase'] = $clase['CLASE'];
                $r = catalogo::consultar('carga_asistencias', 'clase_detalle', $parametros);
                $result = array_merge($result, $r);
            }
            return $result;
	}

        
        function info__lista_clases()
	{
            $parametros = array('legajo' => kernel::persona()->get_legajo_docente());
            return catalogo::consultar('carga_asistencias', 'listado_dias_clases_docente', $parametros);
	}
        
        
        function get_clase_especifica_enviada($seleccion_clase, $materia, $dia_semana, $hs_comienzo_clase, $filas)
	{
            $opciones_enviadas = $this->get_clases_especificas($materia, $dia_semana, $hs_comienzo_clase, $filas);
            if (isset($opciones_enviadas)) 
            {
                foreach($opciones_enviadas['clases'] as $opcion) 
                {
                    if(isset($opcion['__ID__']) && $opcion['__ID__'] == $seleccion_clase){
                        return $opcion;
                    }
                }
            }
            throw new error_guarani('CLASE invalida');		
	}	

	function grabar($seleccion_clase, $materia, $dia_semana, $hs_comienzo_clase, $alumnos)
	{
                $error = new error_guarani_procesar_renglones('Error cargando notas');
		$hay_renglones_actualizados = false;		
		$clases = $this->info__clase_detalle($seleccion_clase, $materia, $dia_semana, $hs_comienzo_clase, 0);
                
                foreach ($alumnos as $id => $alumno)
                {
                    try 
                    {
                        $datos_clase = $this->get_datos_alumno_clase($clases, $id);
                        $parametros = array();
			$parametros['carrera'] = $datos_clase['CARRERA'];
			$parametros['legajo'] = $datos_clase['LEGAJO'];
			$parametros['comision'] = $datos_clase['COMISION'];
			$parametros['clase'] = $datos_clase['CLASE'];
			$parametros['cant_inasist'] = $alumno['CANT_INASIST'];
                        $parametros['motivo'] = $alumno['MOTIVO'];
                        kernel::log()->add_debug('GRABAR_$parametros', $parametros);
			$ok = catalogo::consultar('carga_asistencias', 'guardar', $parametros);
			if($ok[0] == 1) {
                            $hay_renglones_actualizados = true;
			} 
                        else {
                            $error->add_renglon($parametros['legajo'], util::mensaje($ok[1]), $parametros);
			}
                    } 
                    catch (error_kernel $e) 
                    {
                        $error->add_renglon($parametros['legajo'], $e->getMessage(), $parametros);
                        //kernel::log()->add_error($e);				
                    }			
		}
		if($error->hay_renglones()) {
                    throw $error;
		}			
	}
        
        function get_datos_alumno_clase($clases, $url_alumno)
	{
            foreach($clases as $clase)
            {
                if($clase['__ID__']==$url_alumno){
                    return $clase;
                }
            }
            throw new error_guarani('Clase invalida');
	}

        function get_motivos_inasistencia()
        {
            return catalogo::consultar('carga_asistencias', 'get_motivos_inasistencia', null);
        }
        
}

?>
