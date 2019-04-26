<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use \siu\modelo\datos\catalogo;

class sistema extends \siu\modelo\datos\db\sistema
{

	/**
	 * parametros: escala_notas
	 * cache: memoria
	 * filas: n
	 */
	function escala_notas_econ($parametros)
	{
            $sql = " EXECUTE PROCEDURE sp_detalle_escala(  {$parametros['escala_notas']})";
            $datos = kernel::db()->consultar($sql, db::FETCH_NUM);
            $nuevo = array();
            foreach($datos as $id => $dato) 
            {
                //Para las escalas Regular, PyR y Promo que utilizan las materias de las carreras de los planes 50º
                //debe permitir a los docentes sólo cargar notas enteras 
                if ($dato[0] == 3 || $dato[0] == 4 || $dato[0] == 6) 
                {
                    if (!(strpos($dato[1], ',')) && (strlen($dato[1]) > 0)) 
                    {
                        $nuevo[$id]['ESCALA'] = $dato[0];
                        $nuevo[$id]['DESCRIPCION'] = $dato[1];
                        $nuevo[$id]['RESULTADO'] = $dato[2];
                        $nuevo[$id]['VALOR'] = $dato[3];
                    }
                }
                else
                {
                    $nuevo[$id]['ESCALA'] = $dato[0];
                    $nuevo[$id]['DESCRIPCION'] = $dato[1];
                    $nuevo[$id]['RESULTADO'] = $dato[2];
                    $nuevo[$id]['VALOR'] = $dato[3];
                }
            }
            return $nuevo;	 
	}	
}
?>