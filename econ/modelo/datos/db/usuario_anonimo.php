<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
use siu\operaciones\_comun\util\caracteres_especiales;
use \siu\modelo\entidades\parametro as parametro1;

class usuario_anonimo extends \siu\modelo\datos\db\usuario_anonimo
{

    /**
     * Devuelve materias de una carrera o todas las materias de la base
     * parametros: _ua, materia, carrera, plan, anio_cursada
     * no_quote: materia
     * cache: memoria
     * filas: n
     */
    function lista_materias_carrera_x_anio($parametros)
    {
        $filtro_materia = '';
        if ($parametros['materia'] != ""){
            $termino = strtolower($parametros['materia']);
            $term_materia = kernel::db()->quote_like($termino, '%', '%');
            $term_sin_car_esp = kernel::db()->quote_like(caracteres_especiales::limpiar($termino), '%', '%');
            $filtro_materia = " AND (
                                    LOWER(sga_atrib_mat_plan.nombre_materia) LIKE $term_materia 
                                    OR LOWER(sga_atrib_mat_plan.nombre_materia) LIKE $term_materia
                                    OR LOWER(sga_atrib_mat_plan.nombre_materia) LIKE $term_sin_car_esp 
                                    OR LOWER(sga_atrib_mat_plan.nombre_materia) LIKE $term_sin_car_esp
                                ) 
                              ";
        }

        $filtro_carrera = '';
        $filtro_plan = '';
        $filtro_anio = '';

        if ($parametros['carrera'] != "''"){
            $filtro_carrera = " AND sga_atrib_mat_plan.carrera = {$parametros['carrera']}";
        }

        if ($parametros['plan'] != "''"){
            $filtro_plan = " AND sga_atrib_mat_plan.plan = {$parametros['plan']}";
        }

        if ($parametros['anio_cursada'] != "''"){
            $filtro_anio = " AND sga_atrib_mat_plan.anio_de_cursada = {$parametros['anio_cursada']}";
        }

        $sql = "SELECT DISTINCT 
                        sga_atrib_mat_plan.unidad_academica, 
                        sga_atrib_mat_plan.materia, 
                        sga_atrib_mat_plan.nombre_materia
                    FROM sga_atrib_mat_plan,
                        sga_planes
                    WHERE sga_atrib_mat_plan.unidad_academica  = {$parametros['_ua']}
                    AND sga_planes.carrera = sga_atrib_mat_plan.carrera 
                    AND sga_planes.plan = sga_atrib_mat_plan.plan 
                    AND sga_planes.version_actual = sga_atrib_mat_plan.version 
                    AND sga_planes.estado IN ('A', 'V')
                    AND sga_atrib_mat_plan.tipo_materia <> 'G'
                    $filtro_carrera
                    $filtro_plan
                    $filtro_anio
                    $filtro_materia
                ORDER BY sga_atrib_mat_plan.nombre_materia
            ";

        //kernel::log()->add_debug('iris lista_materias', $sql);
        $datos = kernel::db()->consultar($sql);

        $nuevo = array();
        foreach ($datos as $clave => $dato){
            $nuevo[$clave] = $dato;
            $nuevo[$clave]['_ID_'] = catalogo::generar_id($dato['MATERIA']);
        }
        return $nuevo;
    }

    /**
     * parametros: _ua, carrera, plan, anio_cursada, materia
     * cache: no
     * filas: n
     */
    function fechas_parciales_usuario_anonimo($parametros)
    {
        // Filtros
        $filtro_carrera = '';
        $filtro_plan = '';
        $filtro_anio_cursada = '';
        $filtro_materia = '';
        
        if ($parametros['carrera'] != "''"){
            $filtro_carrera = " AND sga_atrib_mat_plan.carrera = {$parametros['carrera']} ";
        }

        if ($parametros['plan'] != "''"){
            $filtro_plan = " AND sga_atrib_mat_plan.plan = {$parametros['plan']} ";
        }

        if ($parametros['anio_cursada'] != "''"){
            $filtro_anio_cursada = " AND sga_atrib_mat_plan.anio_de_cursada = {$parametros['anio_cursada']} ";
        }
        if ($parametros['materia'] != "''"){
            $filtro_materia = " AND sga_atrib_mat_plan.materia = {$parametros['materia']} ";
        }

        $sql = "SELECT  DISTINCT 
                        sga_atrib_mat_plan.materia,
                        sga_atrib_mat_plan.nombre_materia,
                        sga_comisiones.comision,
                        sga_comisiones.nombre AS comision_nombre, 
                        sga_cron_eval_parc.fecha_hora,
                        INITCAP(sga_eval_parc.descripcion) AS eval_parcial,
                        to_char(sga_cron_eval_parc.fecha_hora, '%d/%m/%Y') AS fecha_parcial, 
                        to_char(sga_cron_eval_parc.fecha_hora, '%H:%M') AS hora_inicio
                    FROM    sga_cron_eval_parc,
                            sga_comisiones,
                            sga_periodos_lect, 
                            sga_eval_parc, 
                            sga_atrib_mat_plan,
                            sga_planes
                    WHERE sga_cron_eval_parc.comision = sga_comisiones.comision
                    AND sga_comisiones.anio_academico = sga_periodos_lect.anio_academico
                    AND sga_comisiones.periodo_lectivo = sga_periodos_lect.periodo_lectivo
                    AND TODAY BETWEEN sga_periodos_lect.fecha_inicio AND sga_periodos_lect.fecha_fin
                    AND sga_eval_parc.evaluacion = sga_cron_eval_parc.evaluacion
                    AND sga_atrib_mat_plan.carrera = sga_planes.carrera
                    AND sga_atrib_mat_plan.plan = sga_planes.plan
                    AND sga_atrib_mat_plan.version = sga_planes.version_actual
                    AND sga_planes.estado IN ('A', 'V')
                    AND sga_atrib_mat_plan.materia = sga_comisiones.materia
                    AND sga_cron_eval_parc.fecha_hora >= TODAY
                    AND sga_eval_parc.evaluacion <> 15
                    $filtro_carrera
                    $filtro_plan
                    $filtro_anio_cursada
                    $filtro_materia
                
                ORDER BY 2,4,5 ";

        //kernel::log()->add_debug('iris_buscar_parciales', $sql);
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);

        $result = array();
        foreach ($datos as $dato) 
        {
            $comision = $dato['COMISION'];
            $fecha_parcial = $dato['FECHA_PARCIAL'];
            $sql = "SELECT	AL.nombre_aula, 
                        ED.nombre AS edificio
                    FROM sga_asign_clases AC
                    JOIN sga_asignaciones A ON (A.asignacion = AC.asignacion)
                    JOIN sga_edificios ED ON (ED.edificio = A.edificio)
                    JOIN sga_aulas AL ON (AL.aula = A.aula AND AL.edificio = ED.edificio)
                    WHERE AC.comision = $comision
                        AND A.dia_semana = WEEKDAY('$fecha_parcial')+1";
			//kernel::log()->add_debug('iris_buscar_aulas', $sql);
			$edif_aula = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
            if (!empty($edif_aula)) {
                $dato['EDIFICIO'] = $edif_aula['EDIFICIO'];
                $dato['AULA'] = $edif_aula['NOMBRE_AULA'];
            }       
            $result[] = $dato;            
        }
        return $result;
    }

} 
