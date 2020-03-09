<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;

class prom_directa
{
	/**
    * parametros: anio_academico, periodo
    * cache: no
    * filas: n
    */
	function get_datos_materias_promo_directa($parametros)
	{
		$this->alta_materias_prom_directa($parametros);
		$sql = "SELECT DISTINCT U.materia, 
								M.nombre AS nombre_materia,
								U.promo_directa
                        FROM ufce_materias_promo_directa U
                        JOIN sga_materias M ON (M.materia = U.materia)
                    WHERE	U.anio_academico = {$parametros['anio_academico']}
						AND U.periodo_lectivo = {$parametros['periodo']} 
				ORDER BY 2";
		return kernel::db()->consultar($sql, db::FETCH_ASSOC);
	}

	/**
    * parametros: anio_academico, periodo
    * cache: no
    * filas: n
    */
	private function alta_materias_prom_directa($parametros)
	{
		$sql = "INSERT INTO ufce_materias_promo_directa (anio_academico, periodo_lectivo, materia, promo_directa)
				SELECT DISTINCT C.anio_academico, 
								C.periodo_lectivo, 
								C.materia, 
								'N'
						FROM sga_comisiones C
						JOIN sga_atrib_mat_plan M ON (M.materia = C.materia)
						JOIN sga_planes PL ON (PL.unidad_academica = M.unidad_academica AND PL.carrera = M.carrera AND PL.plan = M.plan and PL.version_actual = M.version)
					WHERE   PL.estado = 'V'
					AND C.anio_academico = {$parametros['anio_academico']}
					AND C.periodo_lectivo = {$parametros['periodo']}
					and C.materia not in (select materia from ufce_materias_promo_directa
								where anio_academico = C.anio_academico
								and periodo_lectivo = C.periodo_lectivo) ";
		return kernel::db()->ejecutar($sql);
	}

	/**
    * parametros: anio_academico, periodo
    * cache: no
    * filas: n
    */
	function resetear_prom_directa($parametros)
	{
		$sql = "UPDATE ufce_materias_promo_directa 
					SET promo_directa = 'N'
				WHERE   anio_academico = {$parametros['anio_academico']}
				AND periodo_lectivo = {$parametros['periodo']} ";
		return kernel::db()->ejecutar($sql);
	}

	/**
    * parametros: anio_academico, periodo, materia
    * cache: no
    * filas: n
    */
	function set_prom_directa($parametros)
	{
		$sql = "UPDATE ufce_materias_promo_directa 
					SET promo_directa = 'S'
				WHERE   anio_academico = {$parametros['anio_academico']}
				AND periodo_lectivo = {$parametros['periodo']} 
				AND materia = {$parametros['materia']} ";
		return kernel::db()->ejecutar($sql);
	}

	/**
    * parametros: anio_academico, periodo, materia
    * cache: no
    * filas: 1
	*/
	function is_promo_directa($parametros)
	{
		$sql = "SELECT COUNT(*) as existe
                        FROM ufce_materias_promo_directa
                    WHERE   anio_academico = {$parametros['anio_academico']}
						AND periodo_lectivo = {$parametros['periodo']}
						AND materia = {$parametros['materia']} 
						AND promo_directa = 'S' ";
		$existe = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		if ($existe['EXISTE'] > 0) {
			return true;
		}
		return false;
	}

	/**
    * parametros: anio_academico, periodo, materia
    * cache: no
    * filas: 1
	*/
	function is_promo($parametros)
	{
		$sql = "SELECT COUNT(*) as existe
                        FROM sga_comisiones
                    WHERE   anio_academico = {$parametros['anio_academico']}
						AND periodo_lectivo = {$parametros['periodo']}
						AND materia = {$parametros['materia']} 
						AND escala_notas = 4 ";
		$existe = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
		if ($existe['EXISTE'] > 0) {
			return true;
		}
		return false;
	}

	/**
    * parametros: anio_academico, periodo, anio_academico_anterior, periodo_anterior
    * cache: no
    * filas: n
    */
    function replicar_materias_promo_directa($parametros)
    {
        //Resetea los del cuatrimestre actual
		$this->resetear_prom_directa($parametros);
        
        //Recupera los del mismo cuatrimestre del año anterior
        $sql = "SELECT materia, promo_directa
                    FROM ufce_materias_promo_directa
                    WHERE anio_academico = {$parametros['anio_academico_anterior']}
					AND periodo_lectivo = {$parametros['periodo_anterior']}";
        $resultado = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        
        //Asigna las materias por promo directa del mismo cuatrimestre del año anterior al actual
        foreach($resultado as $res)
        {
            $materia = json_encode($res['MATERIA']);
            $promo_directa = json_encode($res['PROMO_DIRECTA']);
            $sql = "UPDATE ufce_materias_promo_directa
						SET promo_directa = $promo_directa
                        WHERE anio_academico = {$parametros['anio_academico']}
						AND periodo_lectivo = {$parametros['periodo']}
						AND materia = $materia ";
            kernel::db()->ejecutar($sql);
        }
    }	
}
