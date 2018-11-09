<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;

class mixes 
{
    /**
     * parametros: 
     * cache: no
     * filas: n
     */
    function get_carreras_grado($parametros)
    {
        $sql = "SELECT  carrera, 
                        nombre AS carrera_nombre
                    FROM sga_carreras 
                    WHERE tipo_de_carrera = 'C' ";
            
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos;
    }

    /**
     * parametros: carrera, anio_de_cursada, mix
     * cache: no
     * filas: n
     */
    function get_materias_mix($parametros)
    {
        if (isset($parametros['carrera']) AND isset($parametros['anio_de_cursada']) AND isset($parametros['mix']))
        {            
            $carrera = $parametros['carrera'];
            $anio = $parametros['anio_de_cursada'];
            $mix = $parametros['mix'];
            $sql = "SELECT  X.materia, 
                            M.nombre AS materia_nombre
                    FROM ufce_mixes X
                    JOIN sga_materias M ON (M.materia = X.materia)
                        WHERE X.carrera = $carrera 
                            AND X.anio_de_cursada = $anio
                            AND X.mix = $mix ";
            return kernel::db()->consultar($sql, db::FETCH_ASSOC);
        }
        return null;
    }
    
    /**
     * parametros: carrera
     * cache: no
     * filas: n
     */
    function get_anios_de_cursada_con_mix($parametros)
    {
        if (isset($parametros['carrera']))
        {            
            $carrera = $parametros['carrera'];
            $sql = "SELECT  DISTINCT anio_de_cursada
                    FROM ufce_mixes
                        WHERE carrera = $carrera ";
            $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            return $datos;
        }
        return null;
    }
    
    /**
     * parametros: carrera, anio_de_cursada
     * cache: no
     * filas: n
     */
    function get_mixes_del_anio($parametros)
    {
        if (isset($parametros['carrera']) AND isset($parametros['anio_de_cursada']))
        {            
            $carrera = $parametros['carrera'];
            $anio_de_cursada = $parametros['anio_de_cursada'];
            $sql = "SELECT  DISTINCT mix
                    FROM ufce_mixes
                        WHERE carrera = $carrera 
                            AND anio_de_cursada = $anio_de_cursada ";
            return kernel::db()->consultar($sql, db::FETCH_ASSOC);
        }
        return null;
    }

}
