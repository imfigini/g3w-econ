<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\util;


class cursos 
{
    /**
     * parametros: legajo
     * param_null: legajo
     * cache: no
     * filas: n
     */
    function get_materias_cincuentenario($parametros)
    {
        $sql = "SELECT DISTINCT M.materia, 
                                M.nombre AS materia_nombre
                        FROM sga_materias M
                        JOIN sga_atrib_mat_plan P ON (P.materia = M.materia)
                        WHERE P.plan LIKE '50%' ";

        if ($parametros['legajo'] != "''")
        {
            $legajo = $parametros['legajo'];
            $sql .= " AND M.materia IN (SELECT materia FROM ufce_coordinadores_materias WHERE coordinador = $legajo) ";
        }
        
        $sql .= " ORDER BY 2";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $result;
    }
    
    /**
     * parametros: materia
     * cache: no
     * filas: n
     */
    function get_ciclo_de_materias($parametros)
    {
        $materia = $parametros['materia'];
        $sql = "SELECT DISTINCT 
                            CASE 
                                    WHEN lower(CIC.nombre) LIKE '%fundam%' THEN 'F'
                                    WHEN lower(CIC.nombre) LIKE '%prof%' THEN 'P'
                            END AS ciclo
                    FROM sga_materias M
                    JOIN sga_materias_ciclo MC ON (MC.materia = M.materia)
                    JOIN sga_ciclos CIC ON (CIC.ciclo = MC.ciclo)
                    JOIN sga_ciclos_plan CP ON (CIC.ciclo = CP.ciclo)
                    WHERE CP.plan LIKE '50%'
                    AND M.materia = $materia ";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        switch (count($result))
        {
            case 1: return ($result[0]['CICLO']); 
            case 2: return ('FyP'); 
        }
    }
                
    /**
     * parametros: anio_academico, periodo, materia
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function get_comisiones_promo_de_materia($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $materia = $parametros['materia'];
        
        $promociones = '4,6,9,10';
        //$regulares = '3,4,5,7,8,10';
        //
        //S�lo retorna las comisiones que son promocionables
        $sql = "SELECT  C.comision, 
                        C.nombre AS comision_nombre, 
                        C.anio_academico, 
                        C.periodo_lectivo,
                        C.escala_notas, 
                        C.carrera, 
                        DECODE(C.turno,'T', 'Tarde', 'N', 'Noche', 'M', 'Ma�ana', 'No informa') as turno,
                        EN.nombre AS escala_notas_nombre,
                        UC.porc_parciales,
                        UC.porc_integrador,
                        UC.porc_trabajos
                FROM sga_comisiones C
                JOIN sga_escala_notas EN ON (EN.escala_notas = C.escala_notas)
                LEFT JOIN ufce_comisiones_porc_notas UC ON (UC.comision = C.comision)
                WHERE C.estado = 'A'
                        AND EN.escala_notas IN ($promociones)
                        AND C.anio_academico = $anio_academico
                        AND C.materia = $materia ";

        if ($periodo != "''")
        {
            $sql .= " AND C.periodo_lectivo = $periodo";
        }
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: comision
    * cache: memoria
    * filas: n
    */
    function get_porcentajes_instancias($parametros)
    {
        $comision = $parametros['comision'];
        $sql = "SELECT porc_parciales, porc_integrador, porc_trabajos
                    FROM ufce_comisiones_porc_notas
                WHERE   comision = $comision";
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos[0];  
    }
    
    /**
    * parametros: comision, porc_parciales, porc_integrador, porc_trabajos
    * cache: memoria
    * filas: n
    */
    function update_porcentajes_instancias($parametros)
    {
        $comision = $parametros['comision'];
        $porc_parciales = $parametros['porc_parciales'];
        $porc_integrador = $parametros['porc_integrador'];
        $porc_trabajos = $parametros['porc_trabajos'];

        $sql = "UPDATE ufce_comisiones_porc_notas 
                    SET porc_parciales = $porc_parciales,
                        porc_integrador = $porc_integrador,
                        porc_trabajos = $porc_trabajos
                    WHERE comision = $comision";
        $datos = kernel::db()->ejecutar($sql);
        return $datos;
    }
    
    /**
    * parametros: comision, porc_parciales, porc_integrador, porc_trabajos
    * cache: memoria
    * filas: n
    */
    function set_porcentajes_instancias($parametros)
    {
        $existe = $this->get_porcentajes_instancias($parametros);
        if ($existe)
        {
            return $this->update_porcentajes_instancias($parametros);
        }
        else
        {
            $comision = $parametros['comision'];
            $porc_parciales = $parametros['porc_parciales'];
            $porc_integrador = $parametros['porc_integrador'];
            $porc_trabajos = $parametros['porc_trabajos'];

            $sql = "INSERT INTO ufce_comisiones_porc_notas (comision, porc_parciales, porc_integrador, porc_trabajos) VALUES
                        ($comision, $porc_parciales, $porc_integrador, $porc_trabajos)";
            $datos = kernel::db()->ejecutar($sql);
            return $datos;
        }
    }
    
    
    /**
    * parametros: comision
    * cache: memoria
    * filas: n
    */
    function get_nombre_comision($parametros)
    {
        $comision = $parametros['comision'];

        $sql = "SELECT nombre
                    FROM sga_comisiones
                WHERE   comision = $comision";
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos[0]['NOMBRE'];  
    }

    /**
    * parametros: materia
    * cache: memoria
    * filas: n
    */
    function get_nombre_materia($parametros)
    {
        if (isset($parametros['materia']))
        {
            $materia = $parametros['materia'];
        
            $sql = "SELECT nombre
                        FROM sga_materias
                    WHERE   materia = $materia";
            $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            return $datos[0]['NOMBRE'];  
        }
        if (isset($parametros['comision']))
        {
            $comision = $parametros['comision'];
        
            $sql = "SELECT nombre
                        FROM sga_materias
                    WHERE   materia IN (
                                SELECT materia FROM sga_comisiones WHERE comision = $comision)";
            $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            return $datos[0]['NOMBRE'];  
        }
        return '';
    }
    
    /**
    * parametros: comision
    * cache: memoria
    * filas: n
    */
    function get_nombre_materia_de_comision($parametros)
    {
        $comision = $parametros['comision'];

        $sql = "SELECT nombre
                    FROM sga_materias
                WHERE   materia IN (
                            SELECT materia FROM sga_comisiones WHERE comision = $comision)";
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos[0]['NOMBRE'];  
    }

    /**
     * parametros: anio_academico, periodo, materia
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function get_comisiones_de_materia_con_dias_de_clase($parametros)
    {
        /*  3	Reales Regular
            4	Reales Promoci�
            5	Aprob/Desaprob
            6	Promocion Oblig
            7	Talleres
            8	Regulares 50
            9	Promociones 50
            10	Prom y Reg 50         */
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $materia = $parametros['materia'];
        //Retorna todas las comisiones de la materia detallando si es regular, promoci�n o ambas
        //S�lo retorna las comisiones que tienen d�as de cursada asignados
        //DECODE(C.turno,'T', 'Tarde', 'N', 'Noche', 'M', 'Ma�ana', 'No informa') as turno, 
         $sql = "SELECT  C.comision, 
                        C.nombre AS comision_nombre, 
                        C.anio_academico, 
                        C.periodo_lectivo,
                        CASE 
                            WHEN C.escala_notas = 3 THEN 'R'
                            WHEN C.escala_notas = 4 THEN 'PyR'
                            WHEN C.escala_notas = 5 THEN 'R'
                            WHEN C.escala_notas = 6 THEN 'P'
                            WHEN C.escala_notas = 7 THEN 'R'
                            WHEN C.escala_notas = 8 THEN 'R'
                            WHEN C.escala_notas = 9 THEN 'P'
                            WHEN C.escala_notas = 10 THEN 'PyR'
                        END AS escala,
                        C.turno,
                        C.carrera,
                        O.observaciones
                FROM sga_comisiones C
                JOIN sga_escala_notas EN ON (EN.escala_notas = C.escala_notas)
                LEFT JOIN ufce_cron_eval_parc_obs O ON (O.comision = C.comision)
                WHERE C.estado = 'A'
                        AND C.anio_academico = $anio_academico
                        AND C.materia = $materia 
                        AND C.comision IN (SELECT comision FROM sga_calendcursada)";  
       
        if ($periodo != "''")
        {
            $sql .= " AND C.periodo_lectivo = $periodo";
        }
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
 
    /**
     * parametros: comision
     * cache: no
     * filas: n
     */
    function get_dias_clase($parametros)
    {
        $comision = $parametros['comision'];
        $sql = "SELECT DISTINCT sga_asignaciones.dia_semana - 1 AS dia_semana, 
                                sga_asignaciones.hs_comienzo_clase, 
                                sga_asignaciones.hs_finaliz_clase,
                                CASE 
                                    when sga_asignaciones.dia_semana = 1 then 'Domingo'
                                    when sga_asignaciones.dia_semana = 2 then 'Lunes'
                                    when sga_asignaciones.dia_semana = 3 then 'Martes'
                                    when sga_asignaciones.dia_semana = 4 then 'Mi�rcoles'
                                    when sga_asignaciones.dia_semana = 5 then 'Jueves'
                                    when sga_asignaciones.dia_semana = 6 then 'Viernes'
                                    when sga_asignaciones.dia_semana = 7 then 'S�bado'
                                END AS dia_nombre
                    FROM    sga_comisiones, 
                            sga_calendcursada,
                            sga_asignaciones
		WHERE   sga_comisiones.comision = sga_calendcursada.comision
                        AND sga_calendcursada.comision = $comision
                        AND sga_calendcursada.asignacion = sga_asignaciones.asignacion
                ORDER BY 1";
        
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: anio_academico, periodo, materia
     * cache: no
     * filas: n
     */
    function get_tipo_escala_de_materia($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $materia = $parametros['materia'];
        
        $promociones = '4,6,9,10';
        $regulares = '3,4,5,7,8,10';
        
        $sql = "SELECT  DISTINCT escala_notas
                FROM sga_comisiones
                WHERE estado = 'A'
                        AND anio_academico = $anio_academico
                        AND periodo_lectivo = $periodo
                        AND materia = $materia 
			AND escala_notas IN ($promociones)";
        $prom = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        
        $sql = "SELECT  DISTINCT escala_notas
                FROM sga_comisiones
                WHERE estado = 'A'
                        AND anio_academico = $anio_academico
                        AND periodo_lectivo = $periodo
                        AND materia = $materia 
			AND escala_notas IN ($regulares)";
        $reg = kernel::db()->consultar($sql, db::FETCH_ASSOC);

        if (count($prom) > 0)
        {
            if (count($reg) > 0)
            {
                return 'PyR';
            }
            else
            {
                return 'P';
            }
        }
        if (count($reg) > 0)
        {
            return 'R';
        }
        return '';        
    }
    
   
    /**
     * parametros: anio_academico, periodo
     * cache: no
     * filas: n
     */
    function get_periodos_evaluacion($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $sql = "SELECT  orden,
                        fecha_inicio, 
                        fecha_fin
                    FROM ufce_eval_parc_periodos
                WHERE anio_academico = $anio_academico
                AND periodo_lectivo = $periodo";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
//    /**
//     * parametros: anio_academico, periodo, comision, pertenece_fundamento, evaluacion
//     * cache: no
//     * filas: n
//     */
//    function get_posibles_fechas_eval($parametros)
//    {
//        $anio_academico = $parametros['anio_academico'];
//        $periodo = $parametros['periodo'];
//        $comision = $parametros['comision'];
//        $pertenece_fundamento = $parametros['pertenece_fundamento'];
//        $evaluacion = $parametros['evaluacion'];
//        
//        /** EVALUACIONES:
//         ** Promocion
//            21	Primer Parcial Promo
//            2	segundo parcial
//            7	Recuperatorio Unico
//            14	integrador
//         * 
//         ** Regular 
//            1	Primer Parcial Regular
//            4	primer recuperatorio
//            5	segundo recuperatorio
//        */
//        
//        switch ((string) trim($evaluacion))
//        {
//            case "'1'": ($pertenece_fundamento) ? $orden = 1 : $orden = '1,2'; break;
//            case "'2'": $orden = 2; break;
//            case "'7'": $orden = 2; break;
//            case "'14'": ($pertenece_fundamento) ? $orden = 3 : $orden = '2,3'; break;
//            case "'21'": $orden = 1; break;
//            case "'4'": $orden = '1,2'; break;
//            case "'5'": ($pertenece_fundamento) ? $orden = '2,3' : $orden = 3; break;
//        }
//        $sql = "SELECT sga_calendcursada.fecha 
//                        FROM sga_calendcursada, ufce_eval_parc_periodos
//                    WHERE sga_calendcursada.comision = $comision
//                    AND sga_calendcursada.fecha >= ufce_eval_parc_periodos.fecha_inicio
//                    AND sga_calendcursada.fecha <= ufce_eval_parc_periodos.fecha_fin
//                    AND sga_calendcursada.valido = 'S'
//                    AND ufce_eval_parc_periodos.anio_academico = $anio_academico
//                    AND ufce_eval_parc_periodos.periodo_lectivo = $periodo
//                    AND ufce_eval_parc_periodos.orden IN ($orden)
//                    AND sga_calendcursada.fecha NOT IN (SELECT fecha FROM sga_calend_no_lab)                        
//                ORDER BY 1";
//
//        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
//    }
    
    /**
     * parametros: materia
     * cache: no
     * filas: n
     */
    function get_materias_mismo_mix($parametros)
    {
        $materia = $parametros['materia'];
        $sql = "SELECT DISTINCT materia 
                    FROM ufce_mixes A 
                    WHERE anio_de_cursada IN (
                                    SELECT anio_de_cursada FROM ufce_mixes WHERE ufce_mixes.mix = A.mix AND materia = $materia
                            )
                    AND materia <> $materia";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: materia
     * cache: no
     * filas: n
     */
    function get_fechas_eval_asignadas($parametros)
    {
        $materia = $parametros['materia'];
        $sql = "SELECT DISTINCT DATE(fecha_hora) AS fecha
                    FROM sga_cron_eval_parc 
                    WHERE comision IN (
                                    SELECT comision FROM sga_comisiones WHERE materia = $materia
                            ) 
                UNION
                SELECT DISTINCT DATE(fecha_hora) AS fecha
                    FROM ufce_cron_eval_parc 
                    WHERE comision IN (
                                    SELECT comision FROM sga_comisiones WHERE materia = $materia
                            ) 
                        AND estado IN ('A', 'P')";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: comision
     * cache: no
     * filas: n
     */
    function get_fechas_no_validas($parametros)
    {
        $comision = $parametros['comision'];
        $sql = "SELECT fecha FROM sga_calendcursada
                    WHERE comision = $comision AND valido = 'N'";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
     * parametros: comision, dia_semana
     * cache: no
     * filas: n
     */
    function get_hora_inicio($parametros)
    {
//        print_r('<br>'.__FILE__.'-'.__LINE__.'<br>');
//        print_r($parametros);
        $comision = $parametros['comision'];
        $dia_semana = $parametros['dia_semana'];
        $sql = "SELECT hs_comienzo_clase
                    FROM sga_asignaciones 
                WHERE asignacion IN (SELECT asignacion 
                                        FROM sga_calendcursada 
                                            WHERE comision = $comision)
                                            AND dia_semana = $dia_semana";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: comision, evaluacion, escala_notas, fecha_hora
    * cache: no
    * filas: n
    */
    function alta_propuesta_evaluacion_parcial($parametros)
    {
        $comision = $parametros['comision'];
        $evaluacion = $parametros['evaluacion'];
        $escala = $parametros['escala_notas'];
        $fecha_hora = $parametros['fecha_hora'];
        $sql = "SELECT estado FROM ufce_cron_eval_parc 
                    WHERE comision = $comision 
                        AND evaluacion = $evaluacion";
        $estado = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        
        if (!empty($estado) AND $estado[0]['ESTADO'] == 'P')
        {

            //$sql = "EXECUTE PROCEDURE sp_i_atrcroevalpar($comision, $evaluacion, $escala, $fecha_hora)";
            $sql = "UPDATE ufce_cron_eval_parc
                        SET fecha_hora = $fecha_hora
                    WHERE comision = $comision 
                    AND evaluacion = $evaluacion";
        }
        else
        {
            $sql = "INSERT INTO ufce_cron_eval_parc (comision, evaluacion, fecha_hora, estado)
                        VALUES ($comision, $evaluacion, $fecha_hora, 'P')";
        }
        return util::ejecutar_procedure($sql);
    }
    
    /**
    * parametros: comision, evaluacion
    * cache: no
    * filas: n
    */
    function get_evaluaciones_existentes($parametros)
    {
        $comision = $parametros['comision'];
        $evaluacion = $parametros['evaluacion'];
        $sql = "SELECT fecha_hora, 'A' as estado
                    FROM sga_cron_eval_parc 
                        WHERE comision = $comision
                        AND evaluacion = $evaluacion";
        $resultado = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        if (empty($resultado))
        {
            $sql = "SELECT fecha_hora, estado
                    FROM ufce_cron_eval_parc 
                        WHERE comision = $comision
                        AND evaluacion = $evaluacion";
            $resultado = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        }
        return $resultado;
    }
    
    
    /**
    * parametros: comision, observaciones
    * cache: no
    * filas: n
    */
    function set_evaluaciones_observaciones($parametros)
    {
        $comision = $parametros['comision'];
        $evaluacion = $parametros['evaluacion'];
        $observaciones = $parametros['observaciones'];
        $sql = "SELECT *
                FROM ufce_cron_eval_parc_obs 
                    WHERE comision = $comision";
        $obs = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        if (count($obs) > 0)
        {
            $sql = "UPDATE ufce_cron_eval_parc_obs 
                        SET observaciones = $observaciones
                    WHERE comision = $comision";
        }
        else
        {
            $sql = "INSERT INTO ufce_cron_eval_parc_obs (comision, observaciones)
                        VALUES ($comision, $observaciones)";
        }
        return kernel::db()->ejecutar($sql);
    }
    
    /**
    * parametros: comision, evaluacion, escala_notas, fecha_hora
    * cache: no
    * filas: n
    */
    function alta_evaluacion_parcial($parametros)
    {
        $comision = $parametros['comision'];
        $evaluacion = $parametros['evaluacion'];
        $escala = $parametros['escala_notas'];
        $fecha_hora = $parametros['fecha_hora'];

        $sql = "EXECUTE PROCEDURE sp_i_atrcroevalpar($comision, $evaluacion, $escala, $fecha_hora)";
        $result1 = util::ejecutar_procedure($sql);

        if ($result1 == 'OK')
        {
            $sql = "UPDATE ufce_cron_eval_parc
                        SET estado = 'A'
                    WHERE comision = $comision 
                    AND evaluacion = $evaluacion";
            $result2 = kernel::db()->ejecutar($sql);
            return ("Se dio de alta correctamente la evaluaci�n $evaluacion para la comisi�n $comision. ");
        }
        else
        {
            return $result1;
        }
        
    }
    
    /**
    * parametros: comision
    * cache: no
    * filas: n
    */
    function get_materia($parametros)
    {
        $comision = $parametros['comision'];
        $sql = "SELECT materia FROM sga_comisiones WHERE comision = $comision";
        $materia = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $materia[0]['MATERIA'];
    }
        
}
