<?php

namespace econ\operaciones\asistencias;

use siu\guarani;
use kernel\util\validador;
use kernel\kernel;
use siu\modelo\datos\catalogo;
use siu\operaciones\_comun\operaciones\reporte\generador_pdf;
use siu\operaciones\_comun\operaciones\reporte\generador_excel;
use econ\operaciones\asistencias\pagelet_planilla;

class controlador extends \siu\operaciones\asistencias\controlador
{

    /*Variables para filtros*/
    public $comisiones;
    public $fecha;
    public $hs_comienzo_clase;
    public $filas;

    /* Variables para generaci?n de PDF */
    protected $pdf_hoja = "A4";
    protected $pdf_encabezado = array("", "", "");
    protected $pdf_encabezado_img = array("img" => "", "width" => "");
    //protected $pdf_fuente = "Courier.afm";
    protected $pdf_fuente = "Times-Roman.afm";
    protected $pdf_fuente_texto = 10;
    protected $pdf_fuente_titulo = 10;
    protected $pdf_fuente_subtitulo = 12;
    public $datos_consulta;        
//    public $filtros_consulta;        

    function get_clase_vista()
    {
        //kernel::log()->add_debug('get_clase_vista $this->accion: ', $this->accion);
        switch ($this->accion) {
                case 'index': 
                case 'mostrar_clases': 
                case 'filtrar': return 'vista_materias';
                case 'planilla': return 'vista_planilla';
                case 'resumen': return 'vista_resumen';
                case 'generar_pdf': return 'vista_planilla';
                default: return 'vista_edicion_asistencias';
        }
    }
	
	
    function modelo()
    {
        return guarani::carga_asistencias();
    }
	
    function accion__index()
    {
//        kernel::log()->add_debug('accion__index GET: ', $_GET);
//        kernel::log()->add_debug('accion__index POST: ', $_POST);
    }
	
    function accion__mostrar_clases()
    {
        $filas = $this->validate_param('cant', 'post', validador::TIPO_INT);
        $comisiones_id = $this->validate_param('comisiones', 'post', validador::TIPO_ALPHANUM);
	    $resultado['clases'] = $this->modelo()->get_clases_comisiones($comisiones_id, $filas);
        //kernel::log()->add_debug('accion__mostrar_clases $resultado: '.__FILE__.' - '.__LINE__, $resultado);
        $this->render_template('lista_materias/clases.twig', $resultado);
    }
	

    function accion__editar()
    {
        if (! kernel::request()->isPost()) 
        {
            $this->comisiones = $this->validate_param('comisiones', 'get', validador::TIPO_ALPHANUM);
            $this->fecha = $this->validate_param('fecha', 'get', validador::TIPO_TEXTO);
            $this->hs_comienzo_clase = $this->validate_param('hs_comienzo_clase', 'get', validador::TIPO_ARRAY_TIME);
            $this->filas = $this->validate_param('filas', 'get', validador::TIPO_INT);
        } 
        else 
        {
            $alumnos = $_POST['alumnos'];
//            kernel::log()->add_debug('accion__editar POST: '.__FILE__.' - '.__LINE__, $_POST);
//            kernel::log()->add_debug('accion__editar GET: '.__FILE__.' - '.__LINE__, $_GET);
            //kernel::log()->add_debug('accion__editar $alumnos: '.__FILE__.' - '.__LINE__, $alumnos);
            
            foreach ($alumnos as $key => $alumno) {
                $alumnos[$key]['CANT_INASIST'] = ($alumno['PRESENTE'] == 'on') ? 0 : 1;
            }
            $this->modelo()->grabar($_POST['clase']['COMISIONES'], $_POST['clase']['FECHA'], $_POST['clase']['HS_COMIENZO_CLASE'], $alumnos);                        
            $this->render_ajax(kernel::traductor()->trans('asistencia_guardado_exitoso'));
        }
    }
	
    function accion__planilla()
    {
        //kernel::log()->add_debug('accion__planilla: GET', $_GET);
        //kernel::log()->add_debug('accion__planilla: filtros', $this->filtros_consulta);
        $this->comisiones = $this->validate_param('comisiones', 'get', validador::TIPO_ALPHANUM);
        $this->filtros_consulta = $this->get_filtros();
        if (isset($this->filtros_consulta)){
            $this->validate_value($this->filtros_consulta['fecha'], validador::TIPO_DATE, array('format' => 'd/m/Y'));
            $pagelet = $this->vista()->pagelet('planilla');
            $pagelet->set_filtros($this->filtros_consulta);
        }
    }
	
    function accion__resumen()
    {
//        kernel::log()->add_debug('accion__resumen GET: ', $_GET);
//        kernel::log()->add_debug('accion__resumen POST: ', $_POST);
        $this->comisiones = $this->validate_param('comisiones', 'get', validador::TIPO_ALPHANUM);
//        kernel::log()->add_debug('accion__resumen $comisiones: ', $this->comisiones);
        $pagelet = $this->vista()->pagelet('resumen');
        $pagelet->set_comisiones($this->comisiones);
    }
	

    public function get_planilla($pantalla, $filtros)
    {
        $datos_planilla = null;
        if ($pantalla == pagelet_planilla::ESTADO_PLANILLA)
        {
            if(!isset($this->comisiones))
            {
                $this->comisiones = $this->validate_param('comisiones', 'get', validador::TIPO_ALPHANUM);
            }

            $parametros = array('comisiones' => $this->comisiones,
                                'fecha' => str_replace("/", "-", $filtros['fecha']),
                                'cantidad' => $filtros['cantidad']);

            $datos_planilla = $this->modelo()->get_planilla($parametros);
        }
        $this->datos_consulta = $datos_planilla;
        return $datos_planilla;
    }        

    public function get_resumen()
    {
        if (!isset($this->comisiones)) {
            $this->comisiones = $this->validate_param('comisiones', 'get', validador::TIPO_ALPHANUM);
        }
        $parametros = array('comisiones' => $this->comisiones);
        return $this->modelo()->get_resumen($parametros);
    }   

    function get_clase_encabezado_econ()
    {
        return $this->modelo()->info__clase_cabecera($this->comisiones, $this->fecha, $this->hs_comienzo_clase, $this->filas);
    }

    function get_clase_detalle()
    {
        //kernel::log()->add_debug('$this->comisiones', $this->comisiones);
        $datos = $this->modelo()->info__clase_detalle($this->comisiones, $this->fecha, $this->hs_comienzo_clase, $this->filas);
        return $datos;
    }

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
        // se hace esta funcion para no construir cada vez el builder
        if (! isset($this->form_builder)) 
        {
            $this->form_builder = kernel::localizador()->instanciar('operaciones\asistencias\planilla\builder_form_filtro');
        }
        return $this->form_builder;
    }
        
        
    function get_filtros()
    {
       $filtros = null;
       $form = $this->get_form();
        if (kernel::request()->isPost()) {
            $form->procesar();
            $filtros = $form->get_datos();                    
        }
        return $filtros;
    }

    /* GENERADOR PDF */

    public function get_columnas($cantidad, $datos_fecha)
    {
        $titulo = array();
        $titulo['NRO'] = "Nro";
        $titulo['LEGAJO'] = "Legajo";
        $titulo['ALUMNO'] = "Apellido y Nombres";
        $titulo['CALIDAD_INSC'] = "Insc.";
        $titulo['ACUMULADAS'] = "Acum";
        $titulo['JUSTIFICADAS'] = "Just";


        for($i= 1; $i <= $cantidad; $i++)
        {
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
    public function get_data()
    {
        $datos_alumnos = array();
        if (!isset($this->datos_consulta))
        {
            $this->datos_consulta = $this->get_planilla(pagelet_planilla::ESTADO_PLANILLA, $this->filtros_consulta);
        }

        if (!empty($this->datos_consulta))
        {
            $materia = current($this->datos_consulta);
            $datos_alumnos = $materia['ALUMNOS'];

            $cantidad = $this->filtros_consulta['cantidad'];       
            for($i= 1; $i <= $cantidad; $i++)
            {    
                foreach ($datos_alumnos as $dato){
                    $dato['FECHA'.$i] = null;
                };
            };        
        } 
        return $datos_alumnos;                
    }

    public function get_encabezado()
    {
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
                $encabezado['DOCENTES'] = $materia['DOCENTES'];
                $encabezado['AULA'] = $materia['AULA'];
                $encabezado['FECHA1'] = $materia['FECHAS'][1];
                $encabezado['FECHA2'] = $materia['FECHAS'][2];
                $encabezado['FECHA3'] = $materia['FECHAS'][3];
                $encabezado['FECHA4'] = $materia['FECHAS'][4];
                $encabezado['FECHA5'] = $materia['FECHAS'][5];
                return $encabezado;
        }
        return false;
    }
        
    public function accion__generar_pdf()
    {
        $this->filtros_consulta['fecha'] = $this->validate_param('fecha', 'get', validador::TIPO_TEXTO, array(
                'allowempty' => true
        ));
        $this->filtros_consulta['cantidad'] = $this->validate_param('cantidad', 'get', validador::TIPO_INT, array(
                'allowempty' => true
        ));

        $this->comisiones = $this->validate_param('comisiones', 'get', validador::TIPO_ALPHANUM);
        $pdf = $this->get_generador_pdf();
        $universidad = guarani::ua()->get_nombre_institucion()."\n".guarani::ua()->get_nombre()."\n";
        $pdf->set_font($this->pdf_fuente);
        $pdf->set_options(array('showHeadings'=>1, 'protectRows' => 1, 'maxWidth' => 550, 'width' => 550, 'fontSize' => $this->pdf_fuente_texto, 'shadeCol' => array(0.8,0.8,0.8),'cols' => array()));
        $pdf->set_text_options(array('left'=>5, 'top' => 20, 'maxWidth' => 550, 'width' => 550));
        $pdf->set_pdf_fuente_texto($this->pdf_fuente_texto);
        $pdf->set_pdf_fuente_titulo($this->pdf_fuente_titulo);
        $pdf->set_pdf_fuente_subtitulo($this->pdf_fuente_subtitulo);

        $this->pdf_encabezado = array($universidad, "Planilla de Asistencias", 110);
        
        $pdf->set_encabezado($this->pdf_encabezado[0], $this->pdf_encabezado[1], $this->pdf_encabezado[2]);

        $logo = kernel::localizador()->encontrar_img('www/img','logo.png');
        $pdf->set_encabezado_img($logo['path'], '100');                

        // ENCABEZADO
        $data = $this->get_data();
        $datos_encabezado = $this->get_encabezado();
        
        $pdf->agregar_texto("");                       
        $pdf->agregar_texto("Año Académico: ".$datos_encabezado['ANIO_ACADEMICO']);
        $pdf->agregar_texto("Período Lectivo: ".$datos_encabezado['PERIODO_LECTIVO']);
        $pdf->agregar_texto("Materia: (".$datos_encabezado['MATERIA'].") - ".$datos_encabezado['MATERIA_NOMBRE'] );
        $pdf->agregar_texto("Comisión: (".$datos_encabezado['COMISION'].") - ".$datos_encabezado['COMISION_NOMBRE'] );
        if(!empty($datos_encabezado['TURNO'])){
            $pdf->agregar_texto("Turno: ".kernel::traductor()->trans('asistencias.turno_'.$datos_encabezado['TURNO']));  
        }  else {
            $pdf->agregar_texto("Turno: -");                   
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
        $titulos = $this->get_columnas($this->filtros_consulta['cantidad'], $datos_encabezado);
        
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
            //return new generador_pdf($this->crear_ezpdf('landscape'));
    }

    protected function crear_ezpdf($posicion_hoja = null){		
            $instancia = new \Cezpdf($this->pdf_hoja, $posicion_hoja);
            return $instancia;
    } 

    public function get_nombre_archivo(){
            return "planilla_asistencia.pdf";
    }	
  
    
    public function accion__generar_excel()
    {
        $excel = $this->get_generador_excel();
        $this->comisiones = $this->validate_param('comisiones', 'get', validador::TIPO_ALPHANUM);
        //kernel::log()->add_debug('accion__generar_excel $this->comisiones', $this->comisiones);
        $data = $this->get_resumen();
        //var_dump($data);
        
        $excel->set_nombre_archivo('resumen_asistencias');

        $institucion = guarani::ua()->get_nombre_institucion();
        $facultad = guarani::ua()->get_nombre();
        $titulo = utf8_encode($institucion.' - '.$facultad);

        $estilo_titulo = array(
 			'font' => array('bold' => true),
 		);
        $excel->agregar_texto_estilos($titulo, 1, $estilo_titulo);
        $excel->agregar_texto_estilos('Resumen de Asistencias',1, $estilo_titulo);
        
        $materia = utf8_encode("Materia: ".$data['MATERIA_NOMBRE'].' ('.$data['MATERIA'].')');
        $excel->agregar_texto($materia,1);
        
        $docentes = utf8_encode("Docentes: ".$data['DOCENTES']);
        $excel->agregar_texto($docentes,1);
        
        $turno = utf8_encode("Turno: ".kernel::traductor()->trans('asistencias.turno_'.$data['TURNO']));
        $excel->agregar_texto($turno,1);
        
        $anio_academico = utf8_encode("Año académico: ".$data['ANIO_ACADEMICO']);
        $excel->agregar_texto($anio_academico,1);
        
        $periodo = utf8_encode("Período lectivo: ".$data['PERIODO_LECTIVO']);
        $excel->agregar_texto($periodo,1);
        
        $comisiones = utf8_encode("Comisiones: ".$data['COMISION_NOMBRE']);
        $excel->agregar_texto($comisiones,1);
        
        $horario_aulas = utf8_encode("Dia, hora, edificio y aula: ".$data['HORARIO_AULAS']);
        $excel->agregar_texto($horario_aulas,1);
        
        $clases = utf8_encode("Cant. clases dictadas a la fecha: ".$data['CANT_CLASES']." de ".$data['TOTAL_CLASES']);
        $excel->agregar_texto($clases,1);
        
        $titulos = $this->titulos_tabla($data['FECHAS']);
        $datos = $this->datos_tabla($data['ALUMNOS']);
        //print_r($datos);
        $excel->agregar_tabla($datos, $titulos, '');
        
       
        
        $excel->generar();
    }   

    protected function get_generador_excel()
    {
        return new \siu\operaciones\_comun\operaciones\reporte\generador_excel();
    }

    protected function titulos_tabla($fechas)
    {
        $titulo = array();
        $titulo['nro'] = "Nro.";
        $titulo['legajo'] = "Legajo";
        $titulo['alumno'] = "Alumno";
        $titulo['calidad_insc'] = "Calidad Insc.";
        $titulo['inasist_acum'] = "Inasist. Acum.";
        $titulo['inasist_justif'] = "Inasist. Justif";
        $titulo['asist_real'] = "% Asist. Real";
        $titulo['asist_justif'] = "% Asist. c/justif.";
        $i=1;
        foreach ($fechas as $fecha)
        {
            $titulo[$i] = $fecha;
            $i++;
        }
//        print_r($titulo);
//        print_r('<br>');
        return $titulo;
    }
    
    protected function datos_tabla($alumnos)
    {
        $i=0;
        $datos = array();
        foreach ($alumnos as $legajo=>$alumno)
        {
            $i++;
            $data = array();
            $data['nro'] = $i;
            $data['legajo'] = $legajo;
            $data['alumno'] = utf8_encode($alumno['ALUMNO']);
            $data['calidad_insc'] = $alumno['CALIDAD_INSC'];
            $data['inasist_acum'] = $alumno['CANT_ACUMULADAS'];
            $data['inasist_justif'] = $alumno['CANT_JUSTIFICADAS'];
            $data['asist_real'] = $alumno['PORC_REAL'];
            $data['asist_justif'] = $alumno['PORC_JUST'];
            $k=1;
            foreach ($alumno['ASISTENCIAS'] as $fecha=>$asistencia)
            {
                $data[$k] = ($asistencia) ? $asistencia : '';
                $k++;
            }
            $datos[] = $data;
//            print_r($data); die;
        }
        return $datos;
    }
}
?>