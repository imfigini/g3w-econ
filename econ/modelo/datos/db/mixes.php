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
            $sql = "SELECT DISTINCT mix
                    FROM ufce_mixes
                        WHERE carrera = $carrera 
                            AND anio_de_cursada = $anio_de_cursada ";
            return kernel::db()->consultar($sql, db::FETCH_ASSOC);
        }
        return null;
    }

    
    /**
     * parametros: carrera
     * cache: no
     * filas: n
     */
    function get_carrera_nombre($parametros)
    {
        if (isset($parametros['carrera']))
        {            
            $carrera = $parametros['carrera'];
            $sql = "SELECT nombre AS carrera_nombre
                    FROM sga_carreras
                        WHERE carrera = $carrera ";
            return kernel::db()->consultar($sql, db::FETCH_ASSOC);
        }
        return null;
    }
    
    /**
     * parametros: carrera, anio, mix, materia
     * cache: no
     * filas: 1
     */
    function del_materia_de_mix($parametros)
    {
        $carrera = $parametros['carrera'];
        $anio = $parametros['anio'];
        $mix = $parametros['mix'];
        $materia = $parametros['materia'];
        $sql = "DELETE FROM ufce_mixes 
                    WHERE carrera = $carrera
                    AND anio_de_cursada = $anio
                    AND mix = $mix
                    AND materia = $materia";
        return kernel::db()->ejecutar($sql);
    }
    
    /**
     * parametros: carrera
     * cache: no
     * filas: 1
     */
    function get_materias_sin_mix($parametros)
    {
        $carrera = $parametros['carrera'];
        $sql = "SELECT DISTINCT materia, nombre_materia 
                    FROM sga_atrib_mat_plan MP
                    WHERE carrera = $carrera
                    AND plan IN (SELECT plan FROM sga_planes WHERE carrera = MP.carrera AND plan = MP.plan AND version_actual = MP.version AND estado = 'V') 
                    AND materia NOT IN (SELECT materia FROM ufce_mixes WHERE carrera = MP.carrera)
                    AND tipo_materia = 'N' AND obligatoria = 'S'
                ORDER BY nombre_materia ";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: carrera, materia
     * cache: no
     * filas: 1
     */
    function get_plan_y_version_actual_de_materia($parametros)
    {
        $carrera = $parametros['carrera'];
        $materia = $parametros['materia'];
        $sql = "SELECT plan, version
                    FROM sga_atrib_mat_plan MP
                    WHERE carrera = $carrera
                        AND plan IN (SELECT plan FROM sga_planes WHERE carrera = MP.carrera AND plan = MP.plan AND version_actual = MP.version AND estado = 'V') 
                        AND materia = $materia ";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }

    /**
     * parametros: carrera, plan, version, materia, anio, mix
     * cache: no
     * filas: 1
     */
    function add_materia_a_mix($parametros)
    {
        $carrera = $parametros['carrera'];
        $plan = $parametros['plan'];
        $version = $parametros['version'];
        $materia = $parametros['materia'];
        $anio = $parametros['anio'];
        $mix = $parametros['mix'];
        
        $sql = "INSERT INTO ufce_mixes (unidad_academica, carrera, plan, version, materia, anio_de_cursada, mix) VALUES ('FCE', $carrera, $plan, $version, $materia, $anio, $mix) ";
        return kernel::db()->ejecutar($sql);
    }    
}
