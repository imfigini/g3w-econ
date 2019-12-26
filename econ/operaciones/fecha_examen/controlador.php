<?php
namespace econ\operaciones\fecha_examen;

use kernel\error_kernel_validacion;
use kernel\kernel;
use siu\modelo\datos\catalogo;


class controlador extends \siu\operaciones\fecha_examen\controlador {


	/**
	 * Solo la vista 'vista' tiene el form. Falla en otro caso
	 */
	private function recuperar_filtro()
	{
		$form = $this->get_form();
		if (!$form->procesar('GET')) {
			throw new error_kernel_validacion("Error al validar el filtro");
		}

		//si no se llega aca, aplican los valores default
		$this->datos_filtro = $form->get_datos();
	}
	function accion__index()
	{
		$form = $this->get_form();
		$mostrar_seleccione_turno = true;

		if (!empty($_GET['formulario_filtro'])) {
			$this->recuperar_filtro();
			$filtros = $form->get_datos();

			$carrera = "";
			if($filtros['carrera'] != "") $carrera = $this->decodificar_carrera($filtros['carrera']);
			$plan = "";
			if($carrera && ($filtros['plan'] != "")) $plan = $this->decodificar_plan($filtros['plan'], $carrera);
			$materia = "";
			$materia_descr = "";
			if($filtros['materia'] != ""){
				list($materia, $materia_descr) = $this->decodificar_materia($filtros['materia'], $carrera, $plan);
			}
			$this->datos_filtro["materia_descr"] = $materia_descr;
			$form->set_datos(array("materia_descr" => $materia_descr));
			$fecha_desde = ($filtros['fecha_desde'] != "")? \DateTime::createFromFormat('d/m/Y', $filtros['fecha_desde'])->format('Y-m-d') : "";
			$fecha_hasta = ($filtros['fecha_hasta'] != "")? \DateTime::createFromFormat('d/m/Y', $filtros['fecha_hasta'])->format('Y-m-d') : "";
			$tipo_inscripcion = $filtros['tipo_mesa'];

			$materias = array();

			$parametros = array(
				'carrera'   => $carrera,
				'plan'      => $plan,
				'materia'   => $materia,
				'fecha_desde'   => $fecha_desde,
				'fecha_hasta'   => $fecha_hasta,
				'tipo_inscripcion'  => $tipo_inscripcion
			);

			$datos = catalogo::consultar('usuario_anonimo', 'mesas_examen_usuario_anonimo', $parametros);

			//Hago corte de control por materia
			foreach($datos as $dato){

				$materias[$dato['MATERIA']]['MATERIA_NOMBRE'] = $dato['MATERIA_NOMBRE'];
				$materias[$dato['MATERIA']]['MATERIA'] = $dato['MATERIA'];

				$materias[$dato['MATERIA']]['examenes'][] = array(
					'MESA' => "{$dato['MATERIA_NOMBRE']} {$dato['TIPO_DE_MESA']} ({$dato['MATERIA']})",
					'FECHA' => $dato['FECHA_EXAMEN'],
					'TIPO_MESA' => $dato["TIPO_DE_MESA"],
					'INSCR_INICIO' => $dato['F_INICIO_INSC'],
					'INSCR_FIN' => $dato['F_FIN_INSC'],
					'INFO_EXTRA' => array(
						'HORA_INICIO' => $dato['HORA_INICIO'],
						'HORA_FINALIZACION' => $dato['HORA_FINALIZACION'],
						'EDIFICIO_AULA' => $dato['EDIFICIO_AULA'],
//                        'FECHA_TOPE_BAJAS' => $dato['F_TOPE_BAJA_MESA'],
						'FECHA_TOPE_BAJAS' => $dato['F_FIN_INSC'],
						'DOCENTES' => str_replace(' - ','<br>',$dato['DOCENTES']),
						'CARRERA_NOMBRE' => $dato['CARRERA_NOMBRE'],
						'PLAN' => $dato['PLAN'],
						'GRUPO_CARRERA_NOMBRE' => $dato['GRUPO_CARRERA_NOM']
					)
				);
			}

			$this->vista()->pagelet('filtro')->data['cuadro'] = $materias;
			$mostrar_seleccione_turno = false;
		}

		$this->vista()->pagelet('filtro')->add_var_js('mostrar_seleccione_turno', $mostrar_seleccione_turno);
	}
}
