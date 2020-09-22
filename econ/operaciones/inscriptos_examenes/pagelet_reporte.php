<?php
namespace econ\operaciones\inscriptos_examenes;

use kernel\interfaz\pagelet;
use kernel\kernel;
use kernel\util\u;
use siu\modelo\datos\catalogo;
use siu\guarani;

class pagelet_reporte extends \siu\operaciones\inscriptos_examenes\pagelet_reporte  
{
	public function generar_excel()
	{
		//Se arregl� para que los acentos los emita correctamente
		$excel = $this->get_generador_excel();		
		$data_bruto = $this->datos_excel();
		$titulos = $this->titulos_excel();

		$res = array();
		foreach ($titulos as $col => $val){
			if (is_array($val)){
				$res[$col] = utf8_encode($val[0]);
			} else {
				$res[$col] = utf8_encode($val);
			}
		}		
		$titulos = $res;		
		unset($res);
		
		foreach($data_bruto as $num => $linea){
			$renglon = array();
			foreach ($linea as $key => $value){				
				if ($key != "URL_LINEA" && array_key_exists($key, $titulos)){
					$renglon[$key] = utf8_encode($value);
				}
			}
			$data_bruto[$num] = $renglon;
			unset($renglon);
		}
		
		$excel->set_nombre_archivo('inscripciones_a_examenes');
		$institucion = guarani::ua()->get_nombre_institucion();
		$facultad = utf8_encode(guarani::ua()->get_nombre());
		
		$excel->agregar_texto_sin_enter($institucion,1);
		$excel->agregar_texto($facultad,1);
		$estilo_titulo = array(
 			'font' => array('bold' => true),
 		);		
		$excel->agregar_texto_estilos(utf8_encode('Inscripciones a Ex�menes'),1, $estilo_titulo);		
		$excel->agregar_tabla($data_bruto,$titulos,'');		
		$excel->set_nombre_archivo($this->get_nombre_archivo());
		$excel->generar();
	}

	public function get_columnas(){
		$titulo = array();
        $titulo['MATERIA_NOMBRE_COMPLETO'] = "Materia";
		$titulo['TIPO_MESA'] = "Mesa";
        $titulo['SEDE'] = "Sede";
        $titulo['CANTIDAD_INSCR'] = "Cantidad de inscriptos ";
        $titulo['ANIO_ACADEMICO'] = "Año";
        $titulo['TURNO_EXAMEN'] = "Turno";
        $titulo['LLAMADO'] = "Llamado";
        $titulo['MOMENTO'] = "Fecha";
		return $titulo;
	}
	
}
?>