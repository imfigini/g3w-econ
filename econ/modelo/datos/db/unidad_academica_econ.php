<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;

class unidad_academica_econ
{
	
    /**
     * cache: memoria
     * filas: n
     */
    function anios_academicos()
    {
            $sql = "SELECT a.anio_academico 
                            FROM sga_anio_academico as a 
                            ORDER BY anio_academico DESC";

            $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            foreach ($datos as $clave => $dato){
                    $nuevo[$clave] = $dato;
                    $nuevo[$clave]['_ID_'] = catalogo::generar_id($dato['ANIO_ACADEMICO']);
            }
            return $nuevo;
    }

    /**
     * parametros: anio_academico, periodo
     * cache: no
     * filas: 1
     */
    function get_limites_periodo($parametros)
    {
        $sql = "SELECT fecha_inicio, fecha_fin
                        FROM sga_periodos_lect
                        WHERE anio_academico = {$parametros['anio_academico']}
                        AND periodo_lectivo = {$parametros['periodo']}";
        return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
    }

}
?>