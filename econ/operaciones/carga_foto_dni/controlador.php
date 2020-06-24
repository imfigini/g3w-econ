<?php
namespace econ\operaciones\carga_foto_dni;

use kernel\kernel;
use siu\guarani;
use siu\extension_kernel\controlador_g3w2;
use siu\modelo\datos\catalogo;
use siu\errores\error_guarani;

class controlador extends controlador_g3w2
{
    protected $mensajes = array('mensaje'=>'', 'mensaje_error'=>'');
    
    function modelo()
    {
    }

    function accion__index()
    {
    }

    protected function get_path_attachment()
    {
        return kernel::proyecto()->get_dir_attachment();
    }     

    function get_foto_dni_cargada()
    {
        $parametros = array();
        
        $parametros['unidad_academica']  = guarani::ua()->get_id();
        $parametros['nro_inscripcion']   = kernel::persona()->get_nro_inscripcion();

        $archivo = catalogo::consultar('carga_foto_dni', 'get_foto_dni', $parametros);
        $path = $this->get_path_attachment();

        $filename = $path.'/'.$archivo['ARCHIVO'];
        $contenido = file_get_contents($filename);
        $type = pathinfo($filename, PATHINFO_EXTENSION); 
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($contenido);

        return $base64;
    }

    function accion__grabar()
    {
        // $inipath = php_ini_loaded_file();
        // print_r('<br>inipath: ');
        // print_r($inipath);
        
        if (kernel::request()->isPost()) {
            try
            {
                $parametros = $this->get_parametros_grabar();
                $nombreArchivo = $this->subir_archivo();
                $parametros['upload'] = self::buildNombreArchivo($nombreArchivo);
                $resultado = catalogo::consultar('carga_foto_dni', 'set_foto_dni', $parametros);
                if ($resultado != 1) {
                    throw new error_guarani('Error al grabar en la base de datos.');
                }
                $this->set_mensaje(utf8_decode('La imagen se subió correctamente'));
            }
            catch (error_guarani $e)
            {
                $msj = $e->getMessage();
                $this->set_mensaje_error($msj);
            }
        }  
    }

    function get_parametros_grabar()
    {
        $parametros = array();
        
        $parametros['unidad_academica']  = guarani::ua()->get_id();
        $parametros['nro_inscripcion']   = kernel::persona()->get_nro_inscripcion();

        $nombreArchivo = $_FILES['image']['name'];
        $parametros['upload'] = $nombreArchivo;

        return $parametros;        
    }

    /**
     * @throws error_guarani
     * @return El nombre fisico del archivo a grabar
     */
    function subir_archivo()
    {
        if(isset($_FILES['image']))
        {
            // $file_name = $_FILES['image']['name'];
            // $file_size = $_FILES['image']['size'];
            // $file_type=$_FILES['image']['type'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_error = $_FILES['image']['error'];
            $file_ext = strtolower(end(explode('.',$_FILES['image']['name'])));
            $extensions= array("jpeg","jpg","png");
            
            if(in_array($file_ext,$extensions)=== false){
                throw new error_guarani("Extension no permitida, por favor elija un archivo JPEG o PNG.");
            }
            
            if($file_error > 0)
            {
                $phpFileUploadErrors = array(
                    0 => 'There is no error, the file uploaded with success',
                    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                    3 => 'The uploaded file was only partially uploaded',
                    4 => 'No file was uploaded',
                    6 => 'Missing a temporary folder',
                    7 => 'Failed to write file to disk.',
                    8 => 'A PHP extension stopped the file upload.');
                throw new error_guarani($phpFileUploadErrors[$file_error]);
            }
            else
            {
                return $this->guardar_imagen_resized($file_tmp);
            }
        }
    }

    function guardar_imagen_resized($file_tmp)
    {
        $img = imagecreatefromjpeg($file_tmp);
        if (empty($img)) {
            $img = imagecreatefrompng($file_tmp);
            if (empty($img)) {
                throw new error_guarani('"Extension no permitida, por favor elija un archivo JPEG o PNG.');
            }
        }

        $x = imagesx($img);
        $y = imagesy($img);
            
        $y2 = 300;
        $x2 = $x*$y2/$y;

        $nro_inscripcion = kernel::persona()->get_nro_inscripcion();
        $save = imagecreatetruecolor($x2, $y2);
        imagecopyresized($save, $img, 0, 0, 0, 0, $x2, $y2, $x, $y);

        $saveName = $nro_inscripcion.'.png';

        $path_attach = $this->get_path_attachment().'/fotos_dni'; ///'.uniqid()';
        if (!file_exists($path_attach)) {
            //Se crea una carpeta unica para el archivo a subir
            mkdir($path_attach, 0777, true);
        }
        // Se mueven los archivos subidos al servidor del directorio temporal PHP al recientemente creado
        // move_uploaded_file($file_tmp, $path_attach.'/'.$file_name);

        imagepng($save, $path_attach.'/'.$saveName);
        imagedestroy($img);
        imagedestroy($save);

        $archivo = array(
            'nombre_folder' => $path_attach,
            'nombre_archivo' => $saveName
        );
        unset($saveName);
        return $archivo;
    }

    /**
     * Se queda con las ultimas dos partes del nombre, el resto es fijo
     * @param type $nombreArchivo
     */
    static function buildNombreArchivo($nombreArchivo)
    {
        $nombre_folder = $nombreArchivo['nombre_folder'];
        $nombre_archivo = $nombreArchivo['nombre_archivo'];
        $nombre = explode("/", $nombre_folder);
        $size = count($nombre);
        
        $nombre = $nombre[$size -1] . "/" . $nombre[$size];
        
        return $nombre.'/'.$nombre_archivo;
    }

       
 
    function set_mensaje_error($mensaje)
    {
        $this->mensajes['mensaje_error'] = $mensaje;
    }

    function get_mensaje_error()
    {
        if (!isset($this->mensajes['mensaje_error'])) {
            return '';
        }
        return $this->mensajes['mensaje_error'];
    }
    
    function set_mensaje($mensaje)
    {
        $this->mensajes['mensaje'] = $mensaje;
    }

    function get_mensaje()
    {
        if (!isset($this->mensajes['mensaje'])) {
            return '';
        }
        return $this->mensajes['mensaje'];
    }
	
}
