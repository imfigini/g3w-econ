<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
//use siu\modelo\datos\db\carga_asistencias;
use siu\modelo\entidades\alumno_foto;

class carga_foto_dni
{
    protected function get_path_attachment()
    {
        return kernel::proyecto()->get_dir_attachment();
    }

    /**
    * parametros: _ua, nro_inscripcion
    * cache: no
    * filas: 1
    */
    function get_foto_dni($parametros)
    {
        $sql = "SELECT archivo
                    FROM ufce_fotos_dni
                    WHERE unidad_academica = {$parametros['_ua']}
                    AND nro_inscripcion = {$parametros['nro_inscripcion']} ";

        return kernel::db()->consultar_fila($sql);
    }

    /**
    * parametros: _ua, nro_inscripcion, upload
    * cache: no
    * filas: 1
    */
    function set_foto_dni($parametros)
    {
        $existe = self::get_foto_dni($parametros);
        $sql = '';
        if (count($existe) > 0)
        {
            $sql = "UPDATE ufce_fotos_dni 
                    SET archivo = {$parametros["upload"]},
                        fecha_actualizacion = CURRENT
                    WHERE unidad_academica = {$parametros['_ua']}
                    AND nro_inscripcion = {$parametros['nro_inscripcion']} ";
        }
        else
        {
            $sql = "INSERT INTO ufce_fotos_dni (unidad_academica, nro_inscripcion, archivo, fecha_actualizacion)
                        VALUES ( {$parametros["_ua"] }, {$parametros["nro_inscripcion"]}, {$parametros["upload"]}, CURRENT)";
        }
        return kernel::db()->ejecutar($sql);
    }
    
}