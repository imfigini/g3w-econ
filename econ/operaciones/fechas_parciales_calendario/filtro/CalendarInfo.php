<?php
require_once 'connect.php';

class CalendarInfo
{
    private $fullStart;
    private $fullEnd;

    function __construct($fullStart, $fullEnd) 
    {
        $this->fullStart	= $fullStart;
        $this->fullEnd		= $fullEnd;		
    }

    public function getFromDB()
    {
        $sqlText = "SELECT  tareas_instanciadas.hora AS hora_inicio, 
                            tareas_instanciadas.hora+'00:05' AS hora_fin,
                            tareas_instanciadas.fecha, 
                            tareas_instanciadas.id_indicador, 
                            tareas_instanciadas.resultado, 
                            tipos_estados.background_color,
                            tipos_estados.border_color                            
                        FROM tareas_instanciadas, tipos_estados
                        WHERE tareas_instanciadas.fecha BETWEEN '".$this->fullStart."' AND '".$this->fullEnd."'
                        AND tareas_instanciadas.id_estado = tipos_estados.id
                    ORDER BY tareas_instanciadas.fecha, tareas_instanciadas.hora";

        return toba::db('Indicadores')->consultar($sqlText);
    }

    public function get()
    {
            $eventos = $this->getFromDB();
            $resultado = array();

            foreach ($eventos as $evento)
            {
                    $id_indicador       = $evento['id_indicador'];
                    $source             = $this->get_nombre_indicador($id_indicador);
                    $title              = $evento['id_indicador'].'-'.$source;
                    $start              = $evento['fecha'].'T'.$evento['hora_inicio'].'-03:00';
                    $tip                = trim($evento['resultado']);
                    $url                = '';
                    $backgroundColor	= $evento['background_color'];
                    $borderColor	= $evento['border_color'];
                    $end                = $evento['fecha'].'T'.$evento['hora_fin'].'-03:00';
                       
                    $calendarEvent = CalendarInfo::buildEvent($title, $start, $url, $source, $tip, $backgroundColor, $borderColor, $end);
                    $resultado[] = $calendarEvent;
            }

            $resultado = implode(',', $resultado);
            $resultado = '['.$resultado.']';
            return $resultado;
    }

    static function buildEvent($title, $start, $url, $source, $tip, $backgroundColor, $borderColor, $end)
    {
        $resultado = array();
        $evento = array (
                        'title'			=> $title,
                        'start'			=> $start,
                        'url'			=> $url,
                        'textColor'             => 'black',
                        'tip'                   => $tip,
                        'backgroundColor'       => $backgroundColor,
                        'borderColor'           => $borderColor,
                        'end'			=> $end
                        );

        foreach($evento as $colName => $dataValue)
        {
            $resultado[] = '"'.$colName . '":"'. $dataValue . '"'; 
        }

        $resultado = implode(',', $resultado);
        $resultado = '{'.$resultado.'}';	
        return $resultado;
    }

    function get_nombre_indicador($id)
    {
        $sql = "SELECT nombre
                    FROM indicador I
                    WHERE 	id = $id";

        $bdi = Connect::get_id_bd_indicadores();
        $db = Connect::get_conexion($bdi);
        $result = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $result[0]['nombre'];
    }
}

?>