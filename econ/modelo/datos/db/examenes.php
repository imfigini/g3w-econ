<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
//use siu\modelo\datos\db\carga_asistencias;
use siu\modelo\entidades\alumno_foto;

class examenes
{
    /**
    * parametros: _ua
    * cache: no
    * filas: 1
    */
    function get_fechas_turno_examen_actual($parametros)
    {
        $sql = "SELECT fecha_inicio, fecha_fin 
                FROM sga_turnos_examen
                WHERE TODAY < fecha_fin";

        return kernel::db()->consultar_fila($sql);
    }   
}