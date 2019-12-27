<?php


namespace econ\modelo\datos\db;


use kernel\kernel;

class docente extends \siu\modelo\datos\db\docente
{
    /**
     * parametros: _ua, nro_inscripcion
     * cache: memoria
     * cache_expiracion: 600
     * filas: n
     */
    function agenda_examenes($parametros)
    {
        $sql = "execute procedure sp_AgendaDocExa(" . $parametros["_ua"] . ", " . $parametros["nro_inscripcion"] .")";
        $datos = kernel::db()->consultar($sql, db::FETCH_NUM);
        $nuevo = array();
        foreach($datos as $id => $dato) {
            $nuevo[$id]['MATERIA_NOMBRE_COMPLETO'] = $dato[2]." (".$dato[1].")";
            $nuevo[$id]['MATERIA'] = $dato[1];
            $nuevo[$id]['MATERIA_NOMBRE'] = $dato[2];
            $nuevo[$id]['ANIO_ACADEMICO'] = $dato[3];
            $nuevo[$id]['TURNO_EXAMEN'] = $dato[4];
            $nuevo[$id]['MESA_EXAMEN'] = $dato[5];
            $nuevo[$id]['LLAMADO'] = $dato[6];
            $nuevo[$id]['LEGAJO'] = $dato[7];
            $nuevo[$id]['DOCENTE_NOMBRE'] = $dato[8];
            $nuevo[$id]['DOCENTE_ROL'] = $dato[9];
            $nuevo[$id]['FECHA'] = $dato[11];
            $nuevo[$id]['HORA_INI'] = $dato[12];
            $nuevo[$id]['HORA_FIN'] = $dato[17];
            $nuevo[$id]['AULA'] = $dato[13];
            $nuevo[$id]['AULA_NOMBRE'] = $dato[14];
            $nuevo[$id]['EDIFICIO'] = $dato[15];
            $nuevo[$id]['EDIFICIO_NOMBRE'] = $dato[16];
            $nuevo[$id]['SEDE'] = $dato[18];
            $nuevo[$id]['SEDE_NOMBRE'] = $dato[19];
            $nuevo[$id]['SEDE_NOMBRE_COMPLETO'] = $dato[19];
            foreach($nuevo[$id] as $clave => $valor){
                if (empty($valor)){
                    $nuevo[$id][$clave] = "No informa";
                }
            }
        }
        return $nuevo;
    }

}