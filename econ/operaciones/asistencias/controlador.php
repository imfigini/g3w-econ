<?php

namespace econ\operaciones\asistencias;

use siu\guarani;
use kernel\util\validador;
use kernel\kernel;
use siu\modelo\datos\catalogo;
use siu\operaciones\_comun\operaciones\reporte\generador_pdf;
use econ\operaciones\asistencias\pagelet_planilla;

class controlador extends \siu\operaciones\asistencias\controlador
{

    /*Variables para filtros*/
    public $clase_id;
    public $materia;
    public $fecha;
    //public $tipo_clase = null;
    public $dia_semana;
//    public $dia_nombre;
    public $hs_comienzo_clase;
    public $filas;

    /* Variables para generaci?n de PDF */
    protected $pdf_hoja = "A4";
    protected $pdf_encabezado = array("", "", "");
    protected $pdf_encabezado_img = array("img" => "", "width" => "");
    protected $pdf_fuente = "Courier.afm";
    protected $pdf_fuente_texto = 10;
    protected $pdf_fuente_titulo = 10;
    protected $pdf_fuente_subtitulo = 12;
//    public $datos_consulta;        
//    public $filtros_consulta;        

    function get_clase_vista()
    {
        switch ($this->accion) {
                case 'index': 
                case 'mostrar_clases': 
                case 'filtrar': 
                        return 'vista_materias';
                case 'planilla':
                case 'generar_pdf':
                        return 'vista_planilla';
                default: 
                        return 'vista_edicion_asistencias';
        }
    }
	
	
    function modelo()
    {
        return guarani::carga_asistencias();
    }
	
    function accion__index()
    {
    }
	
    function accion__mostrar_clases()
    {
            //$clase_id = $this->validate_param('clase_id', 'post', validador::TIPO_ALPHANUM);
            $filas= $this->validate_param('cant', 'post', validador::TIPO_INT);
            $materia= $this->validate_param('materia', 'post', validador::TIPO_ALPHANUM);
            $dia_semana= $this->validate_param('dia_semana', 'post', validador::TIPO_INT);
            $hs_comienzo= $this->validate_param('hs_comienzo', 'post', validador::TIPO_ARRAY_TIME);

            $this->materia = $materia;
            $this->dia_semana = $dia_semana;
            $this->hs_comienzo_clase = $hs_comienzo;

            $resultado = $this->modelo()->get_clases_especificas($materia, $dia_semana, $hs_comienzo, $filas);
            $this->render_template('lista_materias/clases.twig', $resultado);
    }
	

	function accion__editar()
	{
            if (! kernel::request()->isPost()) 
            {
                $this->clase_id = $this->validate_param('clase_id', 'get', validador::TIPO_ALPHANUM);
                $this->materia = $this->validate_param('materia', 'get', validador::TIPO_ALPHANUM);
                $this->fecha = $this->validate_param('fecha', 'get', validador::TIPO_ALPHANUM);
                $this->dia_semana = $this->validate_param('dia_semana', 'get', validador::TIPO_INT);
                $this->hs_comienzo_clase = $this->validate_param('hs_comienzo', 'get', validador::TIPO_ARRAY_TIME);
                $this->filas = $this->validate_param('filas', 'get', validador::TIPO_INT);
//                kernel::log()->add_debug('$accion__editar', $this);
            } 
            else 
            {
			$alumnos = $_POST['alumnos'];
                        //kernel::log()->add_debug('GRABAR_alumnos', $alumnos);
                        
			foreach ($alumnos as $key => $alumno) {
                            
                            $alumnos[$key]['MOTIVO'] = ($alumno['PRESENTE'] > 0) ? $alumno['PRESENTE'] : null;
                            $alumnos[$key]['CANT_INASIST'] = ($alumno['PRESENTE'] == 0) ? 0 : 1;
			}
                        kernel::log()->add_debug('GRABAR_alumnos2', $alumnos);
			
//			kernel::log()->add_debug('accion__editar_CLASE', $_POST);
//			kernel::log()->add_debug('accion__editar_ALUMNOS', $alumnos);
			
//			$this->modelo()->grabar($_POST['clase']['ID'], $_POST['clase']['COMISION'], $_POST['clase']['FILAS'], $alumnos);
			//$seleccion_clase, $materia, $dia_semana, $fecha, $hs_comienzo_clase, $alumnos
                        $this->modelo()->grabar($_POST['clase']['ID'], $_POST['clase']['MATERIA'], $_POST['clase']['DIA_SEMANA'], $_POST['clase']['HS_COMIENZO_CLASE'], $alumnos);                        
                        
			
			$this->render_ajax(kernel::traductor()->trans('asistencia_guardado_exitoso'));
		}
	}
	
//	function accion__libres()
//	{
//		$this->comision_id = $this->validate_param('ID', 'get', validador::TIPO_ALPHANUM);
//	}
	
	function accion__planilla()
	{
            $this->subcomision_id = $this->validate_param('SUBCO', 'get', validador::TIPO_ALPHANUM, array('allowempty' => true));
            $this->tipo_clase = $this->validate_param('TIPO', 'get', validador::TIPO_ALPHANUM, array('allowempty' => true));
            $this->decodificar_comision();
            $this->filtros_consulta = $this->get_filtros();
            
            if (isset($this->filtros_consulta)){
                $this->validate_value($this->filtros_consulta['fecha'], validador::TIPO_DATE, array('format' => 'd/m/Y'));
                $pagelet = $this->vista()->pagelet('planilla');
                $pagelet->set_filtros($this->filtros_consulta);
            }
	}
	
        public function get_planilla($pantalla, $filtros)
        {
            $datos_planilla = null;
            if ($pantalla == pagelet_planilla::ESTADO_PLANILLA){

				if(!isset($this->comision_id)){
					$this->comision_hash = $this->validate_param('ID', 'get', validador::TIPO_ALPHANUM);
				}
				
				if(!isset($this->tipo_clase)){
					$this->tipo_clase = $this->validate_param('TIPO', 'get', validador::TIPO_ALPHANUM, array('allowempty' => true));					
				}
									
                $parametros = array('comision' => $this->comision_id,
                                                        'fecha' => str_replace("/", "-", $filtros['fecha']),
                                                        'cantidad' => $filtros['cantidad'],
                                                        'tipo' => $filtros['tipo'],
                                                        'tipo_clase' => $this->tipo_clase);

                $datos_planilla = $this->modelo()->get_planilla($parametros);
            }

            $this->datos_consulta = $datos_planilla;
            return $datos_planilla;
        }        

	function get_clase_encabezado_econ()
	{
            return $this->modelo()->info__clase_cabecera($this->clase_id, $this->materia, $this->dia_semana, $this->hs_comienzo_clase, $this->filas);
	}
	
	function get_clase_detalle()
	{
            $datos = $this->modelo()->info__clase_detalle($this->clase_id, $this->materia, $this->dia_semana, $this->hs_comienzo_clase, $this->filas);
            kernel::log()->add_debug('get_clase_detalle_datos', $datos);
            return $datos;
	}
	
//	function get_motivos_inasistencia()
//	{
//            return $this->modelo()->get_motivos_inasistencia();
//	}
        
	/**
	 * @return guarani_form
	 */
	function get_form()
	{
		return $this->get_form_builder()->get_formulario();
	}

	function get_vista_form(){
		return $this->get_form_builder()->get_vista();
	}
	
	/**
	* @return builder_form_filtro
	*/
	function get_form_builder()
	{
		// se hace esta funci?n para no construir cada vez el builder
		if (! isset($this->form_builder)) {
				$this->form_builder = kernel::localizador()->instanciar('operaciones\asistencias\planilla\builder_form_filtro');
		}

		return $this->form_builder;
	}
        
        
//	function get_filtros()
//	{
//           $filtros = null;
//           $form = $this->get_form();
//            if (kernel::request()->isPost()) {
//                $form->procesar();
//                $filtros = $form->get_datos();                    
//            }
//            return $filtros;
//	}
        
/* GENERADOR PDF */
       
	public function get_columnas($cantidad, $datos_fecha){
		$titulo = array();
		$titulo['NRO'] = "Nro";
		$titulo['LEGAJO'] = "Legajo";
		$titulo['ALUMNO'] = "Apellido y Nombres";
		$titulo['ACUMULADAS'] = "Acum";
                for($i= 1; $i <= $cantidad; $i++){
                    $titulo['FECHA'.$i] = $datos_fecha['FECHA'.$i];
                }
                return $titulo;
	}

	/**
	 * Recupera los datos
	 * @param entero $pagina
	 * Numero de p?gina que se desea recuperar
	 * @param buleano $todo
	 * Recuperar todos los datos
	 * @return array
	 */
	public function get_data(){
            $datos_alumnos = array();
                if (!isset($this->datos_consulta)){
                    $this->datos_consulta = $this->get_planilla(pagelet_planilla::ESTADO_PLANILLA, $this->filtros_consulta);
		}
                
		if (!empty($this->datos_consulta)){
                    $materia = current($this->datos_consulta);
                    $datos_alumnos = $materia['ALUMNOS'];
                    
                $cantidad = $this->filtros_consulta['cantidad'];       
                for($i= 1; $i <= $cantidad; $i++){    
                        foreach ($datos_alumnos as $dato){
                            $dato['FECHA'.$i] = null;
                        };
                };        
		} 
                   return $datos_alumnos;                
	}

	public function get_encabezado(){
            kernel::log()->add_debug('get_encabezado', $this);
		if (!empty($this->datos_consulta)){
                        $materia = current($this->datos_consulta);
			$encabezado = array();
			$encabezado['COMISION'] = $materia['COMISION'];
			$encabezado['FECHA'] = $materia['FECHA'];
			$encabezado['CARRERA_COM'] = $materia['CARRERA_COM'];
			$encabezado['CARRERA_NOMBRE'] = $materia['CARRERA_NOMBRE'];
			$encabezado['MATERIA'] = $materia['MATERIA'];
			$encabezado['MATERIA_NOMBRE'] = $materia['MATERIA_NOMBRE'];
			$encabezado['COMISION_NOMBRE'] = $materia['COMISION_NOMBRE'];
			$encabezado['PERIODO_LECTIVO'] = $materia['PERIODO_LECTIVO'];
			$encabezado['TURNO'] = $materia['TURNO'];
			$encabezado['ANIO_ACADEMICO'] = $materia['ANIO_ACADEMICO'];
			$encabezado['CATEDRA'] = $materia['CATEDRA'];
			$encabezado['DOCENTES'] = $materia['DOCENTES'];
			$encabezado['AULA'] = $materia['AULA'];
//			$encabezado['SUBCOMISION'] = $materia['SUBCOMISION'];
//			$encabezado['SUB_NOMBRE'] = $materia['SUB_NOMBRE'];
			$encabezado['FECHA1'] = $materia['FECHAS'][1];
			$encabezado['FECHA2'] = $materia['FECHAS'][2];
			$encabezado['FECHA3'] = $materia['FECHAS'][3];
			$encabezado['FECHA4'] = $materia['FECHAS'][4];
			$encabezado['FECHA5'] = $materia['FECHAS'][5];
			return $encabezado;
		}
		return false;
	}
        
        public function accion__generar_pdf(){
                $this->filtros_consulta['tipo'] = $this->validate_param('tipo', 'get', validador::TIPO_ALPHA, array(
			'allowempty' => true,
			'default' => 'L',
		));
                $this->filtros_consulta['fecha'] = $this->validate_param('fecha', 'get', validador::TIPO_TEXTO, array(
			'allowempty' => true
		));
                $this->filtros_consulta['cantidad'] = $this->validate_param('cantidad', 'get', validador::TIPO_INT, array(
			'allowempty' => true
		));
                
                $this->decodificar_comision();
		$pdf = $this->get_generador_pdf();
		$universidad = guarani::ua()->get_nombre_institucion()."\n".guarani::ua()->get_nombre()."\n";
		$pdf->set_font($this->pdf_fuente);
		$pdf->set_options(array('showHeadings'=>1, 'protectRows' => 1, 'maxWidth' => 550, 'width' => 550, 'fontSize' => $this->pdf_fuente_texto, 'shadeCol' => array(0.8,0.8,0.8),'cols' => array()));
		$pdf->set_text_options(array('left'=>5, 'top' => 20, 'maxWidth' => 550, 'width' => 550));
		$pdf->set_pdf_fuente_texto($this->pdf_fuente_texto);
		$pdf->set_pdf_fuente_titulo($this->pdf_fuente_titulo);
		$pdf->set_pdf_fuente_subtitulo($this->pdf_fuente_subtitulo);

		$this->pdf_encabezado = array($universidad, "Reporte de Asistencias", 110);
		$pdf->set_encabezado($this->pdf_encabezado[0], $this->pdf_encabezado[1], $this->pdf_encabezado[2]);
		
		$logo = kernel::localizador()->encontrar_img('www/img','logo.png');
		$pdf->set_encabezado_img($logo['path'], '100');                
	
		// ENCABEZADO
		$data = $this->get_data();
		$datos_encabezado = $this->get_encabezado();
		$titulos = $this->get_columnas($this->filtros_consulta['cantidad'], $datos_encabezado);
                
                $pdf->agregar_texto("");                       
		$pdf->agregar_texto("A�o Acad�mico: ".$datos_encabezado['ANIO_ACADEMICO']);
		$pdf->agregar_texto("Per�odo Lectivo: ".$datos_encabezado['PERIODO_LECTIVO']);
		$pdf->agregar_texto("Materia: (".$datos_encabezado['MATERIA'].") - ".$datos_encabezado['MATERIA_NOMBRE'] );
		$pdf->agregar_texto("Comisi�n: (".$datos_encabezado['COMISION'].") - ".$datos_encabezado['COMISION_NOMBRE'] );
                if(!empty($datos_encabezado['TURNO'])){
                    $pdf->agregar_texto("Turno: ".kernel::traductor()->trans('asistencias.turno_'.$datos_encabezado['TURNO']));  
                }  else {
                    $pdf->agregar_texto("Turno: -");                   
                };
                if(!empty($datos_encabezado['CATEDRA'])){            
                    $pdf->agregar_texto("C�tedra: ".$datos_encabezado['CATEDRA']);
                }  else {
                    $pdf->agregar_texto("C�tedra: -");                   
                };
                if(!empty($datos_encabezado['DOCENTES'])){            
                    $pdf->agregar_texto("Docentes: ".$datos_encabezado['DOCENTES']);
                }  else {
                    $pdf->agregar_texto("Docentes: -");                   
                };
                if(!empty($datos_encabezado['AULA'])){            
                    $pdf->agregar_texto("Aula: ".$datos_encabezado['AULA']);
                }  else {
                    $pdf->agregar_texto("Aula: -");                   
                };              
		$pdf->agregar_texto("");
		$pdf->agregar_texto("");
	
                
                //ALUMNOS
		$pdf->agregar_tabla($data,$titulos,'');
                $pdf->agregar_texto("");
                $pdf->agregar_texto("");

                //TABLA PARA FIRMA DOCENTE
                $cantidad = $this->filtros_consulta['cantidad'];
                
		$titulos_firma = array();
		$titulos_firma['DATOS'] = "Datos";
                for($i= 1; $i <= $cantidad; $i++){
                    $titulos_firma['FECHA'.$i] = $datos_encabezado['FECHA'.$i];
                };
		                
		$datos_firma = array();
		$datos_firma[0]['DATOS'] = "Tema";
		$datos_firma[1]['DATOS'] = "Firma Docente";
                        
		$pdf->agregar_tabla($datos_firma,$titulos_firma,'');
                $pdf->agregar_texto("");
                
		$pdf->set_nombre_archivo($this->get_nombre_archivo());
		$pdf->generar();
	}        
               
	protected function get_generador_pdf()
	{
		return new generador_pdf($this->crear_ezpdf('portrait'));
	}
        
	protected function crear_ezpdf($posicion_hoja = null){		
		$instancia = new \Cezpdf($this->pdf_hoja, $posicion_hoja);
		return $instancia;
	} 
	
        public function get_nombre_archivo(){
		return "planilla";
	}	
        
//        public function decodificar_comision(){
//		$this->comision_hash = $this->validate_param('ID', 'get', validador::TIPO_ALPHANUM);
//		$perfil_activo = kernel::persona()->get_id_perfil_activo();
//		if ($perfil_activo == "BED")
//		{
//			$comisiones = $this->modelo()->get_lista_comisiones_filtro($this->filtros);
//		} else 
//		{
//			$comisiones = $this->modelo()->get_lista_comisiones();
//		}
//
//		$rs	= null;
//		foreach ($comisiones as $comision) {
//			if ($comision[catalogo::id] == $this->comision_hash){
//				$this->comision_id = $comision['COMISION'];
//			}
//		}
//	}
	
	/* FILTROS PERFIL BEDELIA*/
/*
	function accion__filtrar(){
		$filtros = array();
		$filtros['materia'] = $this->validate_param('filtro_materia', 'get', validador::TIPO_ALPHANUM, array('allowempty' => true, 'default' => ''));
        $filtros['per_lect'] = $this->validate_param('filtro_per_lectivo', 'get', validador::TIPO_ALPHANUM, array('allowempty' => true, 'default' => ''));
		$filtros['docente'] = $this->validate_param('filtro_docente_legajo', 'get', validador::TIPO_ALPHANUM, array('allowempty' => true, 'default' => ''));

		if($filtros['materia'] != ""){
            $filtros['materia'] = $this->decodificar_materia($filtros['materia']);
        }

        if($filtros['per_lect'] != ""){
            $filtros['per_lect'] = $this->get_per_lec_by_id($filtros['per_lect']);
        }

        if($filtros['docente'] != ""){
            $filtros['docente'] = $this->decodificar_docente($filtros['docente']);
        }

		$this->filtros = $filtros;
		$pagelet = $this->vista()->pagelet('lista_materias');
		$pagelet->set_filtros($filtros);
		kernel::renderer()->add($pagelet);
	}
*/
        /*
	private function get_per_lec_by_id($id){
		$pl = catalogo::consultar('unidad_academica', 'periodos_lectivos');
		foreach($pl as $periodo){
			if ($periodo['_ID_'] == $id){
				return $periodo['PERIODO_LECTIVO'];
			}
		}
		return "";
	}
         * 
         */

//    function decodificar_materia($materia_hash) {
//        $datos = catalogo::consultar('unidad_academica', 'materias', array('term' => ""));
//        if (!empty($datos)) {
//            foreach($datos as $value){
//                if ($value['id'] == $materia_hash){
//                    return $value['materia'];
//                }
//            }
//        }
//        return "";
//    }
//
//    function decodificar_docente($docente_hash) {
//        $datos = catalogo::consultar('unidad_academica', 'docentes', array('term' => ""));
//        if (!empty($datos)) {
//            foreach($datos as $value){
//                if ($value['id'] == $docente_hash){
//                    return $value['legajo'];
//                }
//            }
//        }
//        return "";
//    }
	
//	function accion__buscar_materia() {
//		$term = $this->validate_param('term', 'get', validador::TIPO_TEXTO);
//		
//		$term = utf8_decode($term);
//		$datos = array();
//		if (!is_null($term)){
//			$datos = catalogo::consultar('unidad_academica', 'materias', array('term' => $term));
//		}
//		
//		$this->render_raw_json($datos);
//	}	
//	
//	function accion__buscar_docente() {
//		$term = $this->validate_param('term', 'get', validador::TIPO_TEXTO);
//		
//		$term = utf8_decode($term);
//		$datos = array();
//		if (!is_null($term)){
//			$datos = catalogo::consultar('unidad_academica', 'docentes', array('term' => $term));
//		}
//		
//		$this->render_raw_json($datos);
//	}	
}
?>