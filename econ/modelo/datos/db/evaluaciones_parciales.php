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
        print_R($sql);
        kernel::db()->ejecutar($sql);
    }
    
}