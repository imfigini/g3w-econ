<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\util;

class fechas_parciales 
{

    /**
     * parametros: anio_academico, periodo, materia
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function get_comisiones_de_materia_con_dias_de_clase($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $materia = $parametros['materia'];
        //Retorna todas las comisiones de la materia 
        //Sólo retorna las comisiones que tienen días de cursada asignados
        //DECODE(C.turno,'T', 'Tarde', 'N', 'Noche', 'M', 'Mañana', 'No informa') as turno, 
         $sql = "SELECT sga_comisiones.comision, 
                        sga_comisiones.nombre AS comision_nombre, 
                        sga_comisiones.turno,
                        sga_comisiones.carrera
				FROM    sga_comisiones
                WHERE sga_comisiones.estado = 'A'
                        AND sga_comisiones.anio_academico = $anio_academico
                        AND sga_comisiones.materia = $materia 
						AND sga_comisiones.comision IN (SELECT comision FROM sga_calendcursada) ";  
      
        if ($periodo != "''")
        {
            $sql .= " AND sga_comisiones.periodo_lectivo = $periodo";
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
		//dia_semana -> 0 = domingo
		$sql = "SELECT DISTINCT sga_asignaciones.dia_semana - 1 AS dia_semana, 
                                sga_asignaciones.hs_comienzo_clase, 
                                sga_asignaciones.hs_finaliz_clase
                    FROM    sga_comisiones, 
                            sga_calendcursada,
                            sga_asignaciones
					WHERE   sga_comisiones.comision = sga_calendcursada.comision
                        AND sga_calendcursada.comision = {$parametros['comision']}
                        AND sga_calendcursada.asignacion = sga_asignaciones.asignacion
                ORDER BY 1";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}
	
	/**
    * parametros: comision
    * cache: no
    * filas: n
    */
    function get_fechas_asignadas_o_solicitadas($parametros)
    {
		//Devuelve la fecha de la evaluación, o la fecha solicitada si es que aún no se creó la instancia
		$sql = "SELECT 	NVL(C.evaluacion, U.evaluacion), 
						DECODE(NVL(C.evaluacion, U.evaluacion), 1, 'PROMO1', 2, 'PROMO2', 7, 'RECUP', 14, 'INTEG') AS eval_nombre, 
						NVL(C.fecha_hora, U.fecha_hora)::DATE AS fecha,
						to_char(NVL(C.fecha_hora, U.fecha_hora), '%H:%M:%S') AS hora,
						NVL(U.estado, 'A') AS estado
					FROM sga_cron_eval_parc C
					FULL OUTER JOIN ufce_cron_eval_parc U ON (U.comision = C.comision and U.evaluacion = C.evaluacion)
					WHERE (C.comision = {$parametros['comision']} 
						OR U.comision = {$parametros['comision']}) ";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}
	
	/**
    * parametros: materia, anio_academico, periodo
    * cache: no
    * filas: n
    */
    function get_fechas_eval_ocupadas($parametros)
    {
        $materia = $parametros['materia'];
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $sql = "SELECT 	DISTINCT C.evaluacion, 
						DECODE(C.evaluacion, 1, 'PROMO1', 2, 'PROMO2', 7, 'RECUP', 14, 'INTEG') AS eval_nombre, 
						NVL(C.fecha_hora, U.fecha_hora)::DATE AS fecha
				FROM sga_cron_eval_parc C
				LEFT JOIN ufce_cron_eval_parc U  on (U.comision = C.comision and U.evaluacion = C.evaluacion)
				WHERE C.comision IN (SELECT comision FROM sga_comisiones 
											WHERE materia = $materia
											AND anio_academico = $anio_academico
											AND periodo_lectivo = $periodo) 
				ORDER BY 3";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}
	
	/**
     * parametros: anio_academico, periodo, materia
     * cache: no
     * filas: n
     */
    function get_fechas_no_validas_materia($parametros)
    {
		//Retrona todas las fechas en las que alguna de las comisiones de la materia tiene asignado el día de cursada como no válido
        $sql = "SELECT DISTINCT fecha 
				FROM sga_calendcursada
                    WHERE comision IN (SELECT comision 
											FROM sga_comisiones 
											WHERE materia = {$parametros['materia']}
												AND anio_academico = {$parametros['anio_academico']}
												AND periodo_lectivo = {$parametros['periodo']})
					AND valido = 'N'
				ORDER BY fecha ";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}
	
	/**
    * parametros: materia, anio_academico, periodo
    * cache: no
    * filas: 1
    */
    function get_evaluaciones_observaciones($parametros)
    {
        $materia = $parametros['materia'];
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $sql = "SELECT observaciones
                FROM ufce_cron_eval_parc_obs 
                    WHERE materia = $materia
                    AND anio_academico = $anio_academico
					AND periodo_lectivo = $periodo";
        return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}
	

	/**
    * parametros: comision
    * cache: no
    * filas: 1
	*/
	function get_datos_comision($parametros)
	{
		//Devuelve la materia, anio_academico y periodo al cual pertenece
		$sql = "SELECT 	anio_academico, 
						periodo_lectivo,
						materia
					FROM sga_comisiones
					WHERE comision = {$parametros['comision']} ";
		return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	}

	/**
    * parametros: comision
    * cache: no
    * filas: n
    */
    function get_fechas_eval_solicitadas($parametros)
    {
        $sql = "SELECT 	DISTINCT evaluacion, 
						DECODE(evaluacion, 1, 'PROMO1', 2, 'PROMO2', 7, 'RECUP', 14, 'INTEG') AS eval_nombre, 
						fecha_hora,
						estado
				FROM ufce_cron_eval_parc
				WHERE comision = {$parametros['comision']} ";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}

	/**
    * parametros: comision
    * cache: no
    * filas: n
    */
    function get_fechas_eval_asignadas($parametros)
    {
        $sql = "SELECT 	DISTINCT evaluacion, 
						DECODE(evaluacion, 1, 'PROMO1', 2, 'PROMO2', 7, 'RECUP', 14, 'INTEG', 15, 'TP') AS eval_nombre, 
						fecha_hora
				FROM sga_cron_eval_parc
				WHERE comision = {$parametros['comision']} ";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}

	/**
    * parametros: anio, periodo
    * cache: no
    * filas: 1
    */
	function hoy_dentro_de_periodo($parametros)
	{
		/* Verifica si el dia de hoy está dentro del cuatrimestre recibido como parametro */
		$sql = "SELECT COUNT(*) as cant
					FROM sga_periodos_lect 
					WHERE anio_academico = {$parametros['anio']}
						AND periodo_lectivo = {$parametros['periodo']}
						AND today between fecha_inicio AND fecha_fin ";
		$resultado = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		if ($resultado['CANT'] > 0) {
			return true;
		}
		return false;
	}
}

