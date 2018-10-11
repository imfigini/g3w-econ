<?php
namespace econ\modelo\transacciones;

class asignacion_coord_materias 
{
    function info__get_coordinador($parametros)
    {
        return catalogo::consultar('coord_materia', 'get_coordinador', $parametros);
    }

    function evt__set_coordinador($parametros)
    {
        return catalogo::consultar('coord_materia', 'set_coordinador', $parametros);
    }
    
    function evt__update_coordinador($parametros)
    {
        return catalogo::consultar('coord_materia', 'update_coordinador', $parametros);
    }

}