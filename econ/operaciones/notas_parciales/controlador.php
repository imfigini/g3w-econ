<?php

namespace econ\operaciones\notas_parciales;

use siu\guarani;
use kernel\kernel;
use siu\errores\error_guarani_procesar_renglones;
use kernel\util\validador;
use siu\util\generador_escalas;
use siu\modelo\datos\catalogo;
use kernel\nucleo\finalizador_request;
use siu\operaciones\_comun\operaciones\reporte\generador_pdf;

class controlador extends \siu\operaciones\notas_parciales\controlador
{
	/* Variables para generaci?n de PDF */
	protected $pdf_hoja = "A4";
	protected $pdf_encabezado = array("", "", "");
	protected $pdf_encabezado_img = array("img" => "", "width" => "");
	//protected $pdf_fuente = "Courier.afm";
	protected $pdf_fuente = "Times-Roman.afm";
	protected $pdf_fuente_texto = 10;
	protected $pdf_fuente_titulo = 10;
	protected $pdf_fuente_subtitulo = 12;
	//public $datos_consulta;       
	
	public function get_evaluacion_id()
	{
		return $this->evaluacion_id;
	}

	public function accion__generar_pdf()
    {
		$this->evaluacion_id = $this->validate_param('eval_id', 'get', validador::TIPO_ALPHANUM);
    	$datos_encabezado = $this->get_encabezado();
        $renglones = $this->get_renglones();
                      
        $pdf = $this->get_generador_pdf();
        $universidad = guarani::ua()->get_nombre_institucion()."\n".guarani::ua()->get_nombre()."\n";
        $pdf->set_font($this->pdf_fuente);
        $pdf->set_options(array('showHeadings'=>1, 'protectRows' => 1, 'maxWidth' => 550, 'width' => 550, 'fontSize' => $this->pdf_fuente_texto, 'shadeCol' => array(0.8,0.8,0.8),'cols' => array()));
        $pdf->set_text_options(array('left'=>5, 'top' => 20, 'maxWidth' => 550, 'width' => 550));
        $pdf->set_pdf_fuente_texto($this->pdf_fuente_texto);
        $pdf->set_pdf_fuente_titulo($this->pdf_fuente_titulo);
        $pdf->set_pdf_fuente_subtitulo($this->pdf_fuente_subtitulo);

        // TITULO
		$titulo = "Listado de notas de evaluacion: ".$datos_encabezado['EVALUACION_DESC'];
        $pdf->set_encabezado($universidad, $titulo, 110);

        $logo = kernel::localizador()->encontrar_img('www/img','logo.png');
        $pdf->set_encabezado_img($logo['path'], '100');                

        // ENCABEZADO
        $pdf->agregar_texto("");                       
        $pdf->agregar_texto("Año Académico: ".$datos_encabezado['ANIO_ACADEMICO']);
        $pdf->agregar_texto("Período Lectivo: ".$datos_encabezado['PERIODO_LECTIVO']);
        $pdf->agregar_texto("Materia: (".$datos_encabezado['MATERIA'].") - ".$datos_encabezado['MATERIA_NOMBRE'] );
        $pdf->agregar_texto("Comisión: (".$datos_encabezado['COMISION'].") - ".$datos_encabezado['COMISION_NOMBRE'] );
		$pdf->agregar_texto("Evaluación: ".$datos_encabezado['EVALUACION_DESC'] );
		if(!empty($datos_encabezado['FECHA_HORA'])){
            $pdf->agregar_texto("Fecha y hora: ".$datos_encabezado['FECHA_HORA'] );
        }  else {
            $pdf->agregar_texto("Fecha y hora: -");                   
        };
        $pdf->agregar_texto("");
        $pdf->agregar_texto("");

        // TABLA ALUMNOS
        $titulos = Array('LEGAJO'=>'Legajo', 'NOMBRE'=>'Alumno', 'NOTA'=>'Nota', 'RESULTADO'=>'Resultado', 'CORREGIDO_POR'=>'Corregido por', 'OBSERVACIONES'=>'Observaciones');
        $pdf->agregar_tabla($renglones, $titulos, '');
        $pdf->agregar_texto("");
        $pdf->agregar_texto("");

        $pdf->set_nombre_archivo($this->get_nombre_archivo_pdf($datos_encabezado['EVALUACION_DESC']));
        $pdf->generar();
	}    
	
	protected function get_generador_pdf()
    {
		return new generador_pdf($this->crear_ezpdf('portrait'));
		//return new generador_pdf($this->crear_ezpdf('landscape'));
	}

    protected function crear_ezpdf($posicion_hoja = null){		
		$instancia = new \Cezpdf($this->pdf_hoja, $posicion_hoja);
		return $instancia;
	} 

	public function get_nombre_archivo_pdf($eval){
		return "notas_parcial_".$eval.".pdf";
    }
    
    public function accion__generar_excel()
    {
        $this->evaluacion_id = $this->validate_param('eval_id', 'get', validador::TIPO_ALPHANUM);
    	$datos_encabezado = $this->get_encabezado();
        $renglones = $this->get_renglones();
		$renglones = $this->codificar_carcateres($renglones);

        $excel = $this->get_generador_excel();
        
        $excel->set_nombre_archivo("notas_parcial_".$datos_encabezado['EVALUACION_DESC']);

        $institucion = guarani::ua()->get_nombre_institucion();
        $facultad = guarani::ua()->get_nombre();
        $titulo = utf8_encode($institucion.' - '.$facultad);

        $estilo_titulo = array(
 			'font' => array('bold' => true),
         );
         
        // TITULO
        $excel->agregar_texto_estilos($titulo, 1, $estilo_titulo);
        $subtitulo = "Listado de notas de evaluacion: ".$datos_encabezado['EVALUACION_DESC'];
        $excel->agregar_texto_estilos($subtitulo, 1, $estilo_titulo);

        // ENCABEZADO
        $excel->agregar_texto(utf8_encode("Año académico: ".$datos_encabezado['ANIO_ACADEMICO']), 1);
        $excel->agregar_texto(utf8_encode("Período Lectivo: ".$datos_encabezado['PERIODO_LECTIVO']), 1);
        $excel->agregar_texto(utf8_encode("Materia: ".$datos_encabezado['MATERIA_NOMBRE'].' ('.$datos_encabezado['MATERIA'].')'), 1);
        $excel->agregar_texto(utf8_encode("Comisión: ".$datos_encabezado['COMISION_NOMBRE'].' ('.$datos_encabezado['COMISION'].')'), 1);
        $excel->agregar_texto(utf8_encode("Evaluación: ".$datos_encabezado['EVALUACION_DESC']), 1);
		if(!empty($datos_encabezado['FECHA_HORA'])) {
            $excel->agregar_texto(utf8_encode("Fecha y hora: ".$datos_encabezado['FECHA_HORA']), 1);
        } else {
            $excel->agregar_texto(utf8_encode("Fecha y hora: -"), 1);
        }
        
        // TABLA ALUMNOS
        $titulos = Array('LEGAJO'=>'Legajo', 'NOMBRE'=>'Alumno', 'NOTA'=>'Nota', 'RESULTADO'=>'Resultado', 'CORREGIDO_POR'=>'Corregido por', 'OBSERVACIONES'=>'Observaciones');
        $excel->agregar_tabla($renglones, $titulos, '');

        $excel->generar();
    }   

    protected function get_generador_excel()
    {
        return new \siu\operaciones\_comun\operaciones\reporte\generador_excel();
    }

	protected function codificar_carcateres($renglones)
	{
		$resultado = Array();
		foreach ($renglones as $renglon)
		{
			$renglon['NOMBRE'] = utf8_encode($renglon['NOMBRE']);
			$resultado[] = $renglon;
		}
		return $resultado;
	}

	protected function guardar($renglones_post)
	{
		kernel::log()->add_debug('renglones_post', $renglones_post);
		$renglones_sesion = kernel::sesion()->get($this->get_clave_sesion_parcial());
		$renglones_modificados = array();
		foreach($renglones_sesion as $id => $fila_s) {
			$renglon = $fila_s['RENGLON'];
			kernel::log()->add_debug('renglon', $renglon);
			kernel::log()->add_debug('renglones_post', $renglones_post);
			if(isset($renglones_post[$renglon])) {
				if (!isset($renglones_post[$renglon]['OBSERVACIONES'])) $renglones_post[$renglon]['OBSERVACIONES'] = "";
				if (!isset($renglones_post[$renglon]['CORREGIDO_POR'])) $renglones_post[$renglon]['CORREGIDO_POR'] = "";
				if( ($renglones_post[$renglon]['NOTA'] != $fila_s['NOTA'])
					|| ($renglones_post[$renglon]['OBSERVACIONES'] != $fila_s['OBSERVACIONES']) 
					|| ($renglones_post[$renglon]['CORREGIDO_POR'] != $fila_s['CORREGIDO_POR']) 
					) {
					$renglones_modificados[$id] = $fila_s;
					$renglones_modificados[$id]['NOTA'] = $this->vacio_a_null($renglones_post[$renglon]['NOTA']);
					$renglones_modificados[$id]['CORREGIDO_POR'] = $this->vacio_a_null($renglones_post[$renglon]['CORREGIDO_POR']);
					$renglones_modificados[$id]['OBSERVACIONES'] = $this->vacio_a_null($renglones_post[$renglon]['OBSERVACIONES']);
				}
			}
		}
		
		if ($renglones_modificados){
			$this->modelo()->evt__procesar_evaluaciones($this->evaluacion_id, $renglones_modificados);
		}
	}
}
?>
