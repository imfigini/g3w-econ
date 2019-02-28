<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;


class evaluaciones_parciales
{
    /**
     * parametros: anio_academico, periodo
     * cache: no
     * filas: n
     */
    function get_periodos_evaluacion($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        
        $sql = "SELECT  ufce_orden_periodo.orden, 
                        ufce_orden_periodo.descripcion AS orden_nombre,
                        ufce_eval_parc_periodos.fecha_inicio, 
                        ufce_eval_parc_periodos.fecha_fin 
                    FROM ufce_orden_periodo
                        LEFT JOIN ufce_eval_parc_periodos ON 
                                    (ufce_eval_parc_periodos.orden = ufce_orden_periodo.orden
                                        AND ufce_eval_parc_periodos.anio_academico = $anio_academico 
                                        AND ufce_eval_parc_periodos.periodo_lectivo = $periodo) ";
        
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos;
    }
    
    /**
     * parametros: anio_academico, periodo
     * cache: no
     * filas: n
     */
    function get_periodo_lectivo($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        
        $sql = "SELECT  fecha_inicio, 
                        fecha_fin  
                FROM sga_periodos_lect 
                    WHERE anio_academico = $anio_academico 
                    AND periodo_lectivo = $periodo ";
                                        
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }

    /**
     * parametros: anio_academico, periodo
     * cache: no
     * filas: n
     */
    function get_dias_no_laborales($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        
        $sql = "SELECT  fecha
                FROM sga_calend_no_lab 
                    WHERE fecha >= (SELECT fecha_inicio FROM sga_periodos_lect 
                                        WHERE anio_academico = $anio_academico 
                                        AND periodo_lectivo = $periodo)
                    AND fecha <= (SELECT fecha_fin FROM sga_periodos_lect 
                                        WHERE anio_academico = $anio_academico 
                                        AND periodo_lectivo = $periodo)";
                                        
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: anio_academico, periodo, orden
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function existe_periodo_evaluacion($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $orden = $parametros['orden'];
        $sql = "SELECT COUNT(*) AS existe
                    FROM ufce_eval_parc_periodos
                    WHERE anio_academico = $anio_academico 
                        AND periodo_lectivo = $periodo
                        AND orden = $orden";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        if ($result[0]['EXISTE'] > 0)
        {
            return true;
        }
        return false;
    }
    
    /**
     * parametros: anio_academico, periodo, orden, fecha_inicio, fecha_fin
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function set_periodos_evaluacion($parametros)
    {
        if ($this->existe_periodo_evaluacion($parametros))
        {
            $this->update_periodos_evaluacion($parametros);
        }
        else
        {
            $this->insert_periodos_evaluacion($parametros);
        }
    }

    /**
     * parametros: anio_academico, periodo, orden, fecha_inicio, fecha_fin
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function update_periodos_evaluacion($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $orden = $parametros['orden'];
        $fecha_inicio = self::strToMDY($parametros['fecha_inicio']);
        $fecha_fin = self::strToMDY($parametros['fecha_fin']);
        $sql = "UPDATE ufce_eval_parc_periodos
                    SET fecha_inicio = $fecha_inicio,
                        fecha_fin = $fecha_fin
                WHERE anio_academico = $anio_academico
                    AND periodo_lectivo = $periodo
                    AND orden = $orden";
        kernel::db()->ejecutar($sql);
    }

    /**
     * parametros: strFecha
     * cache: no
     * filas: n
     * El formato de strFecha debe ser: Y-m-d
     */
    private function strToMDY($strFecha)
    {
        $fecha = explode("'", $strFecha);
        $fecha = explode('/', $fecha[1]);
        return 'MDY('.$fecha[1].','. $fecha[0].','.$fecha[2].')';
    }
    
    /**
     * parametros: anio_academico, periodo, orden, fecha_inicio, fecha_fin
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function insert_periodos_evaluacion($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $orden = $parametros['orden'];
        $fecha_inicio = $parametros['fecha_inicio'];
        $fecha_fin = $parametros['fecha_fin'];
        $sql = "INSERT INTO ufce_eval_parc_periodos (anio_academico, periodo_lectivo, orden, fecha_inicio, fecha_fin)
                    VALUES ($anio_academico, $periodo, $orden, $fecha_inicio, $fecha_fin)";
        kernel::db()->ejecutar($sql);
    }
 
    /**
     * parametros: 
     * cache: no
     * filas: n
     */
    function get_carreras()
    {
        $sql = "SELECT carrera, nombre AS carrera_nombre 
                    FROM sga_carreras
                    WHERE carrera IN (SELECT carrera from ufce_mixes)";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $result;
    }

    /**
     * parametros: 
     * cache: no
     * filas: n
     */
    function get_mixs($parametros)
    {
        $sql = "SELECT DISTINCT anio_de_cursada || mix AS mix, anio_de_cursada || ' - ' || mix AS mix_nombre
                    FROM ufce_mixes
                ORDER BY 1";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $result;
    }

    /**
     * parametros: anio_academico, periodo, carrera, anio_cursada, mix
     * cache: no
     * filas: n
     */
    function get_evaluaciones_de_materias($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $carrera = $parametros['carrera'];
        $anio_cursada = $parametros['anio_cursada'];
        $mix = $parametros['mix'];
        
        $sql = "SELECT DISTINCT sga_materias.materia, 
                                sga_materias.nombre as materia_nombre, 
                                sga_eval_parc.descripcion as evaluacion, 
                                sga_cron_eval_parc.fecha_hora::DATE as fecha
                    FROM sga_cron_eval_parc
                    JOIN sga_comisiones ON (sga_comisiones.comision = sga_cron_eval_parc.comision)
                    JOIN sga_materias ON (sga_materias.materia = sga_comisiones.materia)
                    JOIN sga_eval_parc ON (sga_eval_parc.evaluacion = sga_cron_eval_parc.evaluacion)
                    JOIN ufce_mixes ON (ufce_mixes.materia = sga_materias.materia)
                    WHERE sga_comisiones.anio_academico = $anio_academico
                            AND sga_comisiones.periodo_lectivo = $periodo ";
        if ($parametros['carrera'] != "''")
        {
            $sql .= " AND ufce_mixes.carrera = $carrera ";
        }
        if ($parametros['anio_cursada'] != "''")
        {
            $sql .= " AND ufce_mixes.anio_de_cursada = $anio_cursada ";
        }                    
        if ($parametros['mix'] != "''")
        {
            $sql .= " AND ufce_mixes.mix = $mix ";
        }
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $result;
    }
}