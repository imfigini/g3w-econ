<?php
namespace econ\modelo\transacciones;

use kernel\kernel;
use siu\modelo\datos\catalogo;
//use siu\modelo\entidades\alumno_foto;

class carga_notas_cursada extends \siu\modelo\transacciones\carga_notas_cursada
{

	function info__autocalcular_nota_alumno($comision, $legajo)
	{
		$anio_academico = catalogo::consultar('carga_notas_cursada', 'get_anio_comision', Array('comision' => $comision));
		if ($anio_academico['ANIO_ACADEMICO'] < 2019) {
			return null;
		}

		/* Instancias de evaluacion: 
			22: 1er Parcial
			23: 2do Parcial
			24: Recuperatorio Global
			14: Integrador
			15: TP
		  Resultado: 
			A: Aprobo
			P: Promociono
			R: Reprobo
			U: Ausente
			L: Libre
		  Condicion:
			1: L
			2: U
			3: R
			4: A
			5: P
		  Estado:
			abandono
			listo
			va_integ
			va_recup
			falta_tp
			
		*/
		$eval = Array('PARC_1'=>22, 'PARC_2'=>23, 'RECUP'=>24, 'INTEG'=>14, 'TP'=>15);
		$cond = Array('L'=>1, 'U'=>2, 'R'=>3, 'A'=>4, 'P'=>5);

		$paso_parcial2 = catalogo::consultar('carga_evaluaciones_parciales', 'ya_paso_evaluacion', Array('comision' => $comision,
																										'evaluacion' => $eval['PARC_2']));
		//Si todavía no pasó la fecha del 2º parcial no se puede calcular nada
		if (!$paso_parcial2) {
			return null;
		}

		$notas_eval_alumno = self::get_notas_eval_alumno($comision, $legajo);
		kernel::log()->add_debug('notas_eval_alumno', $notas_eval_alumno);

		$porc_asistencia = catalogo::consultar('carga_evaluaciones_parciales', 'get_porc_asistencia', Array('comision'=>$comision, 'legajo'=>$legajo));
		$porc_asistencia = round($porc_asistencia, 2, PHP_ROUND_HALF_DOWN);

		if ($porc_asistencia < 60) {
			return Array('nota'=>null, 'resultado'=>'U', 'condicion'=>$cond['U'], 'asistencia'=>$porc_asistencia, 'estado'=>'abandono');
		}

		//Si ausente en ambos parciales
		if (!isset($notas_eval_alumno[$eval['PARC_1']]) && !isset($notas_eval_alumno[$eval['PARC_2']])) {
			return Array('nota'=>null, 'resultado'=>'U', 'condicion'=>$cond['U'], 'asistencia'=>$porc_asistencia, 'estado'=>'abandono');
		}

		$ponderaciones = self::get_ponderaciones_materia($comision);
		kernel::log()->add_debug('ponderaciones', $ponderaciones);
		
		$tiene_correlativas_cumplidas = self::tiene_correlativas_cumplidas($comision, $legajo);
		kernel::log()->add_debug('tiene_correlativas_cumplidas', $tiene_correlativas_cumplidas);
		// print_r('tiene_correlativas_cumplidas ');
		// print_r($tiene_correlativas_cumplidas);

		if ($tiene_correlativas_cumplidas) 
		{
			$nota = self::calcular_nota_A($legajo, $notas_eval_alumno, $porc_asistencia, $ponderaciones, $eval, $cond, $comision);
			if (isset($nota)) {
				return $nota;
			}
		}
		$nota = self::calcular_nota_B($legajo, $notas_eval_alumno, $porc_asistencia, $ponderaciones, $eval, $cond, $comision);
		if (isset($nota)) {
			return $nota;
		}
		return null;
	}

	function get_notas_eval_alumno($comision, $legajo)
	{
		/* 	22: 1er Parcial
			23: 2do Parcial
			24: Recuperatorio Global
			14: Integrador
			15: TP		*/
		$parametros = Array('comision' => $comision, 'legajo' => $legajo);
		$datos = catalogo::consultar('carga_evaluaciones_parciales', 'get_notas_eval_alumno', $parametros);
		$resultado = Array();
		foreach($datos as $dato) {
			$resultado[$dato['EVALUACION']] = Array('NOTA'=>$dato['NOTA'], 'RESULTADO'=>$dato['RESULTADO']);
		}
		return $resultado;
	}

	function tiene_correlativas_cumplidas($comision, $legajo)
	{
		$datos_comision = catalogo::consultar('carga_notas_cursada', 'get_datos_de_comision', Array('comision'=>$comision));
		$carrera_alumno = catalogo::consultar('carga_notas_cursada', 'get_carrera_incripto', Array('comision'=>$comision, 'legajo'=>$legajo));
		$parametros = Array(	'anio_academico'=>$datos_comision['ANIO_ACADEMICO'], 
								'periodo'=>$datos_comision['PERIODO_LECTIVO'], 
								'legajo'=>$legajo, 
								'carrera'=>$carrera_alumno['CARRERA'], 
								'materia'=>$datos_comision['MATERIA'] );				
		return catalogo::consultar('carga_evaluaciones_parciales', 'tiene_correlativas_cumplidas', $parametros);
	}

	function is_promo_directa($comision)
	{
		$datos_comision = catalogo::consultar('carga_notas_cursada', 'get_datos_de_comision', Array('comision'=>$comision));
		$parametros = Array(	'anio_academico'=>$datos_comision['ANIO_ACADEMICO'], 
								'periodo'=>$datos_comision['PERIODO_LECTIVO'], 
								'materia'=>$datos_comision['MATERIA'] );				
		return catalogo::consultar('prom_directa', 'is_promo_directa', $parametros);
	}
	
	function get_ponderaciones_materia($comision)
	{
		$datos_comision = catalogo::consultar('carga_notas_cursada', 'get_datos_de_comision', Array('comision'=>$comision));
		$parametros = Array(	'anio_academico'=>$datos_comision['ANIO_ACADEMICO'], 
								'periodo'=>$datos_comision['PERIODO_LECTIVO'], 
								'materia'=>$datos_comision['MATERIA'] );				
		$ponderaciones = Array();
		$parametros['calidad'] = 'P';
		$ponderaciones['P'] = catalogo::consultar('ponderacion_notas', 'get_ponderaciones_notas', $parametros);

		$parametros['calidad'] = 'R';
		$ponderaciones['R'] = catalogo::consultar('ponderacion_notas', 'get_ponderaciones_notas', $parametros);

		return $ponderaciones;
	}

	function verificar_nota_tp($notas_eval_alumno, $ponderacion_tp, $eval)
	{
		//Verifico si corresponde tener cargada la nota de TP y si la tiene cargada

		if ($ponderacion_tp > 0) {
			if (!isset($notas_eval_alumno[$eval])) {
				return -1;
			}
		}
		else {
			if (!isset($notas_eval_alumno[$eval])) {
				$notas_eval_alumno[$eval]['NOTA'] = 0;
			}
		}
		return $notas_eval_alumno[$eval];
	}

	function calcular_nota_A($legajo, $notas_eval_alumno, $porc_asistencia, $ponderaciones, $eval, $cond, $comision)
	{
		//Si faltó al menos a alguno de los parciales no puede promocionar
		if (!isset($notas_eval_alumno[$eval['PARC_1']]) || !isset($notas_eval_alumno[$eval['PARC_2']])) {
			return null;
		}

		$prom_parciales = ($notas_eval_alumno[$eval['PARC_1']]['NOTA'] + $notas_eval_alumno[$eval['PARC_2']]['NOTA'] ) / 2;

		$promo_directa = self::is_promo_directa($comision);
	
		if ($promo_directa) {
			if ($notas_eval_alumno[$eval['PARC_1']]['NOTA'] >= 8 && $notas_eval_alumno[$eval['PARC_2']]['NOTA'] >= 8) 
			{	
				$notas_eval_alumno[$eval['TP']] = self::verificar_nota_tp($notas_eval_alumno, $ponderaciones['D']['PORC_TRABAJOS'], $eval['TP']);
		
				//Si no tiene cargada la nota de TP
				if ($notas_eval_alumno[$eval['TP']] == -1) {
					return Array('nota'=>'', 'resultado'=>'', 'condicion'=>'', 'asistencia'=>$porc_asistencia, 'estado'=>'falta_tp');
				}
		
				$nota = $prom_parciales * $ponderaciones['P']['PORC_PARCIALES'] / 100
						+ $notas_eval_alumno[$eval['TP']]['NOTA'] * $ponderaciones['P']['PORC_TRABAJOS'] / 100;
				$nota = round($nota, 0, PHP_ROUND_HALF_DOWN);
				
				return Array('nota'=>$nota, 'resultado'=>'P', 'condicion'=>$cond['P'], 'asistencia'=>$porc_asistencia, 'estado'=>'listo');
			}
		}
		if ($prom_parciales >= 6) 
		{
			$paso_integrador = catalogo::consultar('carga_evaluaciones_parciales', 'ya_paso_evaluacion', Array('comision' => $comision,
																										'evaluacion' => $eval['INTEG']));
			if (!$paso_integrador) {
				return Array('nota'=>'', 'resultado'=>'', 'condicion'=>'', 'asistencia'=>$porc_asistencia, 'estado'=>'va_integ');
			}
			
			if (isset($notas_eval_alumno[$eval['INTEG']]))
			{
				$notas_eval_alumno[$eval['TP']] = self::verificar_nota_tp($notas_eval_alumno, $ponderaciones['P']['PORC_TRABAJOS'], $eval['TP']);
		
				//Si no tiene cargada la nota de TP
				if ($notas_eval_alumno[$eval['TP']] == -1) {
					return Array('nota'=>'', 'resultado'=>'', 'condicion'=>'', 'asistencia'=>$porc_asistencia, 'estado'=>'falta_tp');
				}
		
				$nota = $prom_parciales * $ponderaciones['P']['PORC_PARCIALES'] / 100
						+ $notas_eval_alumno[$eval['INTEG']]['NOTA'] * $ponderaciones['P']['PORC_INTEGRADOR'] / 100;
						+ $notas_eval_alumno[$eval['TP']]['NOTA'] * $ponderaciones['P']['PORC_TRABAJOS'] / 100;
				$nota = round($nota, 0, PHP_ROUND_HALF_DOWN);

				if ($notas_eval_alumno[$eval['INTEG']]['NOTA'] >= 6) 
				{
					if ($nota >= 6) {
						return Array('nota'=>$nota, 'resultado'=>'P', 'condicion'=>$cond['P'], 'asistencia'=>$porc_asistencia, 'estado'=>'listo');
					} else {
						return Array('nota'=>$nota, 'resultado'=>'A', 'condicion'=>$cond['A'], 'asistencia'=>$porc_asistencia, 'estado'=>'listo');
					}
				}
				else
				{
					return Array('nota'=>$nota, 'resultado'=>'A', 'condicion'=>$cond['A'], 'asistencia'=>$porc_asistencia, 'estado'=>'listo');
				}
			}
			else 
			{
				$notas_eval_alumno[$eval['TP']] = self::verificar_nota_tp($notas_eval_alumno, $ponderaciones['R']['PORC_TRABAJOS'], $eval['TP']);
		
				//Si no tiene cargada la nota de TP
				if ($notas_eval_alumno[$eval['TP']] == -1) {
					return Array('nota'=>'', 'resultado'=>'', 'condicion'=>'', 'asistencia'=>$porc_asistencia, 'estado'=>'falta_tp');
				}

				$nota = $prom_parciales * $ponderaciones['R']['PORC_PARCIALES'] / 100
						+ $notas_eval_alumno[$eval['TP']]['NOTA'] * $ponderaciones['R']['PORC_TRABAJOS'] / 100;
				$nota = round($nota, 0, PHP_ROUND_HALF_DOWN);

				if ($nota >= 4) {
					return Array('nota'=>$nota, 'resultado'=>'A', 'condicion'=>$cond['A'], 'asistencia'=>$porc_asistencia, 'estado'=>'listo');
				}  else {
					return Array('nota'=>$nota, 'resultado'=>'R', 'condicion'=>$cond['R'], 'asistencia'=>$porc_asistencia, 'estado'=>'listo');
				}
			}
		}
		return null;
	}	

	function calcular_nota_B($legajo, $notas_eval_alumno, $porc_asistencia, $ponderaciones, $eval, $cond, $comision)
	{
		//Si faltó a ambos parciales -> abandono
		if (!isset($notas_eval_alumno[$eval['PARC_1']]) && !isset($notas_eval_alumno[$eval['PARC_2']])) 
		{
			return Array('nota'=>null, 'resultado'=>'U', 'condicion'=>$cond['U'], 'asistencia'=>$porc_asistencia, 'estado'=>'abandono');
		}

		$notas_eval_alumno[$eval['TP']] = self::verificar_nota_tp($notas_eval_alumno, $ponderaciones['R']['PORC_TRABAJOS'], $eval['TP']);

		//Si asistió a ambos parciales
		if (isset($notas_eval_alumno[$eval['PARC_1']]) && isset($notas_eval_alumno[$eval['PARC_2']])) 
		{
			//Si no tiene cargada la nota de TP
			if ($notas_eval_alumno[$eval['TP']] == -1) {
				return Array('nota'=>'', 'resultado'=>'', 'condicion'=>'', 'asistencia'=>$porc_asistencia, 'estado'=>'falta_tp');
			}

			$prom_parciales = ($notas_eval_alumno[$eval['PARC_1']]['NOTA'] + $notas_eval_alumno[$eval['PARC_2']]['NOTA'] ) / 2;
			
			if ($prom_parciales >= 4) 
			{
				$nota = $prom_parciales * $ponderaciones['R']['PORC_PARCIALES'] / 100
						+ $notas_eval_alumno[$eval['TP']]['NOTA'] * $ponderaciones['R']['PORC_TRABAJOS'] / 100;
				$nota = round($nota, 0, PHP_ROUND_HALF_DOWN);

				if ($nota >= 4) {
					return Array('nota'=>$nota, 'resultado'=>'A', 'condicion'=>$cond['A'], 'asistencia'=>$porc_asistencia, 'estado'=>'listo');
				}
				else {
					return Array('nota'=>$nota, 'resultado'=>'R', 'condicion'=>$cond['R'], 'asistencia'=>$porc_asistencia, 'estado'=>'listo');
				}
			}
		}

		$paso_recup = catalogo::consultar('carga_evaluaciones_parciales', 'ya_paso_evaluacion', Array('comision' => $comision,
																								'evaluacion' => $eval['RECUP']));
		if (!$paso_recup) {
			return Array('nota'=>'', 'resultado'=>'', 'condicion'=>'', 'asistencia'=>$porc_asistencia, 'estado'=>'va_recup');
		}

		//Si no tiene cargada la nota de TP
		if ($notas_eval_alumno[$eval['TP']] == -1) {
			return Array('nota'=>'', 'resultado'=>'', 'condicion'=>'', 'asistencia'=>$porc_asistencia, 'estado'=>'falta_tp');
		}
		
		if (!isset($notas_eval_alumno[$eval['RECUP']])) {
			return Array('nota'=>'', 'resultado'=>'U', 'condicion'=>$cond['U'], 'asistencia'=>$porc_asistencia, 'estado'=>'abandono');
		}

		$nota = $notas_eval_alumno[$eval['RECUP']]['NOTA'] * $ponderaciones['R']['PORC_PARCIALES'] / 100
				+ $notas_eval_alumno[$eval['TP']]['NOTA'] * $ponderaciones['R']['PORC_TRABAJOS'] / 100;
		$nota = round($nota, 0, PHP_ROUND_HALF_DOWN);

		if ($notas_eval_alumno[$eval['RECUP']]['NOTA'] >= 4 && $nota >= 4) {
			return Array('nota'=>$nota, 'resultado'=>'A', 'condicion'=>$cond['A'], 'asistencia'=>$porc_asistencia, 'estado'=>'listo');
		} else {
			return Array('nota'=>$nota, 'resultado'=>'R', 'condicion'=>$cond['R'], 'asistencia'=>$porc_asistencia, 'estado'=>'listo');
		}
		return null;
	}
}

?>
