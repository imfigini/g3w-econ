<?php
namespace econ\operaciones\ficha_alumno;

//use kernel\interfaz\pagelet;
use kernel\kernel;
use siu\modelo\datos\catalogo;

class pagelet_datos_personales extends \siu\operaciones\ficha_alumno\pagelet_datos_personales
{
    const DIR_FOTOS ="/var/www/documentacion_preinscripcion/";

    protected function get_path_documentacion()
    {
        $path = self::DIR_FOTOS;

        // $pathFromConfig = kernel::proyecto()->get('path_documentacion');
        // if(!empty($pathFromConfig))
        //     $path = $pathFromConfig;

        return $path;
    }

    function get_foto_dni_cargada($nro_inscripcion)
    {
        $parametros = array();
		$parametros['nro_inscripcion'] = $nro_inscripcion;
        $archivo = catalogo::consultar('carga_foto_dni', 'get_foto_dni', $parametros);
        $path = $this->get_path_documentacion();
        $filename = $path.$archivo['ARCHIVO'];
        $contenido = file_get_contents($filename);
        if (empty($contenido)) {
            $filename = $path.'no_imagen.png';
            $contenido = file_get_contents($filename);
        }
        $type = pathinfo($filename, PATHINFO_EXTENSION); 
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($contenido);
        return $base64;
	}
	
	public function prepare()
	{
		parent::prepare();
		$parametros = array('nro_inscripcion' => $this->controlador->get_nro_inscripcion());
		$datos = catalogo::consultar('alumno', 'datos_personales', $parametros);
		if (!empty($datos)){
			$datos = $datos[0];
			$datos['foto_dni'] = $this->get_foto_dni_cargada($parametros['nro_inscripcion']);
		}
		$this->data['datos'] = $datos;
	}
}
?>