<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;


class evaluaciones_parciales_calendario
{
    /**
     * parametros: anio_academico, periodo, materia, evaluacion
     * cache: no
     * filas: n
     */
    function get_fechas_propuestas($parametros)
    {
        $sql = "SELECT  ufce_cron_eval_parc.comision,
                        ufce_cron_eval_parc.evaluacion, 
                        ufce_cron_eval_parc.fecha_hora::DATE as fecha,
                        ufce_cron_eval_parc.estado,
                        sga_comisiones.escala_notas
                    from ufce_cron_eval_parc
                    join sga_comisiones on (sga_comisiones.comision = ufce_cron_eval_parc.comision)
                    where sga_comisiones.materia = {$parametros['materia']}
                    and ufce_cron_eval_parc.evaluacion = {$parametros['evaluacion']}
                    and sga_comisiones.anio_academico = {$parametros['anio_academico']}
                    and sga_comisiones.periodo_lectivo = {$parametros['periodo']}";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    

    /**
     * parametros: comision
     * cache: no
     * filas: 1
     */
    function get_hora_comienzo_clase($parametros)
    {
        $sql = "SELECT first 1 hs_comienzo_clase 
                    FROM sga_asignaciones 
                    WHERE asignacion IN ( 
                                SELECT asignacion FROM sga_calendcursada WHERE comision = {$parametros['comision']})";
        return kernel::db()->consultar_fila($sql, db::FETCH_NUM);
    }

    /**
     * parametros: comision, evaluacion
     * cache: no
     * filas: 1
     */
    function tiene_notas_cargadas($parametros)
    {
        $sql = "SELECT count(*) AS cant 
                    from sga_eval_parc_alum 
                        where comision = {$parametros['comision']}
                            and evaluacion = {$parametros['evaluacion']}";
        $notas = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
        if (isset($notas) and isset($notas['CANT']) and $notas['CANT']>0)
        {
            return true;
        }
        return false;
	}

	//  /**
    //  * parametros: comision, evaluacion
    //  * cache: no
    //  * filas: 1
    //  */
    // function get_fecha_asignada_o_propuesta($parametros)
    // {
    //     $sql = "SELECT  fecha_hora::DATE as fecha
    //                 from sga_cron_eval_parc
    //                 where comision = {$parametros['comision']}
    //                 and evaluacion = {$parametros['evaluacion']}";
	// 	$fecha = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
	// 	if (isset($fecha) and isset($fecha['FECHA']))
	// 	{
	// 		return $fecha;
	// 	}
	// 	$sql = "SELECT  fecha_hora::DATE as fecha
	// 				from ufce_cron_eval_parc
	// 				where comision = {$parametros['comision']}
	// 				and evaluacion = {$parametros['evaluacion']}";
	// 	return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
    // }
}
