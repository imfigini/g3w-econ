<?php
namespace econ\operaciones\resumen_cursada;

use kernel\kernel;
use siu\modelo\datos\catalogo;
use kernel\util\validador;
use siu\guarani;
use siu\operaciones\_comun\operaciones\reporte\generador_pdf;


class controlador extends \siu\operaciones\resumen_cursada\controlador
{
    protected $comision_hash = '', $anio_academico_hash = '', $periodo_hash = '';

    /* Variables para generacion de PDF */
	protected $pdf_hoja = "A4";
	protected $pdf_encabezado = array("", "", "");
	protected $pdf_encabezado_img = array("img" => "", "width" => "");
	//protected $pdf_fuente = "Courier.afm";
	protected $pdf_fuente = "Times-Roman.afm";
	protected $pdf_fuente_texto = 10;
	protected $pdf_fuente_tabla = 8;
	protected $pdf_fuente_titulo = 10;
    protected $pdf_fuente_subtitulo = 12;
    

    function get_comision_hash()
    {
        return $this->comision_hash;
    }

    function get_anio_academico_hash()
    {
        return $this->anio_academico_hash;
    }

    function get_periodo_hash()
    {
        return $this->periodo_hash;
    }

	function accion__comision()
	{
        $this->comision_hash = $this->validate_param(0, 'get', validador::TIPO_ALPHANUM);
        $this->anio_academico_hash = $this->validate_param(1, 'get', validador::TIPO_ALPHANUM);
        $this->periodo_hash = $this->validate_param(2, 'get', validador::TIPO_ALPHANUM, array('allowempty' => true));

        $resultado = $this->get_datos_comision($this->comision_hash, $this->anio_academico_hash, $this->periodo_hash);
        //kernel::log()->add_debug('get_datos_comision', $resultado);

        $this->vista()->pagelet("contenido")->data['encabezado'] = $resultado['encabezado'];
        $this->vista()->pagelet("contenido")->data['parciales'] = $resultado['parciales'];
        $this->vista()->pagelet("contenido")->data['tabla'] = $resultado['tabla'];
        $this->vista()->pagelet("contenido")->data['ESTADO_ACTA'] = $resultado['ESTADO_ACTA'];
    }

    public function get_datos_comision($comision_hash, $anio_academico_hash, $periodo_hash)
    {
        $encabezado = $this->get_comision($anio_academico_hash,$periodo_hash,$comision_hash);
        $parciales = array();
        $tabla = array();
        
        if (!empty($encabezado['COMISION'])){
            $parciales = catalogo::consultar('comisiones', 'get_parciales', array('comision' => $encabezado['COMISION'],'visible_al_alumno' => "N"));
			//Iris: Cambiado para que muestre bien la info, aunque no tengan nota cargada
			$parciales_alu = catalogo::consultar('comisiones', 'get_parciales_alumnos_econ', array('comision' => $encabezado['COMISION']));
            $alumnos = catalogo::consultar('docente', 'alum_inscriptos_comision', array('comision' => $encabezado['COMISION']));
            
            foreach ($alumnos as $key => $alumno) {
                $tabla[$alumno['LEGAJO']]['ALUMNO'] = $alumno['ALUMNO'];
            }
            
            // Parciales
            foreach ($parciales_alu as $key => $parcial_alu) {
                $tabla[$parcial_alu['LEGAJO']]['PARCIALES'][$parcial_alu['EVALUACION']]['NOTA'] = $parcial_alu['NOTA'];
                $tabla[$parcial_alu['LEGAJO']]['PARCIALES'][$parcial_alu['EVALUACION']]['RESULTADO'] = $parcial_alu['RESULTADO_DESC'];
            }

            $acta_cursada = catalogo::consultar('comisiones', 'get_resultados_acta_cursada', array('comision' => $encabezado['COMISION']));
            if (!empty($acta_cursada)){
                // Acta
                foreach ($acta_cursada['FILAS'] as $key => $acta) {
                    $tabla[$acta['LEGAJO']]['ACTA']['COND_REG'] = $acta['COND_REG'];
                    $tabla[$acta['LEGAJO']]['ACTA']['NOTA'] = $acta['NOTA'];
                    $tabla[$acta['LEGAJO']]['ACTA']['RESULTADO'] = $acta['RESULTADO'];
                }
            } else {
                $acta_cursada['ESTADO'] = '';
            }
        }

        $resultado['encabezado'] = $encabezado;
        $resultado['parciales'] = $parciales;
        $resultado['tabla'] = $tabla;
        $resultado['ESTADO_ACTA'] = $acta_cursada['ESTADO'];

        return $resultado;
	}

    public function accion__generar_pdf()
    {
        $comision_hash = $this->validate_param('comision_hash', 'get', validador::TIPO_ALPHANUM);
        $anio_academico_hash = $this->validate_param('anio_academico_hash', 'get', validador::TIPO_ALPHANUM);
        $periodo_hash = $this->validate_param('periodo_hash', 'get', validador::TIPO_ALPHANUM);
    	
        $data = $this->get_datos_comision($comision_hash, $anio_academico_hash, $periodo_hash);
        $datos_encabezado = $data['encabezado'];
        $parciales = $data['parciales'];
        $datos_tabla = $data['tabla'];
                      
        $pdf = $this->get_generador_pdf();
        $universidad = guarani::ua()->get_nombre_institucion()."\n".guarani::ua()->get_nombre()."\n";
        $pdf->set_font($this->pdf_fuente);
        $pdf->set_options(array('showHeadings'=>1, 'protectRows' => 1, 'maxWidth' => 750, 'width' => 750, 'fontSize' => $this->pdf_fuente_tabla, 'shadeCol' => array(0.8,0.8,0.8),'cols' => array()));
        $pdf->set_text_options(array('left'=>5, 'top' => 20, 'maxWidth' => 600, 'width' => 600));
        $pdf->set_pdf_fuente_texto($this->pdf_fuente_texto);
        $pdf->set_pdf_fuente_titulo($this->pdf_fuente_titulo);
        $pdf->set_pdf_fuente_subtitulo($this->pdf_fuente_subtitulo);

        // TITULO
		$titulo = "Resumen de cursada";
        $pdf->set_encabezado($universidad, $titulo, 110);

        $logo = kernel::localizador()->encontrar_img('www/img','logo.png');
        $pdf->set_encabezado_img($logo['path'], '100');                

        // ENCABEZADO
        $pdf->agregar_texto("");                       
        $pdf->agregar_texto("Materia: ".$datos_encabezado['MATERIA_NOMBRE'] );
        $pdf->agregar_texto(utf8_decode("Comisión: ").$datos_encabezado['COMISION_NOMBRE']." (".$datos_encabezado['COMISION'].")");
        $pdf->agregar_texto(utf8_decode("Año Académico: ").$datos_encabezado['ANIO_ACADEMICO']);
        $pdf->agregar_texto(utf8_decode("Período Lectivo: ").$datos_encabezado['PERIODO_LECTIVO']);
        $pdf->agregar_texto("");
		$pdf->agregar_texto("");
		
		$titulos = $this->get_columnas($parciales, true);
		$renglones = $this->get_renglones($datos_tabla, true);
		$pdf->agregar_tabla($renglones, $titulos, '');
        $pdf->agregar_texto("");
        $pdf->agregar_texto("");
		
		if ($data['ESTADO_ACTA'] == "A") {
			$mensaje = kernel::traductor()->trans('resumen_cursada.comision.nota.acta_abierta');
			$pdf->agregar_texto($mensaje);
		}
        $pdf->set_nombre_archivo($this->get_nombre_archivo_pdf($datos_encabezado['COMISION']));
        $pdf->generar();
	}    
	
	protected function get_generador_pdf()
    {
		//return new generador_pdf($this->crear_ezpdf('portrait'));
		return new generador_pdf($this->crear_ezpdf('landscape'));
	}

    protected function crear_ezpdf($posicion_hoja = null){		
		$instancia = new \Cezpdf($this->pdf_hoja, $posicion_hoja);
		return $instancia;
	} 

	public function get_nombre_archivo_pdf($param){
		return "resumen_cursada_".$param.".pdf";
    }

    /* GENERADOR PDF */
    public function get_columnas($datos, $pdf=false)
    {
        $titulo = array();
        $titulo['NRO'] = "Nro";
        $titulo['LEGAJO'] = "Legajo";
        $titulo['ALUMNO'] = "Apellido y Nombres";

        foreach($datos as $dato) {
			$titulo[$dato['EVALUACION']] = ucfirst($dato['EVALUACION_DESC']);
		}
		if ($pdf) {
			$titulo['CURSADA'] = '<b>Acta de Cursada</b>';
			$titulo['CONDICION'] = '<b>'.utf8_decode("Condición").'</b>';
		} else {
			$titulo['CURSADA'] = 'Acta de Cursada';
			$titulo['CONDICION'] = 'Condición';
		}
        return $titulo;
    }

    public function get_renglones($datos, $pdf=false)
    {
		$renglones = Array();
		$i = 0;
		foreach($datos as $key_alu => $dato)
		{
			$renglon = Array();
			$renglon['NRO'] = $i;
			$renglon['LEGAJO'] = $key_alu;
			$renglon['ALUMNO'] = $dato['ALUMNO'];
			if (!$pdf) {
				$renglon['ALUMNO'] = utf8_encode($renglon['ALUMNO']);
			}
			foreach($dato['PARCIALES'] as $key_eval => $parcial)
			{
				$renglon[$key_eval] = '';
				if ($parcial['NOTA']) {
					$renglon[$key_eval] = $parcial['NOTA'];
				} 
				if ($parcial['RESULTADO']) {
					$renglon[$key_eval] .= ' ('.trim($parcial['RESULTADO']).')';
					if (!$pdf) {
						$renglon[$key_eval] = utf8_encode($renglon[$key_eval]);
					}					 
				}
			}
			
			$renglon['CURSADA'] = '';
			$renglon['CONDICION'] = '';
			if (isset($dato['ACTA']['NOTA'])) {
				$renglon['CURSADA'] .= $dato['ACTA']['NOTA'];
			}
			if (isset($dato['ACTA']['RESULTADO']) && $dato['ACTA']['RESULTADO'] != "Sin resultado") {
				$renglon['CURSADA'] .= ' ('.trim($dato['ACTA']['RESULTADO']).')';
			}
			if (isset($renglon['CONDICION'])) {
				$renglon['CONDICION'] = trim($dato['ACTA']['COND_REG']);
				if (!$pdf) {
					$renglon['CONDICION'] = utf8_encode($renglon['CONDICION']);
				}
			}
			if ($pdf) {
				$renglon['CURSADA'] = '<b>'.$renglon['CURSADA'].'</b>';
				$renglon['CONDICION'] = '<b>'.$renglon['CONDICION'].'</b>';
			}

			$renglones[] = $renglon;
			$i++;
		}
        return $renglones;
    }
    
    public function accion__generar_excel()
    {
        $comision_hash = $this->validate_param('comision_hash', 'get', validador::TIPO_ALPHANUM);
        $anio_academico_hash = $this->validate_param('anio_academico_hash', 'get', validador::TIPO_ALPHANUM);
        $periodo_hash = $this->validate_param('periodo_hash', 'get', validador::TIPO_ALPHANUM);
    	
        $data = $this->get_datos_comision($comision_hash, $anio_academico_hash, $periodo_hash);
        $datos_encabezado = $data['encabezado'];
        $parciales = $data['parciales'];
        $datos_tabla = $data['tabla'];
        
        $excel = $this->get_generador_excel();
        
        $excel->set_nombre_archivo("resumen_cursada_".$datos_encabezado['COMISION']);

        $institucion = guarani::ua()->get_nombre_institucion();
        $facultad = guarani::ua()->get_nombre();
        $titulo = utf8_encode($institucion.' - '.$facultad);

        $estilo_titulo = array(
 			'font' => array('bold' => true),
         );
         
        // TITULO
        $excel->agregar_texto_estilos($titulo, 1, $estilo_titulo);
        $subtitulo = "Resumen de cursada";
        $excel->agregar_texto_estilos($subtitulo, 1, $estilo_titulo);

		// ENCABEZADO
		$excel->agregar_texto(utf8_encode("Materia: ".$datos_encabezado['MATERIA_NOMBRE']), 1);
		$excel->agregar_texto("Comisión: ".$datos_encabezado['COMISION_NOMBRE'].' ('.$datos_encabezado['COMISION'].')', 1);
		$excel->agregar_texto("Año académico: ".$datos_encabezado['ANIO_ACADEMICO'], 1);
        $excel->agregar_texto("Período Lectivo: ".$datos_encabezado['PERIODO_LECTIVO'], 1);
        
		// TABLA ALUMNOS
		$titulos = $this->get_columnas($parciales, false);
		$renglones = $this->get_renglones($datos_tabla, false);
        $excel->agregar_tabla($renglones, $titulos, '');

		if ($data['ESTADO_ACTA'] == "A") {
			$mensaje = kernel::traductor()->trans('resumen_cursada.comision.nota.acta_abierta');
			$excel->agregar_texto($mensaje, 1);
		}

        $excel->generar();
    }   

    protected function get_generador_excel()
    {
        return new \siu\operaciones\_comun\operaciones\reporte\generador_excel();
    }    
}
?>
