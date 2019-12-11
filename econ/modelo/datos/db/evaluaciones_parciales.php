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
										AND ufce_eval_parc_periodos.periodo_lectivo = $periodo) 
				ORDER BY 1 ";
        
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos;
    }
    
    /**
     * parametros: anio_academico, periodo
     * cache: no
     * filas: n
     */
    function get_periodo_solicitud_fecha($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        
        $sql = "SELECT  fecha_inicio, fecha_fin 
                    FROM ufce_priodo_solic_fecha_parc
                        WHERE anio_academico = $anio_academico 
                        AND periodo_lectivo = $periodo ";
        
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
     * parametros: anio_academico, periodo, fecha_inicio, fecha_fin
     * cache: no
     * filas: n
     */
    function set_periodo_solicitud_fecha($parametros)
    {
        if ($this->existe_periodo_solicitud_fecha($parametros))
        {
            $this->update_periodo_solicitud_fecha($parametros);
        }
        else
        {
            $this->insert_periodo_solicitud_fecha($parametros);
        }
    }
    
    /**
     * parametros: anio_academico, periodo
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function existe_periodo_solicitud_fecha($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $sql = "SELECT COUNT(*) AS existe
                    FROM ufce_priodo_solic_fecha_parc
                    WHERE anio_academico = $anio_academico 
                        AND periodo_lectivo = $periodo ";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        if ($result[0]['EXISTE'] > 0)
        {
            return true;
        }
        return false;
    }

    /**
     * parametros: anio_academico, periodo, fecha_inicio, fecha_fin
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function update_periodo_solicitud_fecha($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $fecha_inicio = self::strToMDY($parametros['fecha_inicio']);
        $fecha_fin = self::strToMDY($parametros['fecha_fin']);
        $sql = "UPDATE ufce_priodo_solic_fecha_parc
                    SET fecha_inicio = $fecha_inicio,
                        fecha_fin = $fecha_fin
                WHERE anio_academico = $anio_academico
                    AND periodo_lectivo = $periodo ";
        kernel::db()->ejecutar($sql);
    }

    /**
     * parametros: anio_academico, periodo, fecha_inicio, fecha_fin
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function insert_periodo_solicitud_fecha($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $fecha_inicio = $parametros['fecha_inicio'];
        $fecha_fin = $parametros['fecha_fin'];
        $sql = "INSERT INTO ufce_priodo_solic_fecha_parc (anio_academico, periodo_lectivo, fecha_inicio, fecha_fin)
                    VALUES ($anio_academico, $periodo, $fecha_inicio, $fecha_fin)";
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
    function get_evaluaciones_aceptadas($parametros)
    {
		//No recupera los TP
        $sql = "SELECT DISTINCT sga_materias.materia, 
                                sga_materias.nombre as materia_nombre, 
                                CASE 
                                    WHEN sga_eval_parc.evaluacion = 1 THEN '1튡romo'
                                    WHEN sga_eval_parc.evaluacion = 2 THEN '2튡romo'
                                    WHEN sga_eval_parc.evaluacion = 7 THEN 'R.Unico'
                                    WHEN sga_eval_parc.evaluacion = 14 THEN 'Integ'
                                    WHEN sga_eval_parc.evaluacion = 21 THEN 'Regu'
                                    WHEN sga_eval_parc.evaluacion = 4 THEN 'Recup1'                                    
                                    WHEN sga_eval_parc.evaluacion = 5 THEN 'Recup2'
									WHEN sga_eval_parc.evaluacion = 22 THEN '1튡arcial'
									WHEN sga_eval_parc.evaluacion = 23 THEN '2튡arcial'
									WHEN sga_eval_parc.evaluacion = 24 THEN 'Recup'
									ELSE sga_eval_parc.descripcion
                                END as evaluacion, 
                                sga_eval_parc.evaluacion as eval_id,
                                sga_cron_eval_parc.fecha_hora::DATE as fecha,
                                'A' as estado
                    FROM sga_cron_eval_parc
                    JOIN sga_comisiones ON (sga_comisiones.comision = sga_cron_eval_parc.comision)
                    JOIN sga_materias ON (sga_materias.materia = sga_comisiones.materia)
                    JOIN sga_eval_parc ON (sga_eval_parc.evaluacion = sga_cron_eval_parc.evaluacion)
                    JOIN ufce_mixes ON (ufce_mixes.materia = sga_materias.materia)
                    WHERE sga_comisiones.anio_academico = {$parametros['anio_academico']}
							AND sga_comisiones.periodo_lectivo = {$parametros['periodo']}
							AND sga_eval_parc.evaluacion <> 15 ";
        if ($parametros['carrera'] != "''")
        {
            $sql .= " AND ufce_mixes.carrera = {$parametros['carrera']} ";
        }
        if ($parametros['anio_cursada'] != "''")
        {
            $sql .= " AND ufce_mixes.anio_de_cursada = {$parametros['anio_cursada']} ";
        }                    
        if ($parametros['mix'] != "''")
        {
            $sql .= " AND ufce_mixes.mix = {$parametros['mix']} ";
        }
        $sql .= " ORDER BY 1 ";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * parametros: anio_academico, periodo, carrera, anio_cursada, mix
     * cache: no
     * filas: n
     */
    function get_evaluaciones_pendientes($parametros)
    {
        $sql = "SELECT DISTINCT sga_materias.materia, 
                                sga_materias.nombre as materia_nombre, 
                                CASE 
                                    WHEN sga_eval_parc.evaluacion = 1 THEN '1튡romo'
                                    WHEN sga_eval_parc.evaluacion = 2 THEN '2튡romo'
                                    WHEN sga_eval_parc.evaluacion = 7 THEN 'R.Unico'
                                    WHEN sga_eval_parc.evaluacion = 14 THEN 'Integ'
                                    WHEN sga_eval_parc.evaluacion = 21 THEN 'Regu'
                                    WHEN sga_eval_parc.evaluacion = 4 THEN 'Recup1'                                    
                                    WHEN sga_eval_parc.evaluacion = 5 THEN 'Recup2'
									WHEN sga_eval_parc.evaluacion = 22 THEN '1튡arcial'
									WHEN sga_eval_parc.evaluacion = 23 THEN '2튡arcial'
									WHEN sga_eval_parc.evaluacion = 24 THEN 'Recup'
									ELSE sga_eval_parc.descripcion
                                END as evaluacion, 
                                sga_eval_parc.evaluacion as eval_id,
                                ufce_cron_eval_parc.fecha_hora::DATE as fecha,
                                'P' as estado
                    FROM ufce_cron_eval_parc
                    JOIN sga_comisiones ON (sga_comisiones.comision = ufce_cron_eval_parc.comision)
                    JOIN sga_materias ON (sga_materias.materia = sga_comisiones.materia)
                    JOIN sga_eval_parc ON (sga_eval_parc.evaluacion = ufce_cron_eval_parc.evaluacion)
                    JOIN ufce_mixes ON (ufce_mixes.materia = sga_materias.materia)
                    WHERE sga_comisiones.anio_academico = {$parametros['anio_academico']}
                            AND sga_comisiones.periodo_lectivo = {$parametros['periodo']} 
                            AND ufce_cron_eval_parc.estado = 'P'";
        if ($parametros['carrera'] != "''")
        {
            $sql .= " AND ufce_mixes.carrera = {$parametros['carrera']} ";
        }
        if ($parametros['anio_cursada'] != "''")
        {
            $sql .= " AND ufce_mixes.anio_de_cursada = {$parametros['anio_cursada']} ";
        }                    
        if ($parametros['mix'] != "''")
        {
            $sql .= " AND ufce_mixes.mix = {$parametros['mix']} ";
        }
        $sql .= " ORDER BY 1 ";
        
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $result;
    }
    
}
