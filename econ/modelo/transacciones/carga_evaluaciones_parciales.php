<?php
namespace econ\modelo\transacciones;

use kernel\kernel;
use siu\modelo\datos\catalogo;

class carga_evaluaciones_parciales extends \siu\modelo\transacciones\carga_evaluaciones_parciales
{
    function info__tipo_evaluacion()
    {
        return catalogo::consultar('carga_evaluaciones_parciales', 'listado_tipo_evaluacion_econ',null);
    }
    
    //---------------------------------------------
    //	LISTA EVALUACIONES
    //---------------------------------------------
    function info__lista_evaluaciones()
    {
        $parametros = array('legajo' => kernel::persona()->get_legajo_docente());
        return catalogo::consultar('carga_evaluaciones_parciales', 'listado_evaluaciones_parciales_econ', $parametros);
    }
    
    //---------------------------------------------
    //	CARGA NOTAS
    //---------------------------------------------	

    function info__escala_notas($escala_notas)
    {
        $parametros = array('escala_notas' => $escala_notas);
        //return catalogo::consultar('carga_evaluaciones_parciales', 'escala_notas', $parametros);
        return catalogo::consultar('sistema', 'escala_notas_econ', $parametros);
    }
    
}
